<?php
// ============================================================
//  gestion_tour.php
//  Panel de Control — Recorrido Virtual 360°
//  Subida de fotos 360 (escenas) y editor visual de puntos de
//  transición: doble click sobre el panorama para ubicarlos.
// ============================================================
require_once __DIR__ . '/panel_config.php';
exigir_login();

$u   = usuario_actual();
$pdo = db();

// ── Configuración de subida ──
const TOUR_CARPETA   = 'img/tour';                 // relativa a la raíz del sitio
const TOUR_MAX_BYTES = 20 * 1024 * 1024;           // 20 MB (las fotos 360 son grandes)
const TOUR_TIPOS     = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

// ============================================================
//  ACCIONES (POST)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $accion = $_POST['accion'] ?? '';
    $volver_a = null; // id de escena a la que volver tras la acción

    try {
        // ── Crear escena (subir foto 360) ──────────────────
        if ($accion === 'crear_escena') {
            $nombre = trim($_POST['nombre'] ?? '');
            $zona   = trim($_POST['zona'] ?? 'General');
            if ($nombre === '') {
                flash('error', 'La escena necesita un nombre.');
            } elseif (empty($_FILES['imagen']['tmp_name']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
                flash('error', 'Tenés que subir la foto 360 (JPG, PNG o WEBP).');
            } elseif ((int)$_FILES['imagen']['size'] > TOUR_MAX_BYTES) {
                flash('error', 'La imagen supera el máximo de 20 MB.');
            } else {
                $tmp   = $_FILES['imagen']['tmp_name'];
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime  = $finfo->file($tmp);
                if (!isset(TOUR_TIPOS[$mime])) {
                    flash('error', 'El archivo no es una imagen válida (JPG, PNG o WEBP).');
                } else {
                    // Aviso (no bloqueante) si no parece equirectangular 2:1
                    [$w, $h] = getimagesize($tmp) ?: [0, 0];
                    $aviso = ($h > 0 && abs($w / $h - 2) > 0.15)
                        ? ' Ojo: la imagen no tiene proporción 2:1, puede no ser una foto 360 completa.' : '';

                    $dir = __DIR__ . '/' . TOUR_CARPETA;
                    if (!is_dir($dir)) @mkdir($dir, 0775, true);

                    $archivo = 'tour_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . TOUR_TIPOS[$mime];
                    if (!move_uploaded_file($tmp, $dir . '/' . $archivo)) {
                        flash('error', 'No se pudo guardar la imagen. Verificá permisos de ' . TOUR_CARPETA . '.');
                    } else {
                        $st = $pdo->prepare(
                            'INSERT INTO tour_escenas (nombre, zona, url_imagen, orden)
                             VALUES (:n, :z, :u, (SELECT COALESCE(MAX(t.orden),0)+1 FROM tour_escenas t WHERE t.zona = :z2))'
                        );
                        $st->execute([':n'=>$nombre, ':z'=>$zona, ':u'=>TOUR_CARPETA.'/'.$archivo, ':z2'=>$zona]);
                        $volver_a = (int)$pdo->lastInsertId();
                        flash('ok', "Escena «{$nombre}» creada. Hacé doble click sobre la foto para agregar los puntos de transición.$aviso");
                    }
                }
            }
        }

        // ── Editar datos de una escena ──────────────────────
        elseif ($accion === 'editar_escena') {
            $id = (int)($_POST['id_escena'] ?? 0);
            $st = $pdo->prepare('UPDATE tour_escenas SET nombre=:n, zona=:z, orden=:o, activa=:a WHERE id_escena=:id');
            $st->execute([
                ':n'  => trim($_POST['nombre'] ?? ''),
                ':z'  => trim($_POST['zona'] ?? 'General'),
                ':o'  => (int)($_POST['orden'] ?? 0),
                ':a'  => isset($_POST['activa']) ? 1 : 0,
                ':id' => $id,
            ]);
            $volver_a = $id;
            flash('ok', 'Escena actualizada.');
        }

        // ── Eliminar escena (borra foto + hotspots en cascada) ──
        elseif ($accion === 'eliminar_escena') {
            $id = (int)($_POST['id_escena'] ?? 0);
            $q  = $pdo->prepare('SELECT url_imagen FROM tour_escenas WHERE id_escena=?');
            $q->execute([$id]);
            if ($url = $q->fetchColumn()) {
                $pdo->prepare('DELETE FROM tour_escenas WHERE id_escena=?')->execute([$id]);
                @unlink(__DIR__ . '/' . $url);
                flash('ok', 'Escena eliminada (junto con sus puntos y los que apuntaban a ella).');
            }
        }

        // ── Crear hotspot (punto de transición o info) ──────
        elseif ($accion === 'crear_hotspot') {
            $id_escena = (int)($_POST['id_escena'] ?? 0);
            $tipo      = ($_POST['tipo'] ?? 'nav') === 'info' ? 'info' : 'nav';
            $destino   = (int)($_POST['id_destino'] ?? 0);
            $yaw       = (float)($_POST['yaw'] ?? 0);
            $pitch     = (float)($_POST['pitch'] ?? 0);
            $texto     = trim($_POST['texto'] ?? '');
            $volver_a  = $id_escena;

            if ($tipo === 'nav' && $destino <= 0) {
                flash('error', 'Elegí a qué escena lleva el punto de transición.');
            } elseif ($tipo === 'nav' && $destino === $id_escena) {
                flash('error', 'El punto no puede llevar a la misma escena.');
            } elseif ($tipo === 'info' && $texto === '') {
                flash('error', 'Los puntos de información necesitan un texto.');
            } else {
                $st = $pdo->prepare(
                    'INSERT INTO tour_hotspots (id_escena, tipo, id_destino, yaw, pitch, texto)
                     VALUES (:e, :t, :d, :y, :p, :x)'
                );
                $st->execute([
                    ':e'=>$id_escena, ':t'=>$tipo,
                    ':d'=>$tipo === 'nav' ? $destino : null,
                    ':y'=>$yaw, ':p'=>$pitch, ':x'=>$texto,
                ]);
                flash('ok', $tipo === 'nav' ? 'Punto de transición agregado.' : 'Punto de información agregado.');
            }
        }

        // ── Eliminar hotspot ─────────────────────────────────
        elseif ($accion === 'eliminar_hotspot') {
            $id = (int)($_POST['id_hotspot'] ?? 0);
            $volver_a = (int)($_POST['id_escena'] ?? 0);
            $pdo->prepare('DELETE FROM tour_hotspots WHERE id_hotspot=?')->execute([$id]);
            flash('ok', 'Punto eliminado.');
        }

        // ── Reubicar hotspot (arrastrado a nueva posición en el editor) ──
        elseif ($accion === 'mover_hotspot') {
            $id = (int)($_POST['id_hotspot'] ?? 0);
            $volver_a = (int)($_POST['id_escena'] ?? 0);
            $st = $pdo->prepare('UPDATE tour_hotspots SET yaw=:y, pitch=:p WHERE id_hotspot=:id');
            $st->execute([':y'=>(float)($_POST['yaw'] ?? 0), ':p'=>(float)($_POST['pitch'] ?? 0), ':id'=>$id]);
            flash('ok', 'Punto reubicado.');
        }

        // ── Guardar vista inicial (hacia dónde mira al entrar) ──
        elseif ($accion === 'vista_inicial') {
            $id = (int)($_POST['id_escena'] ?? 0);
            $st = $pdo->prepare('UPDATE tour_escenas SET yaw_inicial=:y, pitch_inicial=:p WHERE id_escena=:id');
            $st->execute([':y'=>(float)($_POST['yaw'] ?? 0), ':p'=>(float)($_POST['pitch'] ?? 0), ':id'=>$id]);
            $volver_a = $id;
            flash('ok', 'Vista inicial de la escena guardada.');
        }

    } catch (PDOException $e) {
        flash('error', 'Error de base de datos: ' . $e->getMessage());
    }

    header('Location: gestion_tour.php' . ($volver_a ? '?escena=' . $volver_a : ''));
    exit;
}

