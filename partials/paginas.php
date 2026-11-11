<?php
// ============================================================
//  partials/paginas.php
//  Páginas editables desde el propio sitio.
//
//  Quien tiene el permiso "Edición de páginas" para una categoría
//  (Inicial, Primario, Secundario, Técnica, Orientada, Global) ve,
//  al entrar a una página de esa categoría, el botón "Editar esta
//  página". Desde ahí puede:
//    - cambiar cualquier texto haciendo clic sobre él;
//    - agregar a cada sección mensajes, documentos PDF y bloques
//      de información.
//
//  Cómo funciona sin tocar las páginas:
//    partials/header.php llama a pag_iniciar() y partials/footer.php
//    a pag_terminar(). Entre los dos, la salida de la página queda
//    en un buffer; pag_procesar() la recorre, le pone una clave a
//    cada texto (un hash de su contenido original) y reemplaza los
//    que tienen un cambio guardado.
//
//    La clave sale del texto ORIGINAL del .php: si alguien cambia
//    ese texto en el código, el cambio hecho desde el sitio deja de
//    aplicarse (se ve el texto nuevo del código) y no rompe nada.
//
//  Si no hay cambios guardados y quien mira no es editor, la página
//  sale tal cual, sin pasar por el buffer.
//
//  Para que una zona NO sea editable (listados que salen de la base,
//  por ejemplo) se le pone el atributo  data-ed-no  al contenedor.
//
//  Tablas: pagina_textos y pagina_bloques (se crean solas; ver
//  también sql/migracion_paginas.sql).
// ============================================================

require_once __DIR__ . '/../conexion.php';

// ── Páginas editables ───────────────────────────────────────
//  clave = nombre del archivo sin .php
//  cats  = alcanza con tener UNA de estas categorías habilitadas
//  bloques => false: la página no admite bloques agregados
const PAG_PAGINAS = [
    'nivel-inicial'    => ['titulo' => 'Nivel Inicial',                   'grupo' => 'Nivel Inicial',    'cats' => ['Inicial']],
    'nivel-primario'   => ['titulo' => 'Nivel Primario',                  'grupo' => 'Nivel Primario',   'cats' => ['Primario']],
    'nivel-secundario' => ['titulo' => 'Secundario · elegir modalidad',   'grupo' => 'Nivel Secundario', 'cats' => ['Secundario'], 'bloques' => false],
    'sec-orientada'    => ['titulo' => 'Secundaria Orientada',            'grupo' => 'Nivel Secundario', 'cats' => ['Orientada', 'Secundario']],
    'sec-tecnica'      => ['titulo' => 'Secundaria Técnica',              'grupo' => 'Nivel Secundario', 'cats' => ['Técnica', 'Secundario']],

    'historia'                 => ['titulo' => 'Historia',                 'grupo' => 'Institucional', 'cats' => ['Global']],
    'autoridades'              => ['titulo' => 'Autoridades',              'grupo' => 'Institucional', 'cats' => ['Global']],
    'propuesta-educativa'      => ['titulo' => 'Propuesta Educativa',      'grupo' => 'Institucional', 'cats' => ['Global']],
    'becas'                    => ['titulo' => 'Becas',                    'grupo' => 'Institucional', 'cats' => ['Global']],
    'centro-estudiantes'       => ['titulo' => 'Centro de Estudiantes',    'grupo' => 'Comunidad',     'cats' => ['Global']],
    'feria-ciencias'           => ['titulo' => 'Feria de Ciencias',        'grupo' => 'Comunidad',     'cats' => ['Global']],
    'pastoral'                 => ['titulo' => 'Pastoral',                 'grupo' => 'Comunidad',     'cats' => ['Global']],
    'psicopedagogia'           => ['titulo' => 'Equipo de Psicopedagogía', 'grupo' => 'Comunidad',     'cats' => ['Global']],
    'deportes'                 => ['titulo' => 'Deportes',                 'grupo' => 'Comunidad',     'cats' => ['Global']],
    'trabaja-con-nosotros'     => ['titulo' => 'Trabajá con Nosotros',     'grupo' => 'Comunidad',     'cats' => ['Global']],
    'vinculos-institucionales' => ['titulo' => 'Vínculos Institucionales', 'grupo' => 'Comunidad',     'cats' => ['Global']],
];

/** Categorías del permiso "Edición de páginas" (las mismas etiquetas de novedades y agenda). */
const PAG_CATEGORIAS = ['Inicial', 'Primario', 'Secundario', 'Técnica', 'Orientada', 'Global'];

const PAG_TIPOS_BLOQUE = [
    'mensaje' => 'Mensaje',
    'pdf'     => 'Documento PDF',
    'info'    => 'Información',
];
const PAG_ESTILOS = ['info' => 'Aviso', 'importante' => 'Importante'];

