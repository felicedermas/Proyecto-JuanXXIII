<?php
// ============================================================
//  partials/gestion_egresados.php
//  Panel de Control — Historias de egresados
//
//  Se muestra como pestaña dentro de gestion_novedades.php
//  (gestion_novedades?tipo=egresados). Lo publicado aparece en
//  egresados.php (grilla) y egresado.php (detalle).
//
//  Tablas: egresados e imagenes_egresados. El dump original las
//  creó sin clave primaria ni AUTO_INCREMENT: egr_asegurar_tablas()
//  lo corrige solo (ver también migracion_egresados.sql).
//
//  Crear: permiso "Publicación de novedades".
//  Editar / borrar: el autor o un administrador.
//
//  Requiere que panel_config.php ya esté incluido, el usuario
//  logueado y $pdo / $u definidos (lo hace gestion_novedades.php).
// ============================================================

if (!function_exists('usuario_actual') || !isset($pdo, $u)) {   // abierto directo por URL
    http_response_code(404);
    exit;
}

const EGR_CARPETA      ='img/egresados/subidas';     // relativa a la raíz del sitio
const EGR_MAX_BYTES    = 5 * 1024 * 1024;             // 5 MB por imagen
const EGR_TIPOS        = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
const EGR_ORIENTACIONES = ['Bachillerato Orientado', 'Técnica'];
const EGR_ANIO_MIN     = 1962;                        // primera promoción posible
const EGR_URL          = 'gestion_novedades?tipo=egresados';

/** Agrega PK + AUTO_INCREMENT y la columna de autor si faltan. */
function egr_asegurar_tablas(PDO $pdo): bool {
    try {
        foreach (['egresados' => 'id_egresado', 'imagenes_egresados' => 'id_imagen'] as $tabla => $pk) {
            $tiene_pk = (bool) $pdo->query("SHOW INDEX FROM $tabla WHERE Key_name = 'PRIMARY'")->fetch();
            $col      = $pdo->query("SHOW COLUMNS FROM $tabla LIKE '$pk'")->fetch();
            $es_auto  = $col && stripos((string) $col['Extra'], 'auto_increment') !== false;
            if (!$tiene_pk) {
                $pdo->exec("ALTER TABLE $tabla MODIFY $pk INT(10) UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY ($pk)");
            } elseif (!$es_auto) {
                $pdo->exec("ALTER TABLE $tabla MODIFY $pk INT(10) UNSIGNED NOT NULL AUTO_INCREMENT");
            }
        }
        if (!$pdo->query("SHOW INDEX FROM imagenes_egresados WHERE Key_name = 'idx_egresado'")->fetch()) {
            $pdo->exec('ALTER TABLE imagenes_egresados ADD KEY idx_egresado (id_egresado, orden)');
        }
        if (!$pdo->query("SHOW COLUMNS FROM egresados LIKE 'id_usuario'")->fetch()) {
            $pdo->exec("ALTER TABLE egresados ADD COLUMN id_usuario INT NULL COMMENT 'Quién la cargó desde el panel' AFTER publicado");
        }
        return true;
    } catch (Throwable $ex) {
        error_log('[juan23] egresados: ' . $ex->getMessage());
        return false;
    }
}

/** Texto plano del panel → HTML seguro (párrafos separados por línea en blanco). */
function egr_texto_a_html(string $txt): string {
    $txt = str_replace(["\r\n", "\r"], "\n", trim($txt));
    $html = [];
    foreach (preg_split('/\n\s*\n/', $txt) as $parrafo) {
        $parrafo = trim($parrafo);
        if ($parrafo !== '') $html[] = '<p>' . nl2br(e($parrafo), false) . '</p>';
    }
    return implode("\n", $html);
}

