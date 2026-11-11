<?php
// ============================================================
//  pagina_editar.php
//  Recibe lo que manda el editor de páginas (assets/js/editor-paginas.js)
//  y responde en JSON: {ok: true} o {ok: false, error: "…"}.
//
//  Acciones (campo "accion"):
//    textos          guarda los textos cambiados de una página
//    restaurar       vuelve un texto a su versión original
//    restaurar_todo  vuelve TODOS los textos de la página al original
//    bloque_guardar  crea o modifica un mensaje / PDF / información
//    bloque_borrar · bloque_mover · bloque_visible
//
//  Acceso: permiso "Edición de páginas" con la categoría de la página.
//  El permiso se vuelve a verificar acá en cada pedido: que el botón
//  no aparezca en la página no es una protección.
// ============================================================
require_once __DIR__ . '/panel_config.php';
require_once __DIR__ . '/partials/paginas.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function responder(bool $ok, string $error = '', array $extra = [], int $http = 200): never {
    http_response_code($http);
    echo json_encode(['ok' => $ok, 'error' => $error] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') responder(false, 'Método no permitido.', [], 405);
if (!esta_logueado())                      responder(false, 'La sesión venció. Volvé a ingresar al panel.', [], 401);

// Un archivo más grande que post_max_size llega con $_POST vacío
if (!$_POST && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    responder(false, 'El archivo es demasiado grande para el servidor. Probá con un PDF más liviano.', [], 413);
}
if (!is_string($_POST['csrf'] ?? null) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
    responder(false, 'La página estuvo abierta mucho tiempo. Recargala y volvé a intentar.', [], 400);
}

$slug   = (string) ($_POST['pagina'] ?? '');
$accion = (string) ($_POST['accion'] ?? '');
if (!isset(PAG_PAGINAS[$slug]))  responder(false, 'Página desconocida.', [], 404);
if (!pag_puede_editar($slug))    responder(false, 'Tu nivel de permisos no incluye la edición de esta página.', [], 403);

$pdo = db();
if (!pag_asegurar_tablas($pdo)) {
    responder(false, 'No se pudieron crear las tablas. Ejecutá sql/migracion_paginas.sql en phpMyAdmin.', [], 500);
}
$yo = (int) usuario_actual()['id_usuario'];

/** Bloque de ESTA página (un id de otra página no sirve). */
function bloque_de_pagina(PDO $pdo, string $slug, int $id): ?array {
    $st = $pdo->prepare('SELECT * FROM pagina_bloques WHERE id_bloque = ? AND pagina = ?');
    $st->execute([$id, $slug]);
    return $st->fetch() ?: null;
}

function borrar_archivo(string $ruta): void {
    // Solo dentro de la carpeta de archivos de páginas
    if ($ruta === '' || !str_starts_with($ruta, PAG_DIR_ARCHIVOS . '/') || str_contains($ruta, '..')) return;
    $fis = __DIR__ . '/' . $ruta;
    if (is_file($fis)) @unlink($fis);
}

switch ($accion) {

    // ── Textos ──────────────────────────────────────────────
    case 'textos':
        $cambios = json_decode((string) ($_POST['cambios'] ?? ''), true);
        if (!is_array($cambios) || !$cambios) responder(false, 'No hay cambios para guardar.');
        if (count($cambios) > 400)            responder(false, 'Son demasiados cambios juntos. Guardá de a partes.');

        $st = $pdo->prepare(
            'INSERT INTO pagina_textos (pagina, clave, contenido, original, id_usuario) VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE contenido = VALUES(contenido), id_usuario = VALUES(id_usuario)'
        );
        $n = 0;
        $pdo->beginTransaction();
        try {
            foreach ($cambios as $c) {
                $clave = (string) ($c['clave'] ?? '');
                if (!preg_match('~^[a-f0-9]{10}(-\d{1,3})?$~', $clave)) continue;
                $st->execute([
                    $slug, $clave,
                    pag_sanear_html((string) ($c['html'] ?? '')),
                    pag_extracto((string) ($c['original'] ?? '')),
                    $yo,
                ]);
                $n++;
            }
            $pdo->commit();
        } catch (Throwable $ex) {
            $pdo->rollBack();
            error_log('[juan23] textos de página: ' . $ex->getMessage());
            responder(false, 'No se pudieron guardar los cambios. Probá de nuevo.', [], 500);
        }
        responder(true, '', ['guardados' => $n]);

    case 'restaurar':
        $pdo->prepare('DELETE FROM pagina_textos WHERE pagina = ? AND clave = ?')
            ->execute([$slug, (string) ($_POST['clave'] ?? '')]);
        responder(true);

    case 'restaurar_todo':
        $pdo->prepare('DELETE FROM pagina_textos WHERE pagina = ?')->execute([$slug]);
        responder(true);

    // ── Bloques ─────────────────────────────────────────────
    case 'bloque_guardar':
        if ((PAG_PAGINAS[$slug]['bloques'] ?? true) === false) responder(false, 'Esta página no admite contenido agregado.');

        $id     = (int) ($_POST['id'] ?? 0);
        $actual = $id ? bloque_de_pagina($pdo, $slug, $id) : null;
        if ($id && !$actual) responder(false, 'El contenido ya no existe. Recargá la página.');

        $t = fn(string $k, int $max): string => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max);
        $tipo    = $actual['tipo'] ?? $t('tipo', 20);            // el tipo no cambia al editar
        $seccion = $actual['seccion'] ?? $t('seccion', 60);
        $titulo  = $t('titulo', 160);
        $texto   = mb_substr(trim(str_replace("\r\n", "\n", (string) ($_POST['texto'] ?? ''))), 0, 5000);
        $estilo  = isset(PAG_ESTILOS[$t('estilo', 20)]) ? $t('estilo', 20) : 'info';
        $enlace  = $t('enlace', 300);
        $enl_txt = $t('enlace_texto', 80);
        $hasta   = $t('hasta', 10);

        if (!isset(PAG_TIPOS_BLOQUE[$tipo]))                 responder(false, 'Elegí qué querés agregar.');
        if (!preg_match('~^[\w\-]{1,60}$~u', $seccion))      responder(false, 'Sección inválida.');
        if ($hasta !== '') {
            $d = DateTime::createFromFormat('!Y-m-d', $hasta);
            if (!$d || $d->format('Y-m-d') !== $hasta)       responder(false, 'La fecha «Mostrar hasta» no es válida.');
        }
        if ($tipo !== 'pdf' && $texto === '')                responder(false, 'Escribí el texto.');
        if ($tipo === 'info' && $titulo === '')              responder(false, 'La información necesita un título.');
        if ($tipo === 'pdf') { $enlace = $enl_txt = ''; }
        if ($tipo === 'mensaje') { $enlace = $enl_txt = ''; }
        if (!pag_enlace_valido($enlace))                     responder(false, 'El enlace no es válido. Tiene que empezar con https:// o ser una página del sitio (por ejemplo: contacto).');

        // PDF: obligatorio al crear, opcional al modificar
        $archivo = $actual['archivo'] ?? '';
        $arch_nombre = $actual['archivo_nombre'] ?? '';
        $arch_peso = (int) ($actual['archivo_peso'] ?? 0);
        $archivo_viejo = '';
        $f = $_FILES['archivo'] ?? null;
        $subio = $f && ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

        if ($tipo === 'pdf' && $subio) {
            if ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE || $f['size'] > PAG_PDF_MAX) {
                responder(false, 'El PDF pesa más de ' . pag_peso_legible(PAG_PDF_MAX) . '. Probá con uno más liviano.');
            }
            if ($f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
                responder(false, 'No se pudo subir el archivo. Probá de nuevo.');
            }
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
            $cabecera = (string) file_get_contents($f['tmp_name'], false, null, 0, 5);
            if ($mime !== 'application/pdf' || $cabecera !== '%PDF-') {
                responder(false, 'El archivo tiene que ser un PDF.');
            }
            $dir = __DIR__ . '/' . PAG_DIR_ARCHIVOS;
            if (!is_dir($dir)) @mkdir($dir, 0775, true);
            $nombre = $slug . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.pdf';
            if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $nombre)) {
                responder(false, 'No se pudo guardar el archivo en el servidor (revisá los permisos de la carpeta ' . PAG_DIR_ARCHIVOS . ').', [], 500);
            }
            $archivo_viejo = $archivo;
            $archivo     = PAG_DIR_ARCHIVOS . '/' . $nombre;
            $arch_nombre = mb_substr(basename((string) $f['name']), 0, 160);
            $arch_peso   = (int) $f['size'];
        }
        if ($tipo === 'pdf' && $archivo === '') responder(false, 'Elegí el archivo PDF.');
        if ($tipo === 'pdf' && $titulo === '') {
            $titulo = mb_substr(preg_replace('~\.pdf$~i', '', $arch_nombre), 0, 160);
        }

        if ($actual) {
            $pdo->prepare(
                'UPDATE pagina_bloques SET estilo = ?, titulo = ?, texto = ?, archivo = ?, archivo_nombre = ?, archivo_peso = ?,
                        enlace = ?, enlace_texto = ?, hasta = ?, id_usuario = ? WHERE id_bloque = ?'
            )->execute([$estilo, $titulo, $texto, $archivo, $arch_nombre, $arch_peso, $enlace, $enl_txt, $hasta ?: null, $yo, $id]);
            borrar_archivo($archivo_viejo);
        } else {
            $st = $pdo->prepare('SELECT COALESCE(MAX(orden), 0) + 1 FROM pagina_bloques WHERE pagina = ? AND seccion = ?');
            $st->execute([$slug, $seccion]);
            $orden = (int) $st->fetchColumn();
            $pdo->prepare(
                'INSERT INTO pagina_bloques (pagina, seccion, tipo, estilo, titulo, texto, archivo, archivo_nombre, archivo_peso,
                                             enlace, enlace_texto, orden, hasta, id_usuario)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            )->execute([$slug, $seccion, $tipo, $estilo, $titulo, $texto, $archivo, $arch_nombre, $arch_peso,
                        $enlace, $enl_txt, $orden, $hasta ?: null, $yo]);
        }
        responder(true);

    case 'bloque_borrar':
        $b = bloque_de_pagina($pdo, $slug, (int) ($_POST['id'] ?? 0));
        if ($b) {
            $pdo->prepare('DELETE FROM pagina_bloques WHERE id_bloque = ?')->execute([$b['id_bloque']]);
            borrar_archivo($b['archivo']);
        }
        responder(true);

    case 'bloque_visible':
        $b = bloque_de_pagina($pdo, $slug, (int) ($_POST['id'] ?? 0));
        if (!$b) responder(false, 'El contenido ya no existe. Recargá la página.');
        $pdo->prepare('UPDATE pagina_bloques SET visible = 1 - visible, id_usuario = ? WHERE id_bloque = ?')
            ->execute([$yo, $b['id_bloque']]);
        responder(true);

    case 'bloque_mover':
        $b = bloque_de_pagina($pdo, $slug, (int) ($_POST['id'] ?? 0));
        if (!$b) responder(false, 'El contenido ya no existe. Recargá la página.');
        $sube = ($_POST['dir'] ?? '') === 'arriba';
        // Se renumera la sección entera: así no hay empates de orden
        $st = $pdo->prepare('SELECT id_bloque FROM pagina_bloques WHERE pagina = ? AND seccion = ? ORDER BY orden, id_bloque');
        $st->execute([$slug, $b['seccion']]);
        $ids = array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN));
        $i = array_search((int) $b['id_bloque'], $ids, true);
        $j = $sube ? $i - 1 : $i + 1;
        if ($i !== false && isset($ids[$j])) {
            [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
            $up = $pdo->prepare('UPDATE pagina_bloques SET orden = ? WHERE id_bloque = ?');
            foreach ($ids as $k => $idb) $up->execute([$k + 1, $idb]);
        }
        responder(true);
}

responder(false, 'Acción desconocida.', [], 400);