const PAG_DIR_ARCHIVOS = 'archivos/paginas';      // relativa a la raíz del sitio
const PAG_PDF_MAX      = 10 * 1024 * 1024;        // 10 MB
const PAG_SECCION_TOPE = '_inicio';               // bloques al comienzo de la página

// ============================================================
//  PERMISOS
// ============================================================

/** ¿El usuario logueado puede editar esta página? (requiere panel_config.php) */
function pag_puede_editar(string $slug): bool {
    if (!isset(PAG_PAGINAS[$slug]) || !function_exists('puede')) return false;
    foreach (PAG_PAGINAS[$slug]['cats'] as $cat) {
        if (puede('edita_paginas', $cat)) return true;
    }
    return false;
}

/** Páginas que el usuario logueado puede editar: slug => definición. */
function pag_paginas_permitidas(): array {
    return array_filter(PAG_PAGINAS, fn($slug) => pag_puede_editar($slug), ARRAY_FILTER_USE_KEY);
}

// ============================================================
//  BASE DE DATOS
// ============================================================

/** Crea las tablas y las columnas de permiso si faltan. Devuelve false si no pudo. */
function pag_asegurar_tablas(PDO $pdo): bool {
    static $ok = null;
    if ($ok !== null) return $ok;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS pagina_textos (
            pagina      VARCHAR(40)  NOT NULL,
            clave       VARCHAR(24)  NOT NULL COMMENT 'Hash del texto original (+ número de repetición)',
            contenido   MEDIUMTEXT   NOT NULL COMMENT 'HTML ya saneado que reemplaza al original',
            original    VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Comienzo del texto original, para reconocerlo en el panel',
            id_usuario  INT UNSIGNED NULL,
            actualizado TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (pagina, clave)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS pagina_bloques (
            id_bloque      INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            pagina         VARCHAR(40)  NOT NULL,
            seccion        VARCHAR(60)  NOT NULL COMMENT 'id de la sección de la página, o _inicio',
            tipo           ENUM('mensaje','pdf','info') NOT NULL,
            estilo         VARCHAR(20)  NOT NULL DEFAULT 'info',
            titulo         VARCHAR(160) NOT NULL DEFAULT '',
            texto          TEXT         NULL,
            archivo        VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Ruta del PDF, relativa a la raíz del sitio',
            archivo_nombre VARCHAR(160) NOT NULL DEFAULT '',
            archivo_peso   INT UNSIGNED NOT NULL DEFAULT 0,
            enlace         VARCHAR(300) NOT NULL DEFAULT '',
            enlace_texto   VARCHAR(80)  NOT NULL DEFAULT '',
            orden          INT          NOT NULL DEFAULT 0,
            visible        TINYINT(1)   NOT NULL DEFAULT 1,
            hasta          DATE         NULL COMMENT 'Último día en que se muestra (vacío = sin vencimiento)',
            id_usuario     INT UNSIGNED NULL,
            creado_en      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
            actualizado    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_pagina (pagina, seccion, orden)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Permisos en los niveles (si la tabla existe)
        try {
            $cols = $pdo->query('SHOW COLUMNS FROM niveles_permiso')->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('edita_paginas', $cols, true)) {
                $pdo->exec("ALTER TABLE niveles_permiso ADD COLUMN edita_paginas TINYINT(1) NOT NULL DEFAULT 0");
            }
            if (!in_array('cat_paginas', $cols, true)) {
                $pdo->exec("ALTER TABLE niveles_permiso ADD COLUMN cat_paginas VARCHAR(255) NOT NULL DEFAULT ''");
            }
            if (!in_array('edita_formularios', $cols, true)) {
                $pdo->exec("ALTER TABLE niveles_permiso ADD COLUMN edita_formularios TINYINT(1) NOT NULL DEFAULT 0");
            }
        } catch (Throwable $ex) {
            // niveles_permiso aún no migrada: solo el admin edita
        }
        $ok = true;
    } catch (Throwable $ex) {
        error_log('[juan23] páginas editables: ' . $ex->getMessage());
        $ok = false;
    }
    return $ok;
}

/**
 * Todo lo guardado para una página.
 * Devuelve ['textos' => [clave => html], 'bloques' => [seccion => [fila, …]]].
 * Sin base o sin tablas devuelve todo vacío: la página se ve como en el código.
 */
function pag_cargar(string $slug, bool $incluir_ocultos = false): array {
    $out = ['textos' => [], 'bloques' => []];
    $pdo = db_opcional();
    if ($pdo === null) return $out;
    try {
        $st = $pdo->prepare('SELECT clave, contenido FROM pagina_textos WHERE pagina = ?');
        $st->execute([$slug]);
        $out['textos'] = $st->fetchAll(PDO::FETCH_KEY_PAIR);

        $st = $pdo->prepare(
            'SELECT *, (hasta IS NOT NULL AND hasta < CURDATE()) AS vencido
               FROM pagina_bloques WHERE pagina = ? ORDER BY seccion, orden, id_bloque'
        );
        $st->execute([$slug]);
        foreach ($st as $b) {
            if (!$incluir_ocultos && ((int) $b['visible'] !== 1 || (int) $b['vencido'] === 1)) continue;
            $out['bloques'][$b['seccion']][] = $b;
        }
    } catch (Throwable $ex) {
        // tablas aún no creadas
    }
    return $out;
}