/** HTML guardado → texto plano para editar. */
function egr_html_a_texto(string $html): string {
    $t = preg_replace('#</p>\s*<p[^>]*>#i', "\n\n", $html);
    $t = preg_replace('#<br\s*/?>\n?#i', "\n", $t);
    return trim(html_entity_decode(strip_tags($t), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
}

/**
 * Guarda UN archivo subido (ya normalizado: name, tmp_name, error, size).
 * Devuelve [url_relativa|null, error|null]. Sin archivo → [null, null].
 */
function egr_subir_imagen(array $f, string $prefijo): array {
    if ($f['error'] === UPLOAD_ERR_NO_FILE) return [null, null];
    $nombre = (string) $f['name'];
    if ($f['error'] !== UPLOAD_ERR_OK)      return [null, "No se pudo subir «{$nombre}» (código {$f['error']})."];
    if ((int) $f['size'] > EGR_MAX_BYTES)   return [null, "«{$nombre}» supera el tamaño máximo de 5 MB."];

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    if (!isset(EGR_TIPOS[$mime]))           return [null, "«{$nombre}» no es una imagen válida (solo JPG, PNG, WEBP o GIF)."];

    $dir = dirname(__DIR__) . '/' . EGR_CARPETA;
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    if (!is_dir($dir) || !is_writable($dir)) return [null, 'No se pudo escribir en la carpeta de imágenes (' . EGR_CARPETA . '). Verificá los permisos.'];

    $final = $prefijo . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . EGR_TIPOS[$mime];
    if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $final)) return [null, "No se pudo guardar «{$nombre}»."];
    return [EGR_CARPETA . '/' . $final, null];
}

/** Borra el archivo físico solo si es una subida del panel. */
function egr_borrar_archivo(?string $url): void {
    if ($url && str_starts_with($url, EGR_CARPETA . '/')) {
        $ruta = dirname(__DIR__) . '/' . $url;
        if (is_file($ruta)) @unlink($ruta);
    }
}

/** Procesa foto de perfil y galería del POST actual. Devuelve errores. */
function egr_procesar_imagenes(PDO $pdo, int $id, string $alt): array {
    $errs = [];

    // Foto de perfil (reemplaza la anterior)
    if (!empty($_FILES['foto_perfil']) && !is_array($_FILES['foto_perfil']['name'])) {
        [$url, $err] = egr_subir_imagen($_FILES['foto_perfil'], 'egr' . $id . '_perfil');
        if ($err) $errs[] = $err;
        if ($url) {
            $q = $pdo->prepare('SELECT foto_perfil FROM egresados WHERE id_egresado = ?');
            $q->execute([$id]);
            egr_borrar_archivo($q->fetchColumn() ?: null);
            $pdo->prepare('UPDATE egresados SET foto_perfil = ? WHERE id_egresado = ?')->execute([$url, $id]);
        }
    }

    // Galería (se agregan al final)
    if (!empty($_FILES['galeria']) && is_array($_FILES['galeria']['name'])) {
        $q = $pdo->prepare('SELECT COALESCE(MAX(orden), -1) FROM imagenes_egresados WHERE id_egresado = ?');
        $q->execute([$id]);
        $orden = (int) $q->fetchColumn();
        $ins = $pdo->prepare('INSERT INTO imagenes_egresados (id_egresado, url_imagen, alt_text, orden) VALUES (?, ?, ?, ?)');
        foreach (array_keys($_FILES['galeria']['name']) as $i) {
            $f = [];
            foreach (['name', 'tmp_name', 'error', 'size'] as $k) $f[$k] = $_FILES['galeria'][$k][$i];
            [$url, $err] = egr_subir_imagen($f, 'egr' . $id);
            if ($err) $errs[] = $err;
            if ($url) $ins->execute([$id, $url, mb_substr($alt, 0, 200), min(++$orden, 255)]);
        }
    }
    return $errs;
}

/** Autor de una historia (0 = cargada fuera del panel → solo admin), o null si no existe. */
function egr_autor(PDO $pdo, int $id): ?int {
    $q = $pdo->prepare('SELECT COALESCE(id_usuario, 0) FROM egresados WHERE id_egresado = ?');
    $q->execute([$id]);
    $a = $q->fetchColumn();
    return $a === false ? null : (int) $a;
}