// ============================================================
//  DATOS PARA LA VISTA
// ============================================================
$escenas = $pdo->query('SELECT * FROM tour_escenas ORDER BY zona, orden, nombre')->fetchAll();

// Escena seleccionada (GET ?escena=ID o la primera)
$sel = null;
$sel_id = (int)($_GET['escena'] ?? 0);
foreach ($escenas as $e2) { if ((int)$e2['id_escena'] === $sel_id) { $sel = $e2; break; } }
if ($sel === null && $escenas) $sel = $escenas[0];

$hotspots = [];
if ($sel) {
    $st = $pdo->prepare(
        'SELECT h.*, d.nombre AS destino_nombre
           FROM tour_hotspots h
           LEFT JOIN tour_escenas d ON d.id_escena = h.id_destino
          WHERE h.id_escena = ?
          ORDER BY h.id_hotspot'
    );
    $st->execute([(int)$sel['id_escena']]);
    $hotspots = $st->fetchAll();
}

// Zonas existentes (para sugerir en el datalist)
$zonas = array_values(array_unique(array_column($escenas, 'zona')));

$panel_page_title = 'Recorrido Virtual 360°';
$panel_heading    = 'Recorrido <span>Virtual 360°</span>';
$panel_sub        = 'Subí las fotos esféricas y marcá con doble click los puntos para pasar de un espacio a otro.';
require __DIR__ . '/panel_header.php';
?>