// ============================================================
//  ENGANCHE CON LA PÁGINA (header.php / footer.php)
// ============================================================

/**
 * Empieza a capturar la salida de la página si hace falta procesarla.
 * Hay que llamarla ANTES de mandar cualquier salida.
 */
function pag_iniciar(?string $slug = null): void {
    $slug ??= basename($_SERVER['SCRIPT_NAME'] ?? '', '.php');
    if (!isset(PAG_PAGINAS[$slug]) || isset($GLOBALS['__pag'])) return;

    // La sesión solo se abre si el navegador ya trae la cookie: así un
    // visitante común no recibe una cookie por mirar una página.
    $editor = false;
    if (!empty($_COOKIE[session_name()]) && !headers_sent()) {
        require_once __DIR__ . '/../panel_config.php';
        $editor = esta_logueado() && pag_puede_editar($slug);
    }

    $datos = pag_cargar($slug, $editor);
    if (!$editor && !$datos['textos'] && !$datos['bloques']) return;   // la página sale tal cual

    $GLOBALS['__pag'] = ['slug' => $slug, 'editor' => $editor, 'datos' => $datos];
    ob_start();
}

/** Procesa lo capturado y lo manda al navegador. */
function pag_terminar(): void {
    if (!isset($GLOBALS['__pag'])) return;
    $p = $GLOBALS['__pag'];
    unset($GLOBALS['__pag']);
    $html = (string) ob_get_clean();
    try {
        echo pag_procesar($html, $p['slug'], $p['datos'], $p['editor']);
    } catch (Throwable $ex) {
        error_log('[juan23] no se pudo procesar la página ' . $p['slug'] . ': ' . $ex->getMessage());
        echo $html;                     // ante cualquier problema, la página original
    }
}

// ============================================================
//  LECTURA DEL HTML
// ============================================================

const PAG_VACIOS  = ['br', 'img', 'hr', 'input', 'wbr', 'source', 'meta', 'link', 'area', 'col', 'embed', 'track', 'param'];
/** Se saltean enteros: no se mira qué tienen adentro. */
const PAG_ATOMOS  = ['script', 'style', 'svg', 'textarea', 'select', 'iframe', 'template', 'noscript', 'form',
                     'video', 'audio', 'canvas', 'object', 'math', 'picture', 'map'];
/** Etiquetas que pueden ir DENTRO de un texto editable. */
const PAG_INLINE  = ['b', 'strong', 'i', 'em', 'u', 'br', 'span', 'small', 'sup', 'sub', 'mark', 'a', 's', 'wbr'];
/** Nunca son un texto editable por sí mismas. */
const PAG_NO_HOJA = ['main', 'body', 'html', 'ul', 'ol', 'dl', 'table', 'thead', 'tbody', 'tfoot', 'tr',
                     'option', 'label', 'time', 'nav', 'header', 'footer'];

/**
 * Arma un árbol liviano de la región [$ini, $fin) del HTML.
 * Cada nodo: t (etiqueta) · os/oe (inicio y fin de la etiqueta de
 * apertura) · cs/ce (ídem cierre; null si quedó sin cerrar) · k (hijos)
 * · p (padre) · atomo · vacio · sucio (tiene adentro algo sin cerrar).
 * El nodo 0 es la raíz y representa la región entera.
 */
