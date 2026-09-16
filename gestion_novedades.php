<?php
require_once __DIR__ . '/panel_config.php';
exigir_login();

$u   = usuario_actual();
$pdo = db();

// ── Configuración de subida de imágenes ──
const IMG_CARPETA   = 'img/novedades/subidas';          // relativa a la raíz del sitio
const IMG_MAX_BYTES = 5 * 1024 * 1024;                   // 5 MB por imagen
const IMG_TIPOS     = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];

/**
 * Procesa las imágenes subidas ($_FILES['imagenes']) y las asocia a una novedad.
 * Devuelve un array de mensajes de error (vacío si todo salió bien).
 */
function procesar_imagenes_subidas(PDO $pdo, int $id_novedad): array {
    $errs = [];
    if (empty($_FILES['imagenes']) || !is_array($_FILES['imagenes']['name'])) {
        return $errs;
    }

    // Carpeta física de destino (junto a este script)
    $dir_fisico = __DIR__ . '/' . IMG_CARPETA;
    if (!is_dir($dir_fisico)) {
        @mkdir($dir_fisico, 0775, true);
    }
    if (!is_dir($dir_fisico) || !is_writable($dir_fisico)) {
        return ['No se pudo escribir en la carpeta de imágenes (' . IMG_CARPETA . '). Verificá los permisos.'];
    }

    // Orden actual máximo para esta novedad
    $ord = $pdo->prepare('SELECT COALESCE(MAX(orden), 0) FROM imagenes_novedades WHERE id_novedad = ?');
    $ord->execute([$id_novedad]);
    $orden = (int)$ord->fetchColumn();

    $ins = $pdo->prepare(
        'INSERT INTO imagenes_novedades (id_novedad, url_imagen, orden) VALUES (:id, :url, :ord)'
    );

    $cantidad = count($_FILES['imagenes']['name']);
    for ($i = 0; $i < $cantidad; $i++) {
        $err_code = $_FILES['imagenes']['error'][$i];
        if ($err_code === UPLOAD_ERR_NO_FILE) continue;               // campo vacío, se ignora
        $nombre_orig = $_FILES['imagenes']['name'][$i];

        if ($err_code !== UPLOAD_ERR_OK) {
            $errs[] = "No se pudo subir «$nombre_orig» (código $err_code).";
            continue;
        }
        $tmp  = $_FILES['imagenes']['tmp_name'][$i];
        $size = (int)$_FILES['imagenes']['size'][$i];

        if ($size > IMG_MAX_BYTES) {
            $errs[] = "«$nombre_orig» supera el tamaño máximo de 5 MB.";
            continue;
        }

        // Validar el tipo real por contenido (no confiar en la extensión)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($tmp);
        if (!isset(IMG_TIPOS[$mime])) {
            $errs[] = "«$nombre_orig» no es una imagen válida (solo JPG, PNG, WEBP o GIF).";
            continue;
        }
        $ext = IMG_TIPOS[$mime];

        // Nombre único
        $nombre_final = 'nov' . $id_novedad . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destino_fis  = $dir_fisico . '/' . $nombre_final;
        $url_rel      = IMG_CARPETA . '/' . $nombre_final;   // lo que se guarda en la base

        if (!move_uploaded_file($tmp, $destino_fis)) {
            $errs[] = "No se pudo guardar «$nombre_orig».";
            continue;
        }

        $orden++;
        $ins->execute([':id' => $id_novedad, ':url' => $url_rel, ':ord' => $orden]);
    }
    return $errs;
}

/** Elimina una imagen (registro + archivo físico) si pertenece a la novedad indicada. */
function eliminar_imagen(PDO $pdo, int $id_imagen, int $id_novedad): void {
    $q = $pdo->prepare('SELECT url_imagen FROM imagenes_novedades WHERE id_imagen = ? AND id_novedad = ?');
    $q->execute([$id_imagen, $id_novedad]);
    $url = $q->fetchColumn();
    if ($url === false) return;
    $pdo->prepare('DELETE FROM imagenes_novedades WHERE id_imagen = ?')->execute([$id_imagen]);
    // Borrar archivo físico solo si está dentro de nuestra carpeta de subidas
    if (str_starts_with($url, IMG_CARPETA . '/')) {
        $ruta = __DIR__ . '/' . $url;
        if (is_file($ruta)) @unlink($ruta);
    }
}

