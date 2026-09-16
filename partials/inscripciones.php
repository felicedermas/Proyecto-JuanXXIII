<?php
// ============================================================
//  partials/inscripciones.php
//  Núcleo del módulo de inscripciones, compartido por:
//    - las páginas públicas inscripcion-*.php
//      (a través de partials/inscripcion_pagina.php)
//    - el panel: gestion_inscripciones.php
//
//  Acá se define UNA sola vez:
//    - la clasificación nivel → modalidad → año
//    - los 4 formularios y sus campos
//    - el acceso a la base (tablas, habilitado, guardado)
//
//  Tablas: inscripcion_formularios e inscripciones
//  (se crean solas; ver también migracion_inscripciones.sql).
// ============================================================

require_once __DIR__ . '/../conexion.php';

// ── Clasificación: nivel → modalidades y años ───────────────
//  'General' = el nivel no tiene modalidades (Inicial y Primario).
//  La clave numérica del año es el orden (sirve para ordenar).
const INSC_NIVELES = [
    'Inicial' => [
        'modalidades' => ['General'],
        'anios'       => [3 => 'Sala de 3', 4 => 'Sala de 4', 5 => 'Sala de 5'],
    ],
    'Primario' => [
        'modalidades' => ['General'],
        'anios'       => [1 => '1° grado', 2 => '2° grado', 3 => '3° grado',
                          4 => '4° grado', 5 => '5° grado', 6 => '6° grado'],
    ],
    'Secundario' => [
        'modalidades' => ['Orientada', 'Técnica', 'A definir'],
        'anios'       => [1 => '1° año', 2 => '2° año', 3 => '3° año', 4 => '4° año',
                          5 => '5° año', 6 => '6° año', 7 => '7° año'],
    ],
];

/** 7° año existe solo en la Técnica (la Orientada dura 6 años). */
function insc_anio_valido(string $nivel, string $modalidad, int $anio): bool {
    if (!isset(INSC_NIVELES[$nivel]['anios'][$anio])) return false;
    if (!in_array($modalidad, INSC_NIVELES[$nivel]['modalidades'], true)) return false;
    if ($nivel === 'Secundario' && $modalidad === 'Orientada' && $anio > 6) return false;
    return true;
}

/** "Secundario › Técnica › 3° año" (omite la modalidad 'General'). */
function insc_sector(string $nivel, string $modalidad, string $anio): string {
    $partes = [$nivel];
    if ($modalidad !== 'General') $partes[] = $modalidad;
    $partes[] = $anio;
    return implode(' › ', $partes);
}

const INSC_ESTADOS = ['Nueva', 'En revisión', 'Contactada', 'Aceptada', 'Descartada'];

const INSC_COLOR_NIVEL = [
    'Inicial'    => '#E63946',
    'Primario'   => '#1D3557',
    'Secundario' => '#457B9D',
];

// ── Campos reutilizados ─────────────────────────────────────
const INSC_OPC_VINCULO = ['Madre', 'Padre', 'Tutor/a legal', 'Otro'];
const INSC_OPC_REF     = ['Recomendación de una familia', 'Redes sociales', 'Cercanía al domicilio', 'Ex alumno/a', 'Otro'];

/**
 * Definición de un campo:
 *   n => name · l => etiqueta · t => text|date|email|tel|dni|select|textarea
 *   r => obligatorio · o => opciones (select) · full => ocupa las 2 columnas
 *   ph => placeholder · max => largo máximo
 */
function insc_campos_alumno(): array {
    return [
        ['n' => 'alumno_nombre',   'l' => 'Nombres del/la alumno/a', 't' => 'text', 'r' => true,  'max' => 120],
        ['n' => 'alumno_apellido', 'l' => 'Apellidos',               't' => 'text', 'r' => true,  'max' => 120],
        ['n' => 'alumno_dni',      'l' => 'DNI',                     't' => 'dni',  'r' => true,  'ph' => 'Sin puntos'],
        ['n' => 'alumno_fnac',     'l' => 'Fecha de nacimiento',     't' => 'date', 'r' => true],
        ['n' => 'alumno_nac',      'l' => 'Nacionalidad',            't' => 'text', 'r' => false, 'max' => 60],
        ['n' => 'alumno_dom',      'l' => 'Domicilio',               't' => 'text', 'r' => true,  'max' => 200, 'full' => true],
    ];
}