function pag_arbol(string $html, int $ini, int $fin): array {
    $N = [[
        't' => '#raiz', 'os' => $ini, 'oe' => $ini, 'cs' => $fin, 'ce' => $fin,
        'k' => [], 'p' => -1, 'atomo' => false, 'vacio' => false, 'sucio' => false, 'a' => '',
    ]];
    $pila = [0];
    $re = '~<!--.*?-->|<(/?)([a-zA-Z][a-zA-Z0-9]*)((?:[^>"\']++|"[^"]*+"|\'[^\']*+\')*+)>~s';
    if (!preg_match_all($re, $html, $ms, PREG_SET_ORDER | PREG_OFFSET_CAPTURE, $ini)) return $N;

    $saltar_hasta = 0;
    foreach ($ms as $m) {
        $pos = $m[0][1];
        if ($pos >= $fin) break;
        if ($pos < $saltar_hasta || !isset($m[2])) continue;      // dentro de un átomo, o comentario

        $tag    = strtolower($m[2][0]);
        $fin_et = $pos + strlen($m[0][0]);

        if ($m[1][0] === '/') {
            // Cierre: busca su apertura en la pila
            for ($i = count($pila) - 1; $i > 0; $i--) {
                if ($N[$pila[$i]]['t'] === $tag) break;
            }
            if ($i === 0) continue;                               // cierre suelto: se ignora
            if ($i < count($pila) - 1) $N[$pila[$i]]['sucio'] = true;   // había algo sin cerrar adentro
            $N[$pila[$i]]['cs'] = $pos;
            $N[$pila[$i]]['ce'] = $fin_et;
            array_splice($pila, $i);
            continue;
        }

        $padre = $pila[count($pila) - 1];
        $id = count($N);
        $N[] = [
            't' => $tag, 'os' => $pos, 'oe' => $fin_et, 'cs' => null, 'ce' => null,
            'k' => [], 'p' => $padre, 'atomo' => false, 'vacio' => false, 'sucio' => false, 'a' => $m[3][0],
        ];
        $N[$padre]['k'][] = $id;

        if (in_array($tag, PAG_VACIOS, true) || substr(rtrim($m[3][0]), -1) === '/') {
            $N[$id]['vacio'] = true;
            $N[$id]['cs'] = $N[$id]['ce'] = $fin_et;
        } elseif (in_array($tag, PAG_ATOMOS, true)) {
            $cierre = stripos($html, '</' . $tag, $fin_et);
            $gt     = $cierre === false ? false : strpos($html, '>', $cierre);
            if ($gt === false || $gt >= $fin) {
                $N[$id]['cs'] = $N[$id]['ce'] = $fin;             // sin cierre: se lleva el resto
            } else {
                $N[$id]['cs'] = $cierre;
                $N[$id]['ce'] = $gt + 1;
            }
            $N[$id]['atomo'] = true;
            $saltar_hasta = $N[$id]['ce'];
        } else {
            $pila[] = $id;
        }
    }
    // Lo que quedó abierto en la pila no tiene cierre: su padre queda "sucio"
    for ($i = count($pila) - 1; $i > 0; $i--) {
        $N[$N[$pila[$i]]['p']]['sucio'] = true;
    }
    return $N;
}

/** ¿El nodo tiene texto propio (no solo espacios), fuera de sus hijos? */
function pag_tiene_texto_directo(string $html, array $N, int $i): bool {
    $n = $N[$i];
    $desde = $n['oe'];
    $tramos = [];
    foreach ($n['k'] as $h) {
        $tramos[] = substr($html, $desde, $N[$h]['os'] - $desde);
        $desde = $N[$h]['ce'] ?? $N[$h]['oe'];
    }
    $tramos[] = substr($html, $desde, $n['cs'] - $desde);
    foreach ($tramos as $t) {
        $t = preg_replace('~<!--.*?-->~s', '', $t);
        $t = str_replace(['&nbsp;', "\xC2\xA0"], ' ', $t);
        if (trim($t) !== '') return true;
    }
    return false;
}

/** ¿Todo lo que hay adentro son etiquetas de texto bien cerradas (o íconos svg)? */
function pag_solo_inline(array $N, int $i): bool {
    foreach ($N[$i]['k'] as $h) {
        $n = $N[$h];
        if ($n['atomo']) {
            if ($n['t'] !== 'svg') return false;
            continue;
        }
        if (!in_array($n['t'], PAG_INLINE, true)) return false;
        if ($n['cs'] === null || $n['sucio']) return false;
        if (!pag_solo_inline($N, $h)) return false;
    }
    return true;
}

/** ¿Es un texto editable? (tiene texto propio y adentro solo formato) */
function pag_es_hoja(string $html, array $N, int $i): bool {
    $n = $N[$i];
    if ($n['atomo'] || $n['vacio'] || $n['cs'] === null || $n['sucio']) return false;
    if (in_array($n['t'], PAG_NO_HOJA, true)) return false;
    if ($n['cs'] - $n['oe'] > 8000) return false;
    if (!pag_solo_inline($N, $i)) return false;
    return pag_tiene_texto_directo($html, $N, $i);
}

/** Junta en $hojas los textos editables que hay debajo del nodo $i. */
function pag_buscar_hojas(string $html, array $N, int $i, array &$hojas): void {
    foreach ($N[$i]['k'] as $h) {
        $n = $N[$h];
        if ($n['atomo'] || $n['vacio'] || $n['cs'] === null) continue;
        if (preg_match('~\bdata-ed-no\b~', $n['a'])) continue;
        if (pag_es_hoja($html, $N, $h)) {
            $hojas[] = $h;
        } else {
            pag_buscar_hojas($html, $N, $h, $hojas);
        }
    }
}

/** Íconos svg que hay dentro de un nodo, en orden de aparición. */
function pag_svgs(string $html, array $N, int $i, array &$svgs): void {
    foreach ($N[$i]['k'] as $h) {
        if ($N[$h]['t'] === 'svg') {
            $svgs[] = substr($html, $N[$h]['os'], $N[$h]['ce'] - $N[$h]['os']);
        } elseif (!$N[$h]['atomo']) {
            pag_svgs($html, $N, $h, $svgs);
        }
    }
}