// ============================================================
if (!egr_asegurar_tablas($pdo)) {
    die('<p style="font-family:sans-serif;padding:2rem;color:#c1121f;">No se pudieron preparar las tablas de egresados. Ejecutá <b>migracion_egresados.sql</b> en phpMyAdmin.</p>');
}
$puede_crear = puede('pub_novedades');

// ── Quitar una foto de la galería ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'borrar_imagen') {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);
    $autor = egr_autor($pdo, $id);
    if ($autor !== null && puede_gestionar($autor)) {
        $q = $pdo->prepare('SELECT url_imagen FROM imagenes_egresados WHERE id_imagen = ? AND id_egresado = ?');
        $q->execute([(int) ($_POST['id_imagen'] ?? 0), $id]);
        if (($url = $q->fetchColumn()) !== false) {
            $pdo->prepare('DELETE FROM imagenes_egresados WHERE id_imagen = ?')->execute([(int) $_POST['id_imagen']]);
            egr_borrar_archivo($url);
        }
        flash('ok', 'Imagen quitada.');
    } else {
        flash('error', 'No tenés permiso para modificar esa historia.');
    }
    header('Location: ' . EGR_URL . '&editar=' . $id);
    exit;
}

// ── Borrar una historia ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'borrar') {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);
    $autor = egr_autor($pdo, $id);
    if ($autor === null) {
        flash('error', 'La historia no existe.');
    } elseif (!puede_gestionar($autor)) {
        flash('error', 'No tenés permiso para borrar esa historia.');
    } else {
        $q = $pdo->prepare('SELECT url_imagen FROM imagenes_egresados WHERE id_egresado = ?');
        $q->execute([$id]);
        foreach ($q->fetchAll(PDO::FETCH_COLUMN) as $url) egr_borrar_archivo($url);
        $q = $pdo->prepare('SELECT foto_perfil FROM egresados WHERE id_egresado = ?');
        $q->execute([$id]);
        egr_borrar_archivo($q->fetchColumn() ?: null);
        $pdo->prepare('DELETE FROM imagenes_egresados WHERE id_egresado = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM egresados WHERE id_egresado = ?')->execute([$id]);
        flash('ok', 'Historia eliminada correctamente.');
    }
    header('Location: ' . EGR_URL);
    exit;
}

// ── Alta / edición ──
$errores = [];
$modo    = 'crear';
$vacio   = ['id_egresado' => 0, 'nombre' => '', 'apellido' => '', 'anio_egreso' => '', 'orientacion' => 'Bachillerato Orientado',
            'subtitulo' => '', 'resumen' => '', 'cuerpo' => '', 'foto_perfil' => null, 'publicado' => 1];
$edit    = $vacio;
$galeria = [];