function insc_campos_tutor(bool $con_domicilio = false): array {
    $c = [
        ['n' => 'tutor_nombre',  'l' => 'Nombre y apellido del responsable', 't' => 'text',   'r' => true, 'max' => 160, 'full' => true],
        ['n' => 'tutor_vinculo', 'l' => 'Vínculo',                           't' => 'select', 'r' => true, 'o' => INSC_OPC_VINCULO],
        ['n' => 'tutor_dni',     'l' => 'DNI del responsable',               't' => 'dni',    'r' => true, 'ph' => 'Sin puntos'],
        ['n' => 'tutor_tel',     'l' => 'Teléfono de contacto',              't' => 'tel',    'r' => true],
        ['n' => 'tutor_email',   'l' => 'Correo electrónico',                't' => 'email',  'r' => true, 'full' => true],
    ];
    if ($con_domicilio) {
        $c[] = ['n' => 'fam_dom', 'l' => 'Domicilio del grupo familiar', 't' => 'text', 'r' => true, 'max' => 200, 'full' => true];
    }
    return $c;
}

function insc_campos_adicional(bool $con_ref = true): array {
    $c = [];
    if ($con_ref) {
        $c[] = ['n' => 'ref', 'l' => '¿Cómo nos conociste?', 't' => 'select', 'r' => false, 'o' => INSC_OPC_REF];
    }
    $c[] = ['n' => 'obs', 'l' => 'Observaciones / comentarios', 't' => 'textarea', 'r' => false, 'max' => 2000, 'full' => true,
            'ph' => 'Información que quieras compartir con nosotros…'];
    return $c;
}

/** Opciones "valor => texto" de los años de un nivel. */
function insc_opciones_anio(string $nivel): array {
    return INSC_NIVELES[$nivel]['anios'];
}