function pag_atributo(string $attrs, string $nombre): string {
    return preg_match('~\b' . $nombre . '\s*=\s*(?:"([^"]*)"|\'([^\']*)\')~i', $attrs, $m)
        ? html_entity_decode($m[1] !== '' ? $m[1] : ($m[2] ?? ''), ENT_QUOTES, 'UTF-8')
        : '';
}

/**
 * Secciones ("partes") de la página a las que se les puede agregar contenido.
 * Son las <section> y los bloques con id que cuelgan del contenido principal
 * (también los que están envueltos en un <div> de fondo).
 * Devuelve [clave => ['nodo' => i, 'nombre' => texto]].
 */
function pag_partes(string $html, array $N): array {
    $partes = [];
    $sin_id = 0;
    $buscar = function (int $i, int $nivel) use (&$buscar, &$partes, &$sin_id, $html, $N): void {
        foreach ($N[$i]['k'] as $h) {
            $n = $N[$h];
            if ($n['atomo'] || $n['vacio'] || $n['cs'] === null) continue;
            if (!in_array($n['t'], ['section', 'div', 'article', 'aside'], true)) continue;

            $id    = pag_atributo($n['a'], 'id');
            $clase = pag_atributo($n['a'], 'class');
            $es_parte = $n['t'] === 'section' || ($id !== '' && $nivel === 1);

            if ($es_parte && !preg_match('~hero|wave|anchor-nav|breadcrumb|pb-wrap~i', $clase)) {
                $clave = $id !== '' ? substr(preg_replace('~[^\w\-]~u', '', $id), 0, 60) : 'parte-' . (++$sin_id);
                if ($clave === '' || isset($partes[$clave])) continue;
                $titulo = preg_match('~<h[1-3]\b[^>]*>(.*?)</h[1-3]>~is', substr($html, $n['oe'], $n['cs'] - $n['oe']), $m)
                    ? trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES, 'UTF-8')) : '';
                $partes[$clave] = ['nodo' => $h, 'nombre' => $titulo !== '' ? $titulo : $clave];
            } elseif (!$es_parte && $nivel < 3) {
                $buscar($h, $nivel + 1);
            }
        }
    };
    $buscar(0, 1);
    return $partes;
}

/** Dónde se insertan los bloques de una parte: al final, por dentro. */
function pag_punto_insercion(array $N, int $i): int {
    // Patrón "banda de color > contenedor centrado": se mete en el contenedor
    $hijos = array_values(array_filter($N[$i]['k'], fn($h) => !$N[$h]['vacio'] && !$N[$h]['atomo']));
    if (count($N[$i]['k']) === 1 && count($hijos) === 1
        && in_array($N[$hijos[0]]['t'], ['div', 'section'], true) && $N[$hijos[0]]['cs'] !== null) {
        return $N[$hijos[0]]['cs'];
    }
    return $N[$i]['cs'];
}

// ============================================================
//  PROCESAR LA PÁGINA
// ============================================================