// ── Borrar una imagen individual (desde edición) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'borrar_imagen') {
    csrf_check();
    $id_img = (int)($_POST['id_imagen'] ?? 0);
    $id_nov = (int)($_POST['id'] ?? 0);
    $row = $pdo->prepare('SELECT id_usuario FROM novedades WHERE id_novedad = ?');
    $row->execute([$id_nov]);
    $autor = $row->fetchColumn();
    if ($autor !== false && puede_gestionar((int)$autor)) {
        eliminar_imagen($pdo, $id_img, $id_nov);
        flash('ok', 'Imagen eliminada.');
    } else {
        flash('error', 'No tenés permiso para modificar esa novedad.');
    }
    header('Location: gestion_novedades.php?editar=' . $id_nov);
    exit;
}

// ── Acción de borrado ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'borrar') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $row = $pdo->prepare('SELECT id_usuario FROM novedades WHERE id_novedad = ?');
    $row->execute([$id]);
    $autor = $row->fetchColumn();
    if ($autor === false) {
        flash('error', 'La novedad no existe.');
    } elseif (!puede_gestionar((int)$autor)) {
        flash('error', 'No tenés permiso para borrar esa novedad.');
    } else {
        // Borrar archivos físicos de las imágenes subidas antes de eliminar la novedad
        $imgs = $pdo->prepare('SELECT url_imagen FROM imagenes_novedades WHERE id_novedad = ?');
        $imgs->execute([$id]);
        foreach ($imgs->fetchAll(PDO::FETCH_COLUMN) as $url) {
            if (str_starts_with((string)$url, IMG_CARPETA . '/')) {
                $ruta = __DIR__ . '/' . $url;
                if (is_file($ruta)) @unlink($ruta);
            }
        }
        $pdo->prepare('DELETE FROM novedades WHERE id_novedad = ?')->execute([$id]);
        flash('ok', 'Novedad eliminada correctamente.');
    }
    header('Location: gestion_novedades.php');
    exit;
}

// ── Alta / edición ──
$errores = [];
$modo    = 'crear';
$edit    = ['id_novedad' => 0, 'titulo' => '', 'descripcion' => '', 'etiqueta' => 'Global'];
$imagenes_edit = [];   // imágenes ya cargadas de la novedad que se edita