// ── Los 4 formularios ───────────────────────────────────────
//  La clave coincide con la página: inscripcion-{clave}.php
function insc_formularios(): array {
    static $defs = null;
    if ($defs !== null) return $defs;

    $pasos_base = [
        ['Completá el formulario',    'Datos del alumno/a y del responsable.'],
        ['Presentá la documentación', 'DNI, partida de nacimiento y libreta sanitaria.'],
        ['Entrevista',                'Coordinamos una entrevista con la familia.'],
    ];

    $defs = [
        'jardin' => [
            'archivo'  => 'inscripcion-jardin.php',
            'nombre'   => 'Jardín de Infantes',
            'titulo'   => 'Inscripción <em>Jardín de Infantes</em>',
            'bajada'   => 'Nivel Inicial · Salas de 3, 4 y 5 años',
            'icono'    => '🧸',
            'accent'   => '#E63946', 'accent_light' => '#ffb3b8',
            'nivel'    => 'Inicial',
            'pasos'    => $pasos_base,
            'secciones' => [
                ['titulo' => 'Datos del/la alumno/a', 'campos' => array_merge(insc_campos_alumno(), [
                    ['n' => 'anio', 'l' => 'Sala a la que se inscribe', 't' => 'select', 'r' => true, 'o' => insc_opciones_anio('Inicial'), 'clasif' => 'anio'],
                ])],
                ['titulo' => 'Datos del responsable / tutor', 'campos' => insc_campos_tutor()],
                ['titulo' => 'Información adicional', 'campos' => insc_campos_adicional()],
            ],
        ],

        'primaria' => [
            'archivo'  => 'inscripcion-primaria.php',
            'nombre'   => 'Nivel Primario',
            'titulo'   => 'Inscripción <em>Nivel Primario</em>',
            'bajada'   => '1° a 6° grado',
            'icono'    => '✏️',
            'accent'   => '#1D3557', 'accent_light' => '#A8DADC',
            'nivel'    => 'Primario',
            'pasos'    => $pasos_base,
            'secciones' => [
                ['titulo' => 'Datos del/la alumno/a', 'campos' => array_merge(insc_campos_alumno(), [
                    ['n' => 'anio', 'l' => 'Grado al que se inscribe', 't' => 'select', 'r' => true, 'o' => insc_opciones_anio('Primario'), 'clasif' => 'anio'],
                ])],
                ['titulo' => 'Escuela de procedencia', 'campos' => [
                    ['n' => 'esc_anterior', 'l' => 'Institución anterior', 't' => 'text', 'r' => false, 'max' => 160, 'full' => true, 'ph' => 'Nombre del jardín o escuela'],
                    ['n' => 'esc_loc',      'l' => 'Localidad',            't' => 'text', 'r' => false, 'max' => 100],
                    ['n' => 'esc_rep',      'l' => '¿Repitió algún grado?', 't' => 'select', 'r' => false, 'o' => ['No', 'Sí']],
                ]],
                ['titulo' => 'Datos del responsable / tutor', 'campos' => insc_campos_tutor()],
                ['titulo' => 'Información adicional', 'campos' => insc_campos_adicional()],
            ],
        ],

        'sec' => [
            'archivo'  => 'inscripcion-sec.php',
            'nombre'   => 'Nivel Secundario',
            'titulo'   => 'Inscripción <em>Nivel Secundario</em>',
            'bajada'   => 'Bachillerato Orientado y Educación Técnica',
            'icono'    => '🎓',
            'accent'   => '#457B9D', 'accent_light' => '#A8DADC',
            'nivel'    => 'Secundario',
            'pasos'    => $pasos_base,
            'secciones' => [
                ['titulo' => 'Datos del/la alumno/a', 'campos' => array_merge(insc_campos_alumno(), [
                    ['n' => 'modalidad', 'l' => 'Modalidad', 't' => 'select', 'r' => true, 'clasif' => 'modalidad',
                     'o' => ['Orientada' => 'Secundaria Orientada (6 años)', 'Técnica' => 'Secundaria Técnica (7 años)', 'A definir' => 'Aún no lo decidí']],
                ])],
                ['titulo' => 'Trayectoria escolar', 'campos' => [
                    ['n' => 'anio',       'l' => 'Año al que se inscribe', 't' => 'select', 'r' => true, 'o' => insc_opciones_anio('Secundario'), 'clasif' => 'anio',
                     'hint' => '7° año corresponde solo a la modalidad Técnica.'],
                    ['n' => 'sec_origen', 'l' => 'Escuela primaria / secundaria de origen', 't' => 'text', 'r' => false, 'max' => 160],
                    ['n' => 'sec_pend',   'l' => 'Materias pendientes (si las hubiera)', 't' => 'text', 'r' => false, 'max' => 300, 'full' => true, 'ph' => 'Detallar si corresponde'],
                ]],
                ['titulo' => 'Datos del responsable / tutor', 'campos' => insc_campos_tutor()],
                ['titulo' => 'Información adicional', 'campos' => insc_campos_adicional()],
            ],
        ],

        'hermanos' => [
            'archivo'  => 'inscripcion-hermanos.php',
            'nombre'   => 'Hermanos',
            'titulo'   => 'Inscripción de <em>Hermanos</em>',
            'bajada'   => 'Para familias que inscriben a dos o más hijos/as',
            'icono'    => '👨‍👩‍👧‍👦',
            'accent'   => '#6d6875', 'accent_light' => '#cfc9d4',
            'nivel'    => null,           // cada hijo/a elige su nivel
            'pasos'    => [],
            'aviso'    => 'Completá <b>una sola vez</b> los datos del grupo familiar y agregá tantos hijos/as como necesites. Cada hijo/a queda registrado en su nivel, modalidad y año. Las familias con hermanos/as ya matriculados tienen <b>prioridad en la vacante</b>.',
            'secciones' => [
                ['titulo' => 'Datos del responsable / tutor', 'campos' => insc_campos_tutor(true)],
                ['titulo' => 'Hijos/as a inscribir', 'hijos' => true],
                ['titulo' => 'Información adicional', 'campos' => insc_campos_adicional(false)],
            ],
        ],
    ];
    return $defs;
}