function pag_procesar(string $html, string $slug, array $datos, bool $editor): string {
    // Región de contenido: <main> … </main> (o el <body> si no hay main)
    $ini = $fin = null;
    foreach (['main', 'body'] as $cont) {
        if (preg_match('~<' . $cont . '\b[^>]*>~i', $html, $m, PREG_OFFSET_CAPTURE)) {
            $cierre = strripos($html, '</' . $cont . '>');
            if ($cierre !== false && $cierre > $m[0][1]) {
                $ini = $m[0][1] + strlen($m[0][0]);
                $fin = $cierre;
                break;
            }
        }
    }
    if ($ini === null) return $html;

    $N = pag_arbol($html, $ini, $fin);
    $ediciones = [];          // [posición, largo a reemplazar, texto nuevo]

    // ── Textos ──
    $hojas = [];
    pag_buscar_hojas($html, $N, 0, $hojas);
    $vistos = [];
    foreach ($hojas as $h) {
        $n = $N[$h];
        $interior = substr($html, $n['oe'], $n['cs'] - $n['oe']);
        $hash = substr(sha1(preg_replace('~\s+~', ' ', trim($interior))), 0, 10);
        $vistos[$hash] = ($vistos[$hash] ?? 0) + 1;
        $clave = $hash . ($vistos[$hash] > 1 ? '-' . $vistos[$hash] : '');
        $cambiado = array_key_exists($clave, $datos['textos']);

        if ($cambiado || $editor) {
            $svgs = [];
            pag_svgs($html, $N, $h, $svgs);
            // Para el editor cada ícono lleva su número, así al guardar se sabe cuál es cuál
            if ($editor) {
                foreach ($svgs as $k => $svg) {
                    $svgs[$k] = preg_replace('~^<svg~i', '<svg data-ed-svg="' . ($k + 1) . '"', $svg, 1);
                }
            }
            if ($cambiado) {
                $nuevo = preg_replace_callback('~\[\[svg:(\d+)\]\]~', fn($x) => $svgs[(int) $x[1] - 1] ?? '', $datos['textos'][$clave]);
                $ediciones[] = [$n['oe'], $n['cs'] - $n['oe'], $nuevo];
            } elseif ($svgs) {
                // Sin cambio guardado: solo se numeran los íconos, en su lugar
                $k = 0;
                $nuevo = preg_replace_callback('~<svg\b~i', function () use (&$k) {
                    return '<svg data-ed-svg="' . (++$k) . '"';
                }, $interior);
                $ediciones[] = [$n['oe'], $n['cs'] - $n['oe'], $nuevo];
            }
        }
        if ($editor) {
            $marca = ' data-ed="' . $clave . '"' . ($cambiado ? ' data-ed-mod' : '') . ($n['t'] === 'button' ? ' data-ed-btn' : '');
            $ediciones[] = [$n['os'] + 1 + strlen($n['t']), 0, $marca];
        }
    }

    // ── Bloques agregados ──
    $con_bloques = (PAG_PAGINAS[$slug]['bloques'] ?? true);
    $partes = $con_bloques ? pag_partes($html, $N) : [];
    $pendientes = $con_bloques ? $datos['bloques'] : [];
    $hay_bloques = false;

    if ($con_bloques) {
        // Al comienzo de la página (antes de la primera sección)
        $primera = $partes ? $N[reset($partes)['nodo']]['os'] : $fin;
        $inicio  = pag_render_bloques(PAG_SECCION_TOPE, 'Comienzo de la página', $pendientes[PAG_SECCION_TOPE] ?? [], $editor);
        unset($pendientes[PAG_SECCION_TOPE]);
        if ($inicio !== '') { $ediciones[] = [$primera, 0, $inicio]; $hay_bloques = true; }

        foreach ($partes as $clave => $parte) {
            $bloques = $pendientes[$clave] ?? [];
            unset($pendientes[$clave]);
            $out = pag_render_bloques($clave, $parte['nombre'], $bloques, $editor);
            if ($out === '') continue;
            $hay_bloques = true;
            $ediciones[] = [pag_punto_insercion($N, $parte['nodo']), 0, $out];
        }
        // Bloques de una sección que ya no existe en la página: van al final, no se pierden
        foreach ($pendientes as $clave => $bloques) {
            $out = pag_render_bloques($clave, 'Sección «' . $clave . '» (ya no está en la página)', $bloques, $editor);
            if ($out !== '') { $ediciones[] = [$fin, 0, $out]; $hay_bloques = true; }
        }
    }

    // ── Recursos: hoja de estilos de los bloques y, para quien edita, el editor ──
    if ($hay_bloques || $editor) {
        $pos = stripos($html, '</head>');
        if ($pos !== false) {
            $ediciones[] = [$pos, 0, '  <link rel="stylesheet" href="' . e(asset('assets/css/paginas.css')) . '"/>' . "\n"];
        }
    }
    if ($editor) {
        $pos = strripos($html, '</body>');
        if ($pos !== false) {
            $bloques_js = [];
            foreach ($datos['bloques'] as $lista) {
                foreach ($lista as $b) {
                    $bloques_js[(int) $b['id_bloque']] = [
                        'id' => (int) $b['id_bloque'], 'seccion' => $b['seccion'], 'tipo' => $b['tipo'], 'estilo' => $b['estilo'],
                        'titulo' => $b['titulo'], 'texto' => (string) $b['texto'], 'archivo_nombre' => $b['archivo_nombre'],
                        'enlace' => $b['enlace'], 'enlace_texto' => $b['enlace_texto'],
                        'visible' => (int) $b['visible'], 'hasta' => (string) $b['hasta'],
                    ];
                }
            }
            $cfg = [
                'slug'    => $slug,
                'titulo'  => PAG_PAGINAS[$slug]['titulo'],
                'csrf'    => csrf_token(),
                'url'     => 'pagina_editar',
                'bloques' => (object) $bloques_js,
                'conBloques' => $con_bloques,
                'pdfMax'  => PAG_PDF_MAX,
                'cambios' => count($datos['textos']),
            ];
            $ediciones[] = [$pos, 0,
                '<script>window.PAG = ' . json_encode($cfg, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) . ";</script>\n"
                . '<script src="' . e(asset('assets/js/editor-paginas.js')) . '"></script>' . "\n"];
        }
    }

    // Se aplican de atrás hacia adelante para no correr las posiciones.
    // A igual posición, se conserva el orden en que se cargaron (por eso
    // la que se cargó última se inserta primero).
    $orden = array_keys($ediciones);
    usort($orden, fn($a, $b) => [$ediciones[$b][0], $b] <=> [$ediciones[$a][0], $a]);
    foreach ($orden as $k) {
        [$pos, $largo, $nuevo] = $ediciones[$k];
        $html = substr_replace($html, $nuevo, $pos, $largo);
    }
    return $html;
}