if (isset($_GET['editar'])) {
    $q = $pdo->prepare('SELECT * FROM egresados WHERE id_egresado = ?');
    $q->execute([(int) $_GET['editar']]);
    $row = $q->fetch();
    if ($row && puede_gestionar((int) ($row['id_usuario'] ?? 0))) {
        $modo = 'editar';
        $edit = $row;
        $edit['cuerpo'] = egr_html_a_texto((string) $row['cuerpo']);
        $q = $pdo->prepare('SELECT id_imagen, url_imagen, alt_text FROM imagenes_egresados WHERE id_egresado = ? ORDER BY orden, id_imagen');
        $q->execute([(int) $row['id_egresado']]);
        $galeria = $q->fetchAll();
    } else {
        flash('error', 'No podés editar esa historia.');
        header('Location: ' . EGR_URL);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['accion'] ?? '', ['crear', 'editar'], true)) {
    csrf_check();
    $accion = $_POST['accion'];
    $id     = (int) ($_POST['id'] ?? 0);
    $t      = fn(string $k): string => trim((string) ($_POST[$k] ?? ''));
    $v = [
        'id_egresado' => $id,
        'nombre'      => $t('nombre'),
        'apellido'    => $t('apellido'),
        'anio_egreso' => $t('anio_egreso'),
        'orientacion' => $t('orientacion'),
        'subtitulo'   => $t('subtitulo'),
        'resumen'     => $t('resumen'),
        'cuerpo'      => $t('cuerpo'),
        'publicado'   => isset($_POST['publicado']) ? 1 : 0,
    ];

    if ($v['nombre'] === '' || $v['apellido'] === '')        $errores[] = 'Nombre y apellido son obligatorios.';
    if (mb_strlen($v['nombre']) > 80 || mb_strlen($v['apellido']) > 80) $errores[] = 'Nombre y apellido admiten hasta 80 caracteres cada uno.';
    if (!ctype_digit($v['anio_egreso']) || (int) $v['anio_egreso'] < EGR_ANIO_MIN || (int) $v['anio_egreso'] > (int) date('Y'))
        $errores[] = 'El año de egreso tiene que estar entre ' . EGR_ANIO_MIN . ' y ' . date('Y') . '.';
    if (!in_array($v['orientacion'], EGR_ORIENTACIONES, true)) $errores[] = 'Orientación inválida.';
    if ($v['subtitulo'] === '')                                $errores[] = 'El subtítulo es obligatorio (ej.: «Médica · Universidad Austral»).';
    if (mb_strlen($v['subtitulo']) > 220)                      $errores[] = 'El subtítulo admite hasta 220 caracteres.';
    if ($v['resumen'] === '')                                  $errores[] = 'El resumen es obligatorio.';
    if (mb_strlen($v['resumen']) > 600)                        $errores[] = 'El resumen es demasiado largo (máx. 600 caracteres; ideal unos 300).';
    if ($v['cuerpo'] === '')                                   $errores[] = 'La historia completa es obligatoria.';

    if ($accion === 'crear' && !$puede_crear) {
        $errores[] = 'Tu nivel de permisos no incluye la publicación de novedades.';
    }
    if ($accion === 'editar') {
        $autor = egr_autor($pdo, $id);
        if ($autor === null || !puede_gestionar($autor)) {
            flash('error', 'No tenés permiso para editar esa historia.');
            header('Location: ' . EGR_URL);
            exit;
        }
    }

    if (!$errores) {
        $datos = [
            ':n' => $v['nombre'], ':a' => $v['apellido'], ':y' => (int) $v['anio_egreso'], ':o' => $v['orientacion'],
            ':s' => $v['subtitulo'], ':r' => $v['resumen'], ':c' => egr_texto_a_html($v['cuerpo']), ':p' => $v['publicado'],
        ];
        if ($accion === 'crear') {
            $pdo->prepare('INSERT INTO egresados (nombre, apellido, anio_egreso, orientacion, subtitulo, resumen, cuerpo, publicado, id_usuario)
                           VALUES (:n, :a, :y, :o, :s, :r, :c, :p, :u)')
                ->execute($datos + [':u' => $u['id_usuario']]);
            $id = (int) $pdo->lastInsertId();
        } else {
            $pdo->prepare('UPDATE egresados SET nombre = :n, apellido = :a, anio_egreso = :y, orientacion = :o,
                                  subtitulo = :s, resumen = :r, cuerpo = :c, publicado = :p
                            WHERE id_egresado = :id')
                ->execute($datos + [':id' => $id]);
            if (isset($_POST['quitar_foto'])) {
                $q = $pdo->prepare('SELECT foto_perfil FROM egresados WHERE id_egresado = ?');
                $q->execute([$id]);
                egr_borrar_archivo($q->fetchColumn() ?: null);
                $pdo->prepare('UPDATE egresados SET foto_perfil = NULL WHERE id_egresado = ?')->execute([$id]);
            }
        }

        $errs_img = egr_procesar_imagenes($pdo, $id, $v['nombre'] . ' ' . $v['apellido']);
        $que = $accion === 'crear' ? 'Historia creada' : 'Cambios guardados';
        $estado = $v['publicado'] ? '' : ' Quedó como borrador: no se ve en el sitio.';
        if ($errs_img) {
            flash('error', "$que, pero con avisos en las imágenes: " . implode(' ', $errs_img) . $estado);
        } else {
            flash('ok', "$que correctamente." . $estado);
        }
        header('Location: ' . EGR_URL);
        exit;
    }

    // Reponer lo escrito para corregir
    $modo = $accion;
    $foto_actual = null;
    if ($accion === 'editar') {
        $q = $pdo->prepare('SELECT foto_perfil FROM egresados WHERE id_egresado = ?');
        $q->execute([$id]);
        $foto_actual = $q->fetchColumn() ?: null;
        $q = $pdo->prepare('SELECT id_imagen, url_imagen, alt_text FROM imagenes_egresados WHERE id_egresado = ? ORDER BY orden, id_imagen');
        $q->execute([$id]);
        $galeria = $q->fetchAll();
    }
    $edit = $v + ['foto_perfil' => $foto_actual];
}

// ── Listado ──
$lista = $pdo->query(
    'SELECT e.id_egresado, e.nombre, e.apellido, e.anio_egreso, e.orientacion, e.subtitulo, e.foto_perfil,
            e.publicado, e.created_at, COALESCE(e.id_usuario, 0) AS id_usuario,
            CONCAT(us.nombre, " ", us.apellido) AS autor,
            (SELECT COUNT(*) FROM imagenes_egresados im WHERE im.id_egresado = e.id_egresado) AS n_imagenes,
            (SELECT im.url_imagen FROM imagenes_egresados im WHERE im.id_egresado = e.id_egresado ORDER BY im.orden LIMIT 1) AS primera_imagen
       FROM egresados e LEFT JOIN usuarios us ON us.id_usuario = e.id_usuario
      ORDER BY e.created_at DESC'
)->fetchAll();

$mostrar_form = $modo === 'editar' || $puede_crear;

$panel_page_title = 'Historias de egresados';
$panel_heading    = 'Gestión de <span>Novedades</span>';
$panel_sub        = 'Contá la historia de ex-alumnos: aparece en la sección Egresados del sitio.';
require dirname(__DIR__) . '/panel_header.php';
?>

    <div class="panel-toolbar">
      <a href="panel" class="back-link">
        <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg> Volver al panel
      </a>
      <a href="egresados" target="_blank" rel="noopener" class="tipo-link-sitio">Ver Egresados en el sitio ↗</a>
    </div>

    <nav class="tipo-tabs" aria-label="Tipo de publicación">
      <a href="gestion_novedades">Novedades</a>
      <a href="<?= EGR_URL ?>" class="act" aria-current="page">Historias de egresados</a>
    </nav>

    <?php if ($mostrar_form): ?>
    <div class="form-card" style="margin-bottom:2.5rem;">
      <h2 style="font-family:var(--font-display);color:var(--blue-dark);font-size:1.35rem;margin-bottom:.4rem;">
        <?= $modo === 'editar' ? 'Editar historia de ' . e($edit['nombre'] . ' ' . $edit['apellido']) : 'Nueva historia de egresado/a' ?>
      </h2>
      <p style="color:#667;font-size:.86rem;margin-bottom:1.3rem;">
        El <b>resumen</b> se ve en la grilla de Egresados; la <b>historia completa</b>, al entrar a la ficha.
      </p>

      <?php if ($errores): ?>
        <div class="alert alert-error"><?= implode('<br>', array_map('e', $errores)) ?></div>
      <?php endif; ?>

      <form method="post" action="<?= EGR_URL ?>" enctype="multipart/form-data">
        <?= csrf_input() ?>
        <input type="hidden" name="accion" value="<?= $modo === 'editar' ? 'editar' : 'crear' ?>">
        <input type="hidden" name="id" value="<?= (int) $edit['id_egresado'] ?>">

        <div class="field-row">
          <div class="field">
            <label for="egr-nombre">Nombre</label>
            <input type="text" id="egr-nombre" name="nombre" maxlength="80" required value="<?= e((string) $edit['nombre']) ?>" placeholder="Ej.: Lucía">
          </div>
          <div class="field">
            <label for="egr-apellido">Apellido</label>
            <input type="text" id="egr-apellido" name="apellido" maxlength="80" required value="<?= e((string) $edit['apellido']) ?>" placeholder="Ej.: Gómez">
          </div>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="egr-anio">Año de egreso</label>
            <input type="number" id="egr-anio" name="anio_egreso" min="<?= EGR_ANIO_MIN ?>" max="<?= date('Y') ?>" required
                   value="<?= e((string) $edit['anio_egreso']) ?>" placeholder="Ej.: 2010" class="egr-num">
          </div>
          <div class="field">
            <label for="egr-orientacion">Orientación</label>
            <select id="egr-orientacion" name="orientacion">
              <?php foreach (EGR_ORIENTACIONES as $o): ?>
                <option<?= $edit['orientacion'] === $o ? ' selected' : '' ?>><?= e($o) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="field">
          <label for="egr-subtitulo">Subtítulo <span class="hint">(a qué se dedica hoy)</span></label>
          <input type="text" id="egr-subtitulo" name="subtitulo" maxlength="220" required
                 value="<?= e((string) $edit['subtitulo']) ?>" placeholder="Ej.: Médica cirujana · Hospital Universitario Austral">
        </div>

        <div class="field">
          <label for="egr-resumen">Resumen <span class="hint">(para la grilla · ideal unos 300 caracteres)</span></label>
          <textarea id="egr-resumen" name="resumen" maxlength="600" required style="min-height:90px;"
                    placeholder="Dos o tres líneas que inviten a leer la historia completa."><?= e((string) $edit['resumen']) ?></textarea>
          <span class="hint egr-contador" data-para="egr-resumen" data-ideal="300"></span>
        </div>

        <div class="field">
          <label for="egr-cuerpo">Historia completa <span class="hint">(dejá una línea en blanco entre párrafos)</span></label>
          <textarea id="egr-cuerpo" name="cuerpo" required style="min-height:260px;"
                    placeholder="Contá su recorrido desde que egresó, qué hace hoy y qué recuerda del colegio…"><?= e((string) $edit['cuerpo']) ?></textarea>
        </div>

        <div class="field">
          <label for="egr-foto">Foto de perfil <span class="hint">(opcional · JPG, PNG, WEBP o GIF · hasta 5 MB)</span></label>
          <?php if (!empty($edit['foto_perfil'])): ?>
            <div class="egr-foto-actual">
              <img src="<?= e($edit['foto_perfil']) ?>" alt="Foto de perfil actual">
              <label class="egr-quitar"><input type="checkbox" name="quitar_foto" value="1"> Quitar foto actual</label>
              <span class="hint">Si subís otra, reemplaza a la actual.</span>
            </div>
          <?php endif; ?>
          <input type="file" id="egr-foto" name="foto_perfil" accept="image/jpeg,image/png,image/webp,image/gif">
        </div>

        <?php if ($modo === 'editar' && $galeria): ?>
          <div class="field">
            <label>Galería actual</label>
            <div class="img-gallery">
              <?php foreach ($galeria as $img): ?>
                <div class="img-thumb">
                  <img src="<?= e($img['url_imagen']) ?>" alt="<?= e((string) $img['alt_text']) ?>">
                  <button type="submit" class="img-del" form="form-borrar-egr-img-<?= (int) $img['id_imagen'] ?>"
                          title="Quitar esta imagen" onclick="return confirm('¿Quitar esta imagen?');">×</button>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <div class="field">
          <label for="egr-galeria">
            <?= $modo === 'editar' ? 'Agregar fotos a la galería' : 'Galería de fotos' ?>
            <span class="hint">(opcional · se muestran en la ficha · podés elegir varias)</span>
          </label>
          <input type="file" id="egr-galeria" name="galeria[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
          <?php if ($errores): ?>
            <p class="hint" style="color:var(--red);margin-top:.4rem;">Por seguridad del navegador, si hubo un error tenés que volver a seleccionar las imágenes.</p>
          <?php endif; ?>
        </div>

        <label class="egr-publicar">
          <input type="checkbox" name="publicado" value="1" <?= (int) $edit['publicado'] === 1 ? 'checked' : '' ?>>
          <span><b>Publicar en el sitio</b> — si lo destildás, queda guardada como borrador.</span>
        </label>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">
            <?= $modo === 'editar' ? 'Guardar cambios' : 'Crear historia' ?>
          </button>
          <?php if ($modo === 'editar'): ?>
            <a href="<?= EGR_URL ?>" class="btn btn-secundario">Cancelar</a>
          <?php endif; ?>
        </div>
      </form>

      <?php foreach ($galeria as $img): ?>
        <form method="post" action="<?= EGR_URL ?>" id="form-borrar-egr-img-<?= (int) $img['id_imagen'] ?>" style="display:none;">
          <?= csrf_input() ?>
          <input type="hidden" name="accion" value="borrar_imagen">
          <input type="hidden" name="id" value="<?= (int) $edit['id_egresado'] ?>">
          <input type="hidden" name="id_imagen" value="<?= (int) $img['id_imagen'] ?>">
        </form>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <div class="alert alert-info">Tu nivel de permisos no incluye la publicación de novedades, así que no podés crear historias de egresados.</div>
    <?php endif; ?>

    <h2 style="font-family:var(--font-display);color:var(--blue-dark);font-size:1.25rem;margin-bottom:1rem;">
      Historias cargadas
    </h2>

    <?php if (!$lista): ?>
      <div class="empty-state">
        <svg viewBox="0 0 24 24"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
        <p>Todavía no hay historias de egresados.</p>
      </div>
    <?php else: ?>
      <div style="overflow-x:auto;">
      <table class="mng-table">
        <thead>
          <tr><th>Egresado/a</th><th>Promoción</th><th>Estado</th><th>Fotos</th><th>Autor</th><th>Fecha</th><th style="text-align:right;">Acciones</th></tr>
        </thead>
        <tbody>
        <?php foreach ($lista as $row):
          $puede = puede_gestionar((int) $row['id_usuario']);
          $foto  = $row['foto_perfil'] ?: $row['primera_imagen'];
          $ini   = mb_strtoupper(mb_substr($row['nombre'], 0, 1) . mb_substr($row['apellido'], 0, 1)); ?>
          <tr>
            <td>
              <div class="egr-quien">
                <span class="egr-avatar"><?php if ($foto): ?><img src="<?= e($foto) ?>" alt="" onerror="this.remove()"><?php endif; ?><?= e($ini) ?></span>
                <span>
                  <b><?= e($row['nombre'] . ' ' . $row['apellido']) ?></b>
                  <small><?= e($row['subtitulo']) ?></small>
                </span>
              </div>
            </td>
            <td style="white-space:nowrap;">
              <?= e((string) $row['anio_egreso']) ?>
              <small class="egr-sub"><?= e($row['orientacion']) ?></small>
            </td>
            <td>
              <?php if ((int) $row['publicado'] === 1): ?>
                <span class="mng-badge" style="background:#1b7a44;">Publicada</span>
              <?php else: ?>
                <span class="mng-badge" style="background:#9aa0a6;">Borrador</span>
              <?php endif; ?>
            </td>
            <td style="color:#667;"><?= (int) $row['n_imagenes'] + ($row['foto_perfil'] ? 1 : 0) ?: '<span style="color:#bbb;">—</span>' ?></td>
            <td><?= $row['autor'] ? e($row['autor']) : '<span style="color:#9aa0a6;">Carga inicial</span>' ?></td>
            <td style="color:#667;white-space:nowrap;"><?= e(date('d/m/Y', strtotime($row['created_at']))) ?></td>
            <td>
              <div class="mng-actions" style="justify-content:flex-end;">
                <?php if ((int) $row['publicado'] === 1): ?>
                  <a href="egresado?id=<?= (int) $row['id_egresado'] ?>" target="_blank" rel="noopener" class="icon-btn" title="Ver en el sitio">
                    <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </a>
                <?php endif; ?>
                <?php if ($puede): ?>
                  <a href="<?= EGR_URL ?>&amp;editar=<?= (int) $row['id_egresado'] ?>" class="icon-btn" title="Editar">
                    <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                  </a>
                  <form method="post" action="<?= EGR_URL ?>" style="display:inline;"
                        onsubmit="return confirm('¿Eliminar la historia de <?= e(addslashes($row['nombre'] . ' ' . $row['apellido'])) ?>? No se puede deshacer.');">
                    <?= csrf_input() ?>
                    <input type="hidden" name="accion" value="borrar">
                    <input type="hidden" name="id" value="<?= (int) $row['id_egresado'] ?>">
                    <button type="submit" class="icon-btn danger" title="Eliminar">
                      <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                  </form>
                <?php else: ?>
                  <button class="icon-btn" disabled title="Solo el autor o un admin pueden gestionarla">
                    <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                  </button>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    <?php endif; ?>

<style>
  .egr-num { max-width: 100%; }
  .field input[type=number] {
    width: 100%; font-family: var(--font-body); font-size: .95rem; color: var(--text); background: #fff;
    border: 1.5px solid #dfe3e8; border-radius: var(--radius); padding: .7rem .85rem; transition: var(--transition);
  }
  .egr-contador { display: block; margin-top: .3rem; }
  .egr-contador.pasado { color: #9a6700; }
  .egr-foto-actual { display: flex; align-items: center; gap: .9rem; flex-wrap: wrap; margin-bottom: .6rem; }
  .egr-foto-actual img { width: 72px; height: 72px; border-radius: 50%; object-fit: cover; box-shadow: var(--shadow-sm); }
  .egr-foto-actual .hint { flex-basis: 100%; }
  .egr-quitar { display: inline-flex !important; align-items: center; gap: .4rem; font-weight: 600 !important; margin: 0 !important; cursor: pointer; }
  .egr-publicar {
    display: flex; align-items: flex-start; gap: .6rem; cursor: pointer; font-size: .88rem; color: #445;
    background: var(--gray-100); border-radius: var(--radius); padding: .8rem 1rem; margin-top: .4rem;
  }
  .egr-publicar input { margin-top: .2rem; width: 16px; height: 16px; accent-color: var(--blue-dark); }
  .egr-publicar b { color: var(--blue-dark); }
  .egr-quien { display: flex; align-items: center; gap: .7rem; min-width: 220px; }
  .egr-quien b { display: block; color: var(--blue-dark); }
  .egr-quien small, .egr-sub { display: block; color: #8a9099; font-size: .76rem; }
  .egr-avatar {
    position: relative; flex-shrink: 0; width: 40px; height: 40px; border-radius: 50%; overflow: hidden;
    background: var(--blue-dark); color: #fff; font-size: .78rem; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
  }
  .egr-avatar img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
</style>
<script>
  document.querySelectorAll('.egr-contador').forEach(function (c) {
    var ta = document.getElementById(c.dataset.para), ideal = +c.dataset.ideal;
    function act() {
      var n = ta.value.length;
      c.textContent = n + ' caracteres' + (n > ideal ? ' · más largo que lo ideal para la grilla' : '');
      c.classList.toggle('pasado', n > ideal);
    }
    ta.addEventListener('input', act); act();
  });
</script>

  </div>
</body>
</html>