const INSC_MAX_HIJOS = 8;

/** Campos de cada hijo/a en el formulario de hermanos. */
function insc_campos_hijo(): array {
    $opc_nivel = [];
    foreach (INSC_NIVELES as $nivel => $info) {
        foreach ($info['anios'] as $orden => $texto) {
            $opc_nivel[$nivel][$nivel . '|' . $orden] = $texto;   // se renderiza con <optgroup>
        }
    }
    return [
        ['n' => 'nombre',    'l' => 'Nombres',              't' => 'text',   'r' => true, 'max' => 120],
        ['n' => 'apellido',  'l' => 'Apellidos',            't' => 'text',   'r' => true, 'max' => 120],
        ['n' => 'dni',       'l' => 'DNI',                  't' => 'dni',    'r' => true, 'ph' => 'Sin puntos'],
        ['n' => 'fnac',      'l' => 'Fecha de nacimiento',  't' => 'date',   'r' => true],
        ['n' => 'nivel',     'l' => 'Nivel y sala / grado / año', 't' => 'select', 'r' => true, 'o' => $opc_nivel],
        ['n' => 'modalidad', 'l' => 'Modalidad (solo Secundario)', 't' => 'select', 'r' => false,
         'o' => ['Orientada' => 'Secundaria Orientada', 'Técnica' => 'Secundaria Técnica', 'A definir' => 'Aún no lo decidí']],
        ['n' => 'actual',    'l' => '¿Ya es alumno/a del colegio?', 't' => 'select', 'r' => false,
         'o' => ['No, ingresa por primera vez', 'Sí, ya asiste'], 'full' => true],
    ];
}

function insc_formulario(string $clave): ?array {
    return insc_formularios()[$clave] ?? null;
}

// ============================================================
//  BASE DE DATOS
// ============================================================