// ============================================================
//  BLOQUES: HTML PÚBLICO
// ============================================================

/** Texto plano → párrafos, con las direcciones web y los correos como enlaces. */
function pag_texto_html(string $texto): string {
    $texto = trim(str_replace(["\r\n", "\r"], "\n", $texto));
    if ($texto === '') return '';
    $out = '';
    foreach (preg_split('~\n{2,}~', $texto) as $parrafo) {
        $p = e($parrafo);
        $p = preg_replace_callback('~\bhttps?://[^\s<]+[^\s<.,;:!?)]~i',
            fn($m) => '<a href="' . $m[0] . '" target="_blank" rel="noopener">' . $m[0] . '</a>', $p);
        $p = preg_replace('~(?<![\w/:.@])[\w.+\-]+@[\w\-]+(?:\.[\w\-]+)+~', '<a href="mailto:$0">$0</a>', $p);
        $out .= '<p>' . nl2br($p, false) . '</p>';
    }
    return $out;
}

function pag_peso_legible(int $bytes): string {
    if ($bytes <= 0) return '';
    if ($bytes < 1024 * 1024) return max(1, (int) round($bytes / 1024)) . ' KB';
    return str_replace('.', ',', (string) round($bytes / 1024 / 1024, 1)) . ' MB';
}

/** Enlace válido para un bloque: web, correo, teléfono o una página del sitio. */
function pag_enlace_valido(string $url): bool {
    if ($url === '') return true;
    if (preg_match('~^(https?://|mailto:|tel:)[^\s"<>]+$~i', $url)) return true;
    return (bool) preg_match('~^[\w\-./#?=&%]+$~', $url) && !str_contains($url, '//');
}

function pag_render_bloque(array $b, bool $editor): string {
    $id      = (int) $b['id_bloque'];
    $oculto  = (int) $b['visible'] !== 1 || !empty($b['vencido']);
    $attrs   = ($editor ? ' data-pb-id="' . $id . '"' : '');
    $clases  = 'pb pb-' . $b['tipo'] . ($oculto ? ' pb--oculto' : '');
    $titulo  = trim((string) $b['titulo']);
    $aviso   = '';
    if ($editor && $oculto) {
        $aviso = '<span class="pb-estado">' . ((int) $b['visible'] !== 1 ? 'Oculto: solo lo ven quienes editan' : 'Vencido: ya no se muestra') . '</span>';
    }

    if ($b['tipo'] === 'pdf') {
        $meta = trim('PDF' . ($b['archivo_peso'] ? ' · ' . pag_peso_legible((int) $b['archivo_peso']) : ''));
        return '<a class="' . $clases . '" href="' . e($b['archivo']) . '" target="_blank" rel="noopener"' . $attrs . '>'
            . '<span class="pb-ico" aria-hidden="true">PDF</span>'
            . '<span class="pb-cuerpo">'
            .   '<strong class="pb-titulo">' . e($titulo !== '' ? $titulo : $b['archivo_nombre']) . '</strong>'
            .   (trim((string) $b['texto']) !== '' ? '<span class="pb-desc">' . e((string) $b['texto']) . '</span>' : '')
            .   '<span class="pb-meta">' . e($meta) . '</span>' . $aviso
            . '</span>'
            . '<span class="pb-accion">Abrir <span aria-hidden="true">↗</span></span>'
            . '</a>';
    }

    $ico = $b['tipo'] === 'mensaje' ? ($b['estilo'] === 'importante' ? '!' : '📢') : 'i';
    if ($b['tipo'] === 'mensaje' && $b['estilo'] === 'importante') $clases .= ' pb--importante';
    $enlace = '';
    if ($b['enlace'] !== '') {
        $externo = (bool) preg_match('~^https?://~i', $b['enlace']);
        $enlace = '<a class="pb-enlace" href="' . e($b['enlace']) . '"' . ($externo ? ' target="_blank" rel="noopener"' : '') . '>'
                . e($b['enlace_texto'] !== '' ? $b['enlace_texto'] : 'Más información') . ' <span aria-hidden="true">→</span></a>';
    }
    return '<div class="' . $clases . '"' . $attrs . ($b['tipo'] === 'mensaje' ? ' role="note"' : '') . '>'
        . '<span class="pb-ico" aria-hidden="true">' . $ico . '</span>'
        . '<div class="pb-cuerpo">'
        .   ($titulo !== '' ? '<h4 class="pb-titulo">' . e($titulo) . '</h4>' : '')
        .   '<div class="pb-texto">' . pag_texto_html((string) $b['texto']) . '</div>'
        .   $enlace . $aviso
        . '</div>'
        . '</div>';
}