<div class="panel-toolbar">
  <a href="panel.php" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg> Volver al panel
  </a>
</div>

<link rel="stylesheet" href="pannellum/pannellum.css"/>
<script src="pannellum/pannellum.js"></script>

<style>
  .tour-grid{display:grid;grid-template-columns:320px 1fr;gap:1.2rem;align-items:start}
  @media (max-width:980px){.tour-grid{grid-template-columns:1fr}}
  .tour-card{background:#fff;border:1px solid #e2e6ee;border-radius:14px;padding:1.1rem 1.2rem;margin-bottom:1.2rem}
  .tour-card h3{font-family:'Playfair Display',serif;color:#1d3557;font-size:1.05rem;margin-bottom:.7rem}
  .tour-card label{display:block;font-weight:700;font-size:.8rem;color:#3a4a63;margin:.55rem 0 .2rem}
  .tour-card input[type=text],.tour-card input[type=number],.tour-card select{
    width:100%;padding:.5rem .6rem;border:1px solid #cdd5e1;border-radius:8px;font-family:'Nunito',sans-serif;font-size:.88rem}
  .tour-card input[type=file]{font-size:.82rem}
  .btn-tour{display:inline-block;background:#1d3557;color:#fff;border:none;border-radius:9px;
    padding:.55rem 1rem;font-family:'Nunito',sans-serif;font-weight:700;font-size:.85rem;cursor:pointer;margin-top:.7rem}
  .btn-tour:hover{background:#274a76}
  .btn-tour.sec{background:#457b9d}
  .btn-tour.danger{background:#c1121f}
  .escena-item{display:flex;align-items:center;gap:.5rem;padding:.45rem .55rem;border-radius:8px;
    color:#2a3a55;font-weight:600;font-size:.86rem;text-decoration:none;transition:background .15s}
  .escena-item:hover{background:#eef2f8}
  .escena-item.sel{background:#1d3557;color:#fff}
  .escena-item .ez{margin-left:auto;font-size:.68rem;font-weight:800;color:#8a97ab;text-transform:uppercase;letter-spacing:.06em}
  .escena-item.sel .ez{color:#e9c46a}
  .escena-item .off{font-size:.66rem;background:#c1121f;color:#fff;border-radius:6px;padding:.05rem .35rem}
  #editorPano{width:100%;aspect-ratio:16/9;min-height:420px;border-radius:12px;overflow:hidden;background:#12233d}
  .editor-hint{font-size:.8rem;color:#5a6b85;margin:.5rem 0 0}
  .hs-tabla{width:100%;border-collapse:collapse;font-size:.84rem;margin-top:.4rem}
  .hs-tabla th{font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:#8a97ab;text-align:left;padding:.35rem .4rem;border-bottom:1px solid #e2e6ee}
  .hs-tabla td{padding:.4rem;border-bottom:1px solid #eef1f6;color:#2a3a55}
  .hs-pill{font-size:.68rem;font-weight:800;border-radius:6px;padding:.12rem .45rem}
  .hs-pill.nav{background:#dcebf7;color:#1d3557}
  .hs-pill.info{background:#fdf1d7;color:#8a6d1f}
  .coords-box{display:flex;gap:.6rem}
  .coords-box>div{flex:1}
  /* Estilos de los hotspots dentro del editor */
  .ed-nav{width:44px;height:44px;border-radius:50%;background:rgba(29,53,87,.85);border:2px solid #fff;
    display:grid;place-items:center;cursor:pointer;box-shadow:0 4px 14px rgba(0,0,0,.4)}
  .ed-nav::after{content:"";width:13px;height:13px;border-top:3px solid #e9c46a;border-right:3px solid #e9c46a;
    transform:rotate(-45deg) translate(-2px,2px)}
  .ed-info{width:30px;height:30px;border-radius:50%;background:#e9c46a;border:2px solid #fff;color:#1d3557;
    display:grid;place-items:center;font-weight:800;font-size:.9rem}
  .ed-nuevo{width:26px;height:26px;border-radius:50%;background:#c1121f;border:3px solid #fff;
    box-shadow:0 0 0 6px rgba(193,18,31,.25);animation:edp 1.4s infinite}
  @keyframes edp{50%{box-shadow:0 0 0 12px rgba(193,18,31,0)}}
</style>

<div class="tour-grid">

  <!-- ══════════ COLUMNA IZQUIERDA: escenas ══════════ -->
  <div>
    <div class="tour-card">
      <h3>Escenas del recorrido</h3>
      <?php if (!$escenas): ?>
        <p style="font-size:.85rem;color:#5a6b85">Todavía no hay escenas. Subí la primera foto 360 con el formulario de abajo.</p>
      <?php else: ?>
        <?php foreach ($escenas as $e3): ?>
          <a class="escena-item <?= $sel && $e3['id_escena'] === $sel['id_escena'] ? 'sel' : '' ?>"
             href="gestion_tour.php?escena=<?= (int)$e3['id_escena'] ?>">
            <?= e($e3['nombre']) ?>
            <?php if (!$e3['activa']): ?><span class="off">oculta</span><?php endif; ?>
            <span class="ez"><?= e($e3['zona']) ?></span>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="tour-card">
      <h3>Agregar escena (foto 360)</h3>
      <form method="post" enctype="multipart/form-data">
        <?= csrf_input() ?>
        <input type="hidden" name="accion" value="crear_escena">
        <label>Nombre del espacio</label>
        <input type="text" name="nombre" required placeholder="Ej: Patio central">
        <label>Zona (agrupa el menú del tour)</label>
        <input type="text" name="zona" list="zonasList" value="General" required>
        <datalist id="zonasList">
          <?php foreach ($zonas as $z): ?><option value="<?= e($z) ?>"><?php endforeach; ?>
          <option value="Acceso"><option value="Espacios comunes">
          <option value="Nivel Inicial"><option value="Nivel Primario"><option value="Nivel Secundario">
        </datalist>
        <label>Foto esférica (equirectangular, sacada con app 360)</label>
        <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp" required>
        <button class="btn-tour" type="submit">Subir escena</button>
      </form>
    </div>

    <?php if ($sel): ?>
    <div class="tour-card">
      <h3>Datos de «<?= e($sel['nombre']) ?>»</h3>
      <form method="post">
        <?= csrf_input() ?>
        <input type="hidden" name="accion" value="editar_escena">
        <input type="hidden" name="id_escena" value="<?= (int)$sel['id_escena'] ?>">
        <label>Nombre</label>
        <input type="text" name="nombre" value="<?= e($sel['nombre']) ?>" required>
        <label>Zona</label>
        <input type="text" name="zona" list="zonasList" value="<?= e($sel['zona']) ?>" required>
        <label>Orden dentro de la zona</label>
        <input type="number" name="orden" value="<?= (int)$sel['orden'] ?>">
        <label style="display:flex;align-items:center;gap:.4rem;margin-top:.6rem">
          <input type="checkbox" name="activa" <?= $sel['activa'] ? 'checked' : '' ?> style="width:auto">
          Visible en la web pública
        </label>
        <button class="btn-tour" type="submit">Guardar cambios</button>
      </form>
      <form method="post" onsubmit="return confirm('¿Eliminar la escena, su foto y todos sus puntos?');" style="margin-top:.4rem">
        <?= csrf_input() ?>
        <input type="hidden" name="accion" value="eliminar_escena">
        <input type="hidden" name="id_escena" value="<?= (int)$sel['id_escena'] ?>">
        <button class="btn-tour danger" type="submit">Eliminar escena</button>
      </form>
    </div>
    <?php endif; ?>
  </div>

  <!-- ══════════ COLUMNA DERECHA: editor visual ══════════ -->
  <div>
    <?php if (!$sel): ?>
      <div class="tour-card"><p style="font-size:.9rem;color:#5a6b85">Subí una escena para empezar a editar el recorrido.</p></div>
    <?php else: ?>

    <div class="tour-card">
      <h3>Editor — <?= e($sel['nombre']) ?></h3>
      <div id="editorPano"></div>
      <p class="editor-hint">
        <strong>Doble click</strong> sobre la foto en el lugar exacto de la puerta o pasillo: ahí queda el punto (marcador rojo)
        y se cargan las coordenadas en el formulario. Después elegís a qué escena lleva y guardás.
      </p>
      <form method="post" style="margin-top:.3rem" id="vistaForm">
        <?= csrf_input() ?>
        <input type="hidden" name="accion" value="vista_inicial">
        <input type="hidden" name="id_escena" value="<?= (int)$sel['id_escena'] ?>">
        <input type="hidden" name="yaw" id="viYaw"><input type="hidden" name="pitch" id="viPitch">
        <button class="btn-tour sec" type="submit"
                onclick="document.getElementById('viYaw').value=viewer.getYaw().toFixed(1);document.getElementById('viPitch').value=viewer.getPitch().toFixed(1);">
          Fijar la vista actual como inicial
        </button>
      </form>
    </div>

    <div class="tour-card">
      <h3>Nuevo punto en esta escena</h3>
      <form method="post">
        <?= csrf_input() ?>
        <input type="hidden" name="accion" value="crear_hotspot">
        <input type="hidden" name="id_escena" value="<?= (int)$sel['id_escena'] ?>">
        <div class="coords-box">
          <div><label>Yaw (horizontal)</label><input type="text" name="yaw" id="hsYaw" readonly placeholder="Doble click en la foto" required></div>
          <div><label>Pitch (vertical)</label><input type="text" name="pitch" id="hsPitch" readonly placeholder="—" required></div>
        </div>
        <label>Tipo de punto</label>
        <select name="tipo" id="hsTipo">
          <option value="nav">Transición → lleva a otra escena</option>
          <option value="info">Información → muestra un texto</option>
        </select>
        <div id="destinoWrap">
          <label>Escena destino</label>
          <select name="id_destino">
            <option value="">— Elegir —</option>
            <?php foreach ($escenas as $e4): if ($e4['id_escena'] === $sel['id_escena']) continue; ?>
              <option value="<?= (int)$e4['id_escena'] ?>"><?= e($e4['zona'] . ' — ' . $e4['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <label>Texto del punto (tooltip)</label>
        <input type="text" name="texto" maxlength="180" placeholder="Ej: Ir al patio central">
        <button class="btn-tour" type="submit">Guardar punto</button>
      </form>
    </div>

    <div class="tour-card">
      <h3>Puntos de esta escena (<?= count($hotspots) ?>)</h3>
      <?php if (!$hotspots): ?>
        <p style="font-size:.85rem;color:#5a6b85">Sin puntos todavía. Una escena sin transiciones queda "aislada" del recorrido.</p>
      <?php else: ?>
      <table class="hs-tabla">
        <thead><tr><th>Tipo</th><th>Destino / Texto</th><th>Yaw</th><th>Pitch</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($hotspots as $h): ?>
          <tr>
            <td><span class="hs-pill <?= e($h['tipo']) ?>"><?= $h['tipo'] === 'nav' ? 'Transición' : 'Info' ?></span></td>
            <td><?= $h['tipo'] === 'nav'
                  ? '→ ' . e($h['destino_nombre'] ?? '(escena eliminada)') . ($h['texto'] !== '' ? ' · ' . e($h['texto']) : '')
                  : e($h['texto']) ?></td>
            <td><?= e((string)$h['yaw']) ?></td>
            <td><?= e((string)$h['pitch']) ?></td>
            <td>
              <button type="button" class="btn-tour sec" style="padding:.3rem .6rem;margin:0 .3rem 0 0;font-size:.72rem"
                      onclick="activarReubicar(<?= (int)$h['id_hotspot'] ?>)">Reubicar</button>
              <form method="post" onsubmit="return confirm('¿Eliminar este punto?');" style="display:inline">
                <?= csrf_input() ?>
                <input type="hidden" name="accion" value="eliminar_hotspot">
                <input type="hidden" name="id_hotspot" value="<?= (int)$h['id_hotspot'] ?>">
                <input type="hidden" name="id_escena" value="<?= (int)$sel['id_escena'] ?>">
                <button class="btn-tour danger" style="padding:.3rem .6rem;margin:0;font-size:.72rem" type="submit">Borrar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>

    <?php endif; ?>
  </div>
</div>

<?php if ($sel): ?>
<script>
// ── Hotspots existentes para previsualizar en el editor ──
const HS = <?= json_encode(array_map(fn($h) => [
    'id'    => (int)$h['id_hotspot'],
    'tipo'  => $h['tipo'],
    'yaw'   => (float)$h['yaw'],
    'pitch' => (float)$h['pitch'],
    'texto' => $h['texto'] !== '' ? $h['texto']
               : ($h['tipo'] === 'nav' ? 'Ir a ' . ($h['destino_nombre'] ?? '?') : ''),
], $hotspots), JSON_UNESCAPED_UNICODE) ?>;
const ID_ESCENA = <?= (int)$sel['id_escena'] ?>;
const CSRF = <?= json_encode(csrf_token()) ?>;

const viewer = pannellum.viewer('editorPano', {
  type: 'equirectangular',
  panorama: <?= json_encode($sel['url_imagen']) ?>,
  autoLoad: true,
  yaw:   <?= (float)$sel['yaw_inicial'] ?>,
  pitch: <?= (float)$sel['pitch_inicial'] ?>,
  hfov: 100, minHfov: 50, maxHfov: 120,
  showControls: true,
  hotSpots: HS.map(h => ({
    pitch: h.pitch, yaw: h.yaw, type: 'info',
    createTooltipFunc: el => {
      el.innerHTML = '<div class="' + (h.tipo === 'nav' ? 'ed-nav' : 'ed-info') + '" title="' + h.texto.replace(/"/g,'') + '">'
                   + (h.tipo === 'info' ? 'i' : '') + '</div>';
    }
  }))
});

// ── Modo reubicar: al activarlo, el próximo doble click mueve esa flecha ──
let reubicarId = null;
function activarReubicar(id){
  reubicarId = id;
  const hint = document.querySelector('.editor-hint');
  if (hint) hint.innerHTML = '<strong>Modo reubicar activo:</strong> hacé doble click en la foto sobre el nuevo lugar de este punto. Se guarda solo.';
}

function enviarMover(id, yaw, pitch){
  const f = document.createElement('form');
  f.method = 'post'; f.style.display = 'none';
  f.innerHTML = `<input name="csrf" value="${CSRF}">
                 <input name="accion" value="mover_hotspot">
                 <input name="id_hotspot" value="${id}">
                 <input name="id_escena" value="${ID_ESCENA}">
                 <input name="yaw" value="${yaw}">
                 <input name="pitch" value="${pitch}">`;
  document.body.appendChild(f);
  f.submit();
}

// ── Doble click: reubica la flecha en modo reubicar, o coloca marcador nuevo ──
let marcadorTmp = null;
document.getElementById('editorPano').addEventListener('dblclick', e => {
  const c = viewer.mouseEventToCoords(e);   // [pitch, yaw]
  const yaw = c[1].toFixed(1), pitch = c[0].toFixed(1);

  if (reubicarId !== null) {                // mover una flecha existente
    enviarMover(reubicarId, yaw, pitch);
    return;
  }

  document.getElementById('hsPitch').value = pitch;
  document.getElementById('hsYaw').value   = yaw;
  if (marcadorTmp) viewer.removeHotSpot('nuevo');
  viewer.addHotSpot({
    id: 'nuevo', pitch: c[0], yaw: c[1], type: 'info',
    createTooltipFunc: el => { el.innerHTML = '<div class="ed-nuevo" title="Punto nuevo (sin guardar)"></div>'; }
  });
  marcadorTmp = true;
});

// ── Mostrar/ocultar el selector de destino según el tipo ──
const tipoSel = document.getElementById('hsTipo');
tipoSel.addEventListener('change', () => {
  document.getElementById('destinoWrap').style.display = tipoSel.value === 'nav' ? '' : 'none';
});
</script>
<?php endif; ?>

  </div><!-- /.panel-wrap -->
</body>
</html>