/** Crea las tablas si no existen. Devuelve false si no se pudo. */
function insc_asegurar_tablas(PDO $pdo): bool {
    static $ok = null;
    if ($ok !== null) return $ok;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS inscripcion_formularios (
            clave        VARCHAR(20)  NOT NULL PRIMARY KEY,
            habilitado   TINYINT(1)   NOT NULL DEFAULT 0,
            id_usuario   INT UNSIGNED NULL COMMENT 'Último usuario que lo cambió',
            actualizado  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS inscripciones (
            id_inscripcion  INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            formulario      VARCHAR(20)  NOT NULL,
            grupo           CHAR(10)     NOT NULL COMMENT 'Código de envío; lo comparten los hermanos',
            nivel           ENUM('Inicial','Primario','Secundario') NOT NULL,
            modalidad       VARCHAR(20)  NOT NULL DEFAULT 'General',
            anio            VARCHAR(20)  NOT NULL,
            anio_orden      TINYINT UNSIGNED NOT NULL,
            alumno_nombre   VARCHAR(120) NOT NULL,
            alumno_apellido VARCHAR(120) NOT NULL,
            alumno_dni      VARCHAR(12)  NOT NULL,
            alumno_fnac     DATE         NULL,
            tutor_nombre    VARCHAR(160) NOT NULL,
            tutor_email     VARCHAR(160) NOT NULL,
            tutor_tel       VARCHAR(40)  NOT NULL,
            datos           LONGTEXT     NOT NULL COMMENT 'JSON con todo lo enviado, por sección',
            estado          ENUM('Nueva','En revisión','Contactada','Aceptada','Descartada') NOT NULL DEFAULT 'Nueva',
            notas           TEXT         NULL,
            ip              VARCHAR(45)  NOT NULL DEFAULT '',
            creado_en       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
            actualizado     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_sector (nivel, modalidad, anio_orden),
            KEY idx_estado (estado),
            KEY idx_grupo  (grupo),
            KEY idx_fecha  (creado_en)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $ins = $pdo->prepare('INSERT IGNORE INTO inscripcion_formularios (clave, habilitado) VALUES (?, 0)');
        foreach (array_keys(insc_formularios()) as $clave) {
            $ins->execute([$clave]);
        }
        $ok = true;
    } catch (Throwable $ex) {
        error_log('[juan23] inscripciones: no se pudieron crear las tablas: ' . $ex->getMessage());
        $ok = false;
    }
    return $ok;
}

/** Estado de los formularios: clave => fila (habilitado, actualizado, id_usuario). */
function insc_estados_formularios(PDO $pdo): array {
    $out = [];
    foreach ($pdo->query('SELECT * FROM inscripcion_formularios') as $f) {
        $out[$f['clave']] = $f;
    }
    return $out;
}

function insc_habilitado(PDO $pdo, string $clave): bool {
    $st = $pdo->prepare('SELECT habilitado FROM inscripcion_formularios WHERE clave = ?');
    $st->execute([$clave]);
    return (int) $st->fetchColumn() === 1;
}

// ============================================================
//  VALIDACIÓN
// ============================================================

/** Normaliza y valida un valor según su campo. Devuelve [valor, error|null]. */
function insc_validar_campo(array $c, $bruto): array {
    $v = is_string($bruto) ? trim(preg_replace('/\s+/u', ' ', $bruto) ?? '') : '';
    if ($c['t'] === 'textarea' && is_string($bruto)) {
        $v = trim(str_replace("\r\n", "\n", $bruto));
    }

    if ($v === '') {
        return ['', !empty($c['r']) ? 'Este campo es obligatorio.' : null];
    }

    $max = $c['max'] ?? 160;
    if (mb_strlen($v) > $max) {
        return [$v, "Máximo $max caracteres."];
    }

    switch ($c['t']) {
        case 'dni':
            $v = preg_replace('/[.\s-]/', '', $v);
            if (!preg_match('/^[0-9]{7,8}$/', $v)) return [$v, 'Ingresá el DNI sin puntos (7 u 8 números).'];
            break;
        case 'email':
            if (!filter_var($v, FILTER_VALIDATE_EMAIL)) return [$v, 'El correo no parece válido.'];
            break;
        case 'tel':
            if (!preg_match('/^[0-9+\s()\-]{6,25}$/', $v)) return [$v, 'Usá solo números, espacios, guiones o +.'];
            break;
        case 'date':
            $d = DateTime::createFromFormat('!Y-m-d', $v);
            if (!$d || $d->format('Y-m-d') !== $v) return [$v, 'Fecha inválida.'];
            $anios = (int) $d->diff(new DateTime('today'))->format('%r%y');
            if ($d > new DateTime('today') || $anios > 25) return [$v, 'Revisá la fecha de nacimiento.'];
            break;
        case 'select':
            if (!in_array($v, insc_valores_opciones($c['o']), true)) return ['', 'Elegí una opción de la lista.'];
            break;
    }
    return [$v, null];
}

/** Lista plana de valores válidos de un select (admite listas, mapas y grupos). */
function insc_valores_opciones(array $opciones): array {
    $vals = [];
    $es_lista = array_is_list($opciones);
    foreach ($opciones as $k => $o) {
        if (is_array($o)) {                         // <optgroup>
            foreach ($o as $kk => $oo) $vals[] = (string) $kk;
        } else {
            $vals[] = $es_lista ? (string) $o : (string) $k;
        }
    }
    return $vals;
}

/** Texto visible de una opción elegida. */
function insc_texto_opcion(array $opciones, string $valor): string {
    $es_lista = array_is_list($opciones);
    foreach ($opciones as $k => $o) {
        if (is_array($o)) {
            if (isset($o[$valor])) return $k . ' · ' . $o[$valor];
        } elseif (!$es_lista && (string) $k === $valor) {
            return $o;
        }
    }
    return $valor;
}

/**
 * Valida el POST completo de un formulario.
 * Devuelve ['valores' => [...], 'errores' => [name => msg], 'alumnos' => [filas listas para guardar]]
 */
function insc_procesar(string $clave, array $post): array {
    $def     = insc_formulario($clave);
    $valores = [];
    $errores = [];
    $datos_comunes = [];   // secciones compartidas (tutor, adicional...) para el JSON
    $datos_alumno  = [];   // secciones propias del alumno (form. individuales)

    $modalidad = 'General';
    $anio_ord  = 0;

    foreach ($def['secciones'] as $sec) {
        if (!empty($sec['hijos'])) continue;
        $filas = [];
        foreach ($sec['campos'] as $c) {
            [$v, $err] = insc_validar_campo($c, $post[$c['n']] ?? '');
            $valores[$c['n']] = $v;
            if ($err) $errores[$c['n']] = $err;

            if (($c['clasif'] ?? '') === 'anio')      $anio_ord  = (int) $v;
            if (($c['clasif'] ?? '') === 'modalidad') $modalidad = $v;

            if ($v !== '') {
                $filas[] = [$c['l'], $c['t'] === 'select' ? insc_texto_opcion($c['o'], $v) : insc_formatear($c, $v)];
            }
        }
        $es_de_alumno = str_starts_with($sec['titulo'], 'Datos del/la') || in_array($sec['titulo'], ['Escuela de procedencia', 'Trayectoria escolar'], true);
        if ($es_de_alumno) $datos_alumno[]  = ['seccion' => $sec['titulo'], 'campos' => $filas];
        else               $datos_comunes[] = ['seccion' => $sec['titulo'], 'campos' => $filas];
    }

    // Declaración jurada
    $valores['acepto'] = !empty($post['acepto']);
    if (!$valores['acepto']) {
        $errores['acepto'] = 'Necesitamos que confirmes la declaración para enviar.';
    }

    $tutor = [
        'tutor_nombre' => $valores['tutor_nombre'] ?? '',
        'tutor_email'  => $valores['tutor_email'] ?? '',
        'tutor_tel'    => $valores['tutor_tel'] ?? '',
    ];

    $alumnos = [];

    if ($def['nivel'] !== null) {
        // ── Formulario individual ──
        $nivel = $def['nivel'];
        if (!isset($errores['anio']) && !isset($errores['modalidad'])
            && $anio_ord > 0 && !insc_anio_valido($nivel, $modalidad, $anio_ord)) {
            $errores['anio'] = 'La modalidad Orientada tiene 6 años: elegí de 1° a 6°.';
        }
        $alumnos[] = $tutor + [
            'nivel'           => $nivel,
            'modalidad'       => $modalidad,
            'anio'            => INSC_NIVELES[$nivel]['anios'][$anio_ord] ?? '',
            'anio_orden'      => $anio_ord,
            'alumno_nombre'   => $valores['alumno_nombre'] ?? '',
            'alumno_apellido' => $valores['alumno_apellido'] ?? '',
            'alumno_dni'      => $valores['alumno_dni'] ?? '',
            'alumno_fnac'     => ($valores['alumno_fnac'] ?? '') ?: null,
            'datos'           => array_merge($datos_alumno, $datos_comunes),
        ];
    } else {
        // ── Hermanos: un registro por hijo/a ──
        $hijos_post = is_array($post['hijos'] ?? null) ? array_values($post['hijos']) : [];
        $hijos_post = array_slice($hijos_post, 0, INSC_MAX_HIJOS);
        // Se descartan las filas totalmente vacías (el usuario agregó y no completó)
        $hijos_post = array_values(array_filter($hijos_post, function ($h) {
            return is_array($h) && implode('', array_map(fn($x) => is_string($x) ? trim($x) : '', $h)) !== '';
        }));

        $valores['hijos'] = [];
        if (count($hijos_post) < 2) {
            $errores['hijos'] = 'Cargá al menos dos hijos/as. Si inscribís a uno solo, usá el formulario de su nivel.';
        }
        $dnis = [];
        foreach ($hijos_post as $i => $h) {
            $vh = [];
            $filas = [];
            foreach (insc_campos_hijo() as $c) {
                [$v, $err] = insc_validar_campo($c, $h[$c['n']] ?? '');
                $vh[$c['n']] = $v;
                if ($err) $errores["hijos.$i.{$c['n']}"] = $err;
                if ($v !== '') {
                    $filas[] = [$c['l'], $c['t'] === 'select' ? insc_texto_opcion($c['o'], $v) : insc_formatear($c, $v)];
                }
            }
            $valores['hijos'][$i] = $vh;

            [$nivel, $ord] = array_pad(explode('|', $vh['nivel']), 2, '0');
            $ord = (int) $ord;
            $mod = 'General';
            if ($nivel === 'Secundario') {
                $mod = $vh['modalidad'];
                if ($mod === '') {
                    $errores["hijos.$i.modalidad"] = 'Para Secundario, elegí la modalidad.';
                } elseif ($ord > 0 && !insc_anio_valido($nivel, $mod, $ord)) {
                    $errores["hijos.$i.nivel"] = 'La modalidad Orientada tiene 6 años: elegí de 1° a 6°.';
                }
            }
            if ($vh['dni'] !== '' && in_array($vh['dni'], $dnis, true)) {
                $errores["hijos.$i.dni"] = 'Este DNI ya está cargado en otro hijo/a.';
            }
            $dnis[] = $vh['dni'];

            if (isset(INSC_NIVELES[$nivel])) {
                $alumnos[] = $tutor + [
                    'nivel'           => $nivel,
                    'modalidad'       => $mod,
                    'anio'            => INSC_NIVELES[$nivel]['anios'][$ord] ?? '',
                    'anio_orden'      => $ord,
                    'alumno_nombre'   => $vh['nombre'],
                    'alumno_apellido' => $vh['apellido'],
                    'alumno_dni'      => $vh['dni'],
                    'alumno_fnac'     => $vh['fnac'] ?: null,
                    'datos'           => array_merge([['seccion' => 'Datos del/la alumno/a', 'campos' => $filas]], $datos_comunes),
                ];
            }
        }
        if (!$valores['hijos']) {
            $valores['hijos'] = [[], []];   // para volver a mostrar dos bloques vacíos
        }
    }

    return ['valores' => $valores, 'errores' => $errores, 'alumnos' => $alumnos];
}

/** Formato legible para el panel (fechas en dd/mm/aaaa). */
function insc_formatear(array $c, string $v): string {
    if ($c['t'] === 'date' && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $v, $m)) {
        return "$m[3]/$m[2]/$m[1]";
    }
    return $v;
}