/** Contenedor de los bloques de una sección. Vacío y sin editor → no se dibuja. */
function pag_render_bloques(string $seccion, string $nombre, array $bloques, bool $editor): string {
    if (!$bloques && !$editor) return '';
    $out = "\n" . '<div class="pb-wrap"' . ($editor ? ' data-pb-wrap="' . e($seccion) . '" data-pb-nombre="' . e($nombre) . '"' : '') . '>';
    foreach ($bloques as $b) {
        $out .= pag_render_bloque($b, $editor);
    }
    return $out . '</div>' . "\n";
}

// ============================================================
//  SANEADO DE LO QUE LLEGA DEL EDITOR
// ============================================================

/** Deja pasar solo declaraciones de estilo simples (color, tamaño, peso…). */
function pag_estilo_seguro(string $style): string {
    $ok = [];
    foreach (explode(';', $style) as $decl) {
        if (!preg_match('~^\s*(color|background-color|font-weight|font-style|font-size|text-decoration|opacity|white-space)\s*:\s*([#\w\s.,%()\-]+)\s*$~i', $decl, $m)) continue;
        if (preg_match('~url|expression|javascript~i', $m[2])) continue;
        $ok[] = strtolower($m[1]) . ':' . trim($m[2]);
    }
    return implode(';', $ok);
}

/**
 * HTML de un texto editado → HTML seguro para guardar.
 * Solo sobreviven las etiquetas de formato (PAG_INLINE) con sus atributos
 * class / style (filtrado) / href. Todo lo demás pierde la etiqueta y
 * conserva el texto; los saltos de bloque pasan a <br>.
 */
function pag_sanear_html(string $html): string {
    $html = mb_substr($html, 0, 20000);
    if (trim($html) === '') return '';

    if (!class_exists('DOMDocument')) {
        return trim(strip_tags($html, '<b><strong><i><em><u><br><small><sup><sub><mark><s>'));
    }

    $doc = new DOMDocument();
    $prev = libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8"><div id="pag-raiz">' . $html . '</div>', LIBXML_NONET);
    libxml_clear_errors();
    libxml_use_internal_errors($prev);
    $raiz = $doc->getElementById('pag-raiz');
    if (!$raiz) return '';

    $bloque = ['div', 'p', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'tr', 'section', 'article'];
    $tirar  = ['script', 'style', 'svg', 'iframe', 'object', 'embed', 'template', 'noscript', 'head', 'title', 'meta', 'link',
               'form', 'input', 'button', 'select', 'textarea', 'img', 'video', 'audio', 'canvas', 'math'];

    $recorrer = function (DOMNode $nodo) use (&$recorrer, $bloque, $tirar): string {
        $out = '';
        foreach ($nodo->childNodes as $h) {
            if ($h instanceof DOMText) {
                $out .= htmlspecialchars($h->nodeValue, ENT_NOQUOTES, 'UTF-8');
                continue;
            }
            if (!$h instanceof DOMElement) continue;
            $t = strtolower($h->nodeName);
            if (in_array($t, $tirar, true)) continue;
            if ($t === 'br' || $t === 'wbr') { $out .= '<' . $t . '>'; continue; }

            $dentro = $recorrer($h);
            if (!in_array($t, PAG_INLINE, true)) {
                // Etiqueta no permitida: queda el contenido; si era un bloque, separado por salto
                $out .= in_array($t, $bloque, true) ? ($out !== '' && !str_ends_with($out, '<br>') ? '<br>' : '') . $dentro . '<br>' : $dentro;
                continue;
            }

            $attrs = '';
            if (($c = trim($h->getAttribute('class'))) !== '' && preg_match('~^[\w\- ]{1,120}$~', $c)) {
                $attrs .= ' class="' . $c . '"';
            }
            if (($s = pag_estilo_seguro($h->getAttribute('style'))) !== '') {
                $attrs .= ' style="' . htmlspecialchars($s, ENT_QUOTES, 'UTF-8') . '"';
            }
            if ($t === 'a') {
                $href = trim($h->getAttribute('href'));
                if ($href !== '' && pag_enlace_valido($href)) {
                    $attrs .= ' href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '"';
                    if ($h->getAttribute('target') === '_blank') $attrs .= ' target="_blank" rel="noopener"';
                }
            }
            $out .= '<' . $t . $attrs . '>' . $dentro . '</' . $t . '>';
        }
        return $out;
    };

    $out = $recorrer($raiz);
    $out = preg_replace('~^(?:\s|<br>)+|(?:\s|<br>)+$~', '', $out);
    return $out;
}

/** Comienzo del texto, sin etiquetas, para mostrarlo en el panel. */
function pag_extracto(string $html, int $largo = 250): string {
    $t = html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />'], ' ', $html)), ENT_QUOTES, 'UTF-8');
    $t = trim(preg_replace('~\[\[svg:\d+\]\]|\s+~u', ' ', $t));
    return mb_substr($t, 0, $largo);
}