// ¿Estamos editando? (?editar=ID)
if (isset($_GET['editar'])) {
    $id  = (int)$_GET['editar'];
    $stmt = $pdo->prepare('SELECT id_novedad, titulo, descripcion, etiqueta, id_usuario FROM novedades WHERE id_novedad = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row && puede_gestionar((int)$row['id_usuario'])) {
        $modo = 'editar';
        $edit = $row;
        $qimg = $pdo->prepare('SELECT id_imagen, url_imagen FROM imagenes_novedades WHERE id_novedad = ? ORDER BY orden ASC, id_imagen ASC');
        $qimg->execute([$id]);
        $imagenes_edit = $qimg->fetchAll();
    } else {
        flash('error', 'No podés editar esa novedad.');
        header('Location: gestion_novedades.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array(($_POST['accion'] ?? ''), ['crear', 'editar'], true)) {
    csrf_check();
    $accion   = $_POST['accion'];
    $id       = (int)($_POST['id'] ?? 0);
    $titulo   = trim((string)($_POST['titulo'] ?? ''));
    $desc     = trim((string)($_POST['descripcion'] ?? ''));
    $etiqueta = (string)($_POST['etiqueta'] ?? 'Global');

    if ($titulo === '')                              $errores[] = 'El título es obligatorio.';
    if (mb_strlen($titulo) > 255)                    $errores[] = 'El título es demasiado largo (máx. 255).';
    if ($desc === '')                                $errores[] = 'La descripción es obligatoria.';
    if (!in_array($etiqueta, ETIQUETAS_VALIDAS, true)) $errores[] = 'Etiqueta inválida.';

    if (!$errores) {
        if ($accion === 'crear') {
            $ins = $pdo->prepare(
                'INSERT INTO novedades (titulo, descripcion, etiqueta, origen, id_usuario)
                 VALUES (:t, :d, :e, "manual", :uid)'
            );
            $ins->execute([':t' => $titulo, ':d' => $desc, ':e' => $etiqueta, ':uid' => $u['id_usuario']]);
            $nuevo_id = (int)$pdo->lastInsertId();
            $errs_img = procesar_imagenes_subidas($pdo, $nuevo_id);
            if ($errs_img) {
                flash('error', 'Novedad creada, pero con avisos en las imágenes: ' . implode(' ', $errs_img));
            } else {
                flash('ok', 'Novedad creada correctamente.');
            }
            header('Location: gestion_novedades.php');
            exit;
        } else {
            // Verificar permiso sobre el registro real
            $chk = $pdo->prepare('SELECT id_usuario FROM novedades WHERE id_novedad = ?');
            $chk->execute([$id]);
            $autor = $chk->fetchColumn();
            if ($autor === false || !puede_gestionar((int)$autor)) {
                flash('error', 'No tenés permiso para editar esa novedad.');
                header('Location: gestion_novedades.php');
                exit;
            }
            $upd = $pdo->prepare(
                'UPDATE novedades SET titulo = :t, descripcion = :d, etiqueta = :e WHERE id_novedad = :id'
            );
            $upd->execute([':t' => $titulo, ':d' => $desc, ':e' => $etiqueta, ':id' => $id]);
            $errs_img = procesar_imagenes_subidas($pdo, $id);
            if ($errs_img) {
                flash('error', 'Cambios guardados, pero con avisos en las imágenes: ' . implode(' ', $errs_img));
            } else {
                flash('ok', 'Novedad actualizada correctamente.');
            }
            header('Location: gestion_novedades.php');
            exit;
        }
    } else {
        // Reponer valores para remostrar el formulario
        $modo = $accion;
        $edit = ['id_novedad' => $id, 'titulo' => $titulo, 'descripcion' => $desc, 'etiqueta' => $etiqueta];
    }
}

// ── Listado ──
$sql = 'SELECT n.id_novedad, n.titulo, n.etiqueta, n.origen, n.created_at, n.id_usuario,
               CONCAT(us.nombre, " ", us.apellido) AS autor,
               (SELECT COUNT(*) FROM imagenes_novedades im WHERE im.id_novedad = n.id_novedad) AS n_imagenes
        FROM novedades n JOIN usuarios us ON us.id_usuario = n.id_usuario
        ORDER BY n.created_at DESC';
$lista = $pdo->query($sql)->fetchAll();

function color_etiqueta(string $e): string {
    return match ($e) {
        'Inicial' => '#E63946', 'Primario' => '#1D3557', 'Secundario' => '#2a9d8f',
        'Técnica' => '#0d1b2a', 'Orientada' => '#457B9D',
        default => '#6d6875',
    };
}

$panel_page_title = 'Gestión de Novedades';
$panel_heading    = 'Gestión de <span>Novedades</span>';
$panel_sub        = 'Creá, editá o eliminá las novedades del sitio.';
require __DIR__ . '/panel_header.php';
?>

    <div class="panel-toolbar">
      <a href="panel.php" class="back-link">
        <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg> Volver al panel
      </a>
    </div>

    <!-- Formulario -->
    <div class="form-card" style="margin-bottom:2.5rem;">
      <h2 style="font-family:var(--font-display);color:var(--blue-dark);font-size:1.35rem;margin-bottom:1.3rem;">
        <?= $modo === 'editar' ? 'Editar novedad' : 'Nueva novedad' ?>
      </h2>

      <?php if ($errores): ?>
        <div class="alert alert-error">
          <?= implode('<br>', array_map('e', $errores)) ?>
        </div>
      <?php endif; ?>

      <form method="post" action="gestion_novedades.php" enctype="multipart/form-data">
        <?= csrf_input() ?>
        <input type="hidden" name="accion" value="<?= $modo === 'editar' ? 'editar' : 'crear' ?>">
        <input type="hidden" name="id" value="<?= (int)$edit['id_novedad'] ?>">

        <div class="field">
          <label for="titulo">Título</label>
          <input type="text" id="titulo" name="titulo" maxlength="255" required
                 value="<?= e($edit['titulo']) ?>" placeholder="Ej.: Acto del Día de la Bandera">
        </div>

        <div class="field">
          <label for="etiqueta">Nivel / Etiqueta</label>
          <select id="etiqueta" name="etiqueta">
            <?php foreach (ETIQUETAS_VALIDAS as $et): ?>
              <option value="<?= e($et) ?>" <?= $edit['etiqueta'] === $et ? 'selected' : '' ?>><?= e($et) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <label for="descripcion">Descripción</label>
          <textarea id="descripcion" name="descripcion" required
                    placeholder="Escribí el contenido de la novedad..."><?= e($edit['descripcion']) ?></textarea>
        </div>

        <?php if ($modo === 'editar' && $imagenes_edit): ?>
          <div class="field">
            <label>Imágenes actuales</label>
            <div class="img-gallery">
              <?php foreach ($imagenes_edit as $img): ?>
                <div class="img-thumb">
                  <img src="<?= e($img['url_imagen']) ?>" alt="Imagen de la novedad">
                  <button type="submit" class="img-del" name="_borrar_img_click"
                          form="form-borrar-img-<?= (int)$img['id_imagen'] ?>"
                          title="Quitar esta imagen"
                          onclick="return confirm('¿Quitar esta imagen?');">×</button>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <div class="field">
          <label for="imagenes">
            <?= $modo === 'editar' ? 'Agregar más imágenes' : 'Imágenes' ?>
            <span class="hint">(opcional · JPG, PNG, WEBP o GIF · hasta 5 MB c/u · podés elegir varias)</span>
          </label>
          <input type="file" id="imagenes" name="imagenes[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
          <?php if ($errores): ?>
            <p class="hint" style="color:var(--red);margin-top:.4rem;">Por seguridad del navegador, si hubo un error tenés que volver a seleccionar las imágenes.</p>
          <?php endif; ?>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">
            <?= $modo === 'editar' ? 'Guardar cambios' : 'Crear novedad' ?>
          </button>
          <?php if ($modo === 'editar'): ?>
            <a href="gestion_novedades.php" class="btn btn-outline">Cancelar</a>
          <?php endif; ?>
        </div>
      </form>

      <?php if ($modo === 'editar' && $imagenes_edit): ?>
        <?php foreach ($imagenes_edit as $img): ?>
          <form method="post" action="gestion_novedades.php" id="form-borrar-img-<?= (int)$img['id_imagen'] ?>" style="display:none;">
            <?= csrf_input() ?>
            <input type="hidden" name="accion" value="borrar_imagen">
            <input type="hidden" name="id" value="<?= (int)$edit['id_novedad'] ?>">
            <input type="hidden" name="id_imagen" value="<?= (int)$img['id_imagen'] ?>">
          </form>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Listado -->
    <h2 style="font-family:var(--font-display);color:var(--blue-dark);font-size:1.25rem;margin-bottom:1rem;">
      Novedades publicadas
    </h2>

    <?php if (!$lista): ?>
      <div class="empty-state">
        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><polyline points="14 2 14 8 20 8"/></svg>
        <p>Todavía no hay novedades cargadas.</p>
      </div>
    <?php else: ?>
      <div style="overflow-x:auto;">
      <table class="mng-table">
        <thead>
          <tr>
            <th>Título</th><th>Etiqueta</th><th>Origen</th><th>Imágenes</th><th>Autor</th><th>Fecha</th><th style="text-align:right;">Acciones</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($lista as $row): ?>
          <?php $puede = puede_gestionar((int)$row['id_usuario']); ?>
          <tr>
            <td><?= e($row['titulo']) ?></td>
            <td><span class="mng-badge" style="background:<?= color_etiqueta($row['etiqueta']) ?>"><?= e($row['etiqueta']) ?></span></td>
            <td style="text-transform:capitalize;color:#667;"><?= e($row['origen']) ?></td>
            <td style="color:#667;">
              <?php if ((int)$row['n_imagenes'] > 0): ?>
                <span style="display:inline-flex;align-items:center;gap:.3rem;">
                  <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                  <?= (int)$row['n_imagenes'] ?>
                </span>
              <?php else: ?>
                <span style="color:#bbb;">—</span>
              <?php endif; ?>
            </td>
            <td><?= e($row['autor']) ?></td>
            <td style="color:#667;white-space:nowrap;"><?= e(date('d/m/Y', strtotime($row['created_at']))) ?></td>
            <td>
              <div class="mng-actions" style="justify-content:flex-end;">
                <?php if ($puede): ?>
                  <a href="gestion_novedades.php?editar=<?= (int)$row['id_novedad'] ?>" class="icon-btn" title="Editar">
                    <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                  </a>
                  <form method="post" action="gestion_novedades.php" style="display:inline;"
                        onsubmit="return confirm('¿Eliminar esta novedad? Esta acción no se puede deshacer.');">
                    <?= csrf_input() ?>
                    <input type="hidden" name="accion" value="borrar">
                    <input type="hidden" name="id" value="<?= (int)$row['id_novedad'] ?>">
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

  </div>
</body>
</html>