/** Guarda los alumnos de un envío. Devuelve el código de grupo. */
function insc_guardar(PDO $pdo, string $clave, array $alumnos, string $ip): string {
    $grupo = strtoupper(substr(bin2hex(random_bytes(6)), 0, 10));
    $st = $pdo->prepare(
        'INSERT INTO inscripciones
           (formulario, grupo, nivel, modalidad, anio, anio_orden,
            alumno_nombre, alumno_apellido, alumno_dni, alumno_fnac,
            tutor_nombre, tutor_email, tutor_tel, datos, ip)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
    );
    $pdo->beginTransaction();
    try {
        foreach ($alumnos as $a) {
            $st->execute([
                $clave, $grupo, $a['nivel'], $a['modalidad'], $a['anio'], $a['anio_orden'],
                $a['alumno_nombre'], $a['alumno_apellido'], $a['alumno_dni'], $a['alumno_fnac'],
                $a['tutor_nombre'], $a['tutor_email'], $a['tutor_tel'],
                json_encode($a['datos'], JSON_UNESCAPED_UNICODE), $ip,
            ]);
        }
        $pdo->commit();
    } catch (Throwable $ex) {
        $pdo->rollBack();
        throw $ex;
    }
    return $grupo;
}

/** ¿Demasiados envíos desde la misma IP en poco tiempo? (anti-spam simple) */
function insc_limite_superado(PDO $pdo, string $ip): bool {
    $st = $pdo->prepare(
        'SELECT COUNT(DISTINCT grupo) FROM inscripciones
          WHERE ip = ? AND creado_en > (NOW() - INTERVAL 15 MINUTE)'
    );
    $st->execute([$ip]);
    return (int) $st->fetchColumn() >= 6;
}
