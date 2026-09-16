<?php
require_once __DIR__ . '/panel_config.php';
exigir_login();

$u   = usuario_actual();
$pdo = db();

// ── Borrado ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'borrar') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $row = $pdo->prepare('SELECT id_usuario FROM agenda WHERE id_evento = ?');
    $row->execute([$id]);
    $autor = $row->fetchColumn();
    if ($autor === false) {
        flash('error', 'El evento no existe.');
    } elseif (!puede_gestionar((int)$autor)) {
        flash('error', 'No tenés permiso para borrar ese evento.');
    } else {
        $pdo->prepare('DELETE FROM agenda WHERE id_evento = ?')->execute([$id]);
        flash('ok', 'Evento eliminado correctamente.');
    }
    header('Location: gestion_agenda.php');
    exit;
}

// ── Alta / edición ──
$errores = [];
$modo    = 'crear';
$edit    = [
    'id_evento' => 0, 'titulo' => '', 'descripcion' => '', 'etiqueta' => 'Global',
    'tipo' => 'Otro', 'fecha_evento' => '', 'hora_inicio' => '', 'hora_fin' => '',
    'lugar' => '', 'enlace' => '',
];

if (isset($_GET['editar'])) {
    $id  = (int)$_GET['editar'];
    $stmt = $pdo->prepare('SELECT * FROM agenda WHERE id_evento = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row && puede_gestionar((int)$row['id_usuario'])) {
        $modo = 'editar';
        $edit = $row;
    } else {
        flash('error', 'No podés editar ese evento.');
        header('Location: gestion_agenda.php');
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
    $tipo     = (string)($_POST['tipo'] ?? 'Otro');
    $fecha    = (string)($_POST['fecha_evento'] ?? '');
    $h_ini    = trim((string)($_POST['hora_inicio'] ?? ''));
    $h_fin    = trim((string)($_POST['hora_fin'] ?? ''));
    $lugar    = trim((string)($_POST['lugar'] ?? ''));
    $enlace   = trim((string)($_POST['enlace'] ?? ''));

    if ($titulo === '')                                 $errores[] = 'El título es obligatorio.';
    if (mb_strlen($titulo) > 180)                       $errores[] = 'El título es demasiado largo (máx. 180).';
    if (!in_array($etiqueta, ETIQUETAS_VALIDAS, true))  $errores[] = 'Etiqueta inválida.';
    if (!in_array($tipo, TIPOS_EVENTO, true))           $errores[] = 'Tipo de evento inválido.';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha))    $errores[] = 'La fecha del evento es obligatoria.';
    if ($h_ini !== '' && !preg_match('/^\d{2}:\d{2}$/', $h_ini)) $errores[] = 'Hora de inicio inválida.';
    if ($h_fin !== '' && !preg_match('/^\d{2}:\d{2}$/', $h_fin)) $errores[] = 'Hora de fin inválida.';
    if ($enlace !== '' && !filter_var($enlace, FILTER_VALIDATE_URL)) $errores[] = 'El enlace no es una URL válida.';

    // Normalizar vacíos a NULL
    $h_ini_v  = $h_ini  !== '' ? $h_ini  : null;
    $h_fin_v  = $h_fin  !== '' ? $h_fin  : null;
    $lugar_v  = $lugar  !== '' ? $lugar  : null;
    $enlace_v = $enlace !== '' ? $enlace : null;
    $desc_v   = $desc   !== '' ? $desc   : null;

    if (!$errores) {
        if ($accion === 'crear') {
            $ins = $pdo->prepare(
                'INSERT INTO agenda (titulo, descripcion, etiqueta, tipo, fecha_evento,
                                     hora_inicio, hora_fin, lugar, enlace, id_usuario)
                 VALUES (:t, :d, :e, :ti, :f, :hi, :hf, :l, :en, :uid)'
            );
            $ins->execute([
                ':t' => $titulo, ':d' => $desc_v, ':e' => $etiqueta, ':ti' => $tipo,
                ':f' => $fecha, ':hi' => $h_ini_v, ':hf' => $h_fin_v,
                ':l' => $lugar_v, ':en' => $enlace_v, ':uid' => $u['id_usuario'],
            ]);
            flash('ok', 'Evento creado correctamente.');
            header('Location: gestion_agenda.php');
            exit;
        } else {
            $chk = $pdo->prepare('SELECT id_usuario FROM agenda WHERE id_evento = ?');
            $chk->execute([$id]);
            $autor = $chk->fetchColumn();
            if ($autor === false || !puede_gestionar((int)$autor)) {
                flash('error', 'No tenés permiso para editar ese evento.');
                header('Location: gestion_agenda.php');
                exit;
            }
            $upd = $pdo->prepare(
                'UPDATE agenda SET titulo=:t, descripcion=:d, etiqueta=:e, tipo=:ti,
                        fecha_evento=:f, hora_inicio=:hi, hora_fin=:hf, lugar=:l, enlace=:en
                 WHERE id_evento=:id'
            );
            $upd->execute([
                ':t' => $titulo, ':d' => $desc_v, ':e' => $etiqueta, ':ti' => $tipo,
                ':f' => $fecha, ':hi' => $h_ini_v, ':hf' => $h_fin_v,
                ':l' => $lugar_v, ':en' => $enlace_v, ':id' => $id,
            ]);
            flash('ok', 'Evento actualizado correctamente.');
            header('Location: gestion_agenda.php');
            exit;
        }
    } else {
        $modo = $accion;
        $edit = [
            'id_evento' => $id, 'titulo' => $titulo, 'descripcion' => $desc, 'etiqueta' => $etiqueta,
            'tipo' => $tipo, 'fecha_evento' => $fecha, 'hora_inicio' => $h_ini, 'hora_fin' => $h_fin,
            'lugar' => $lugar, 'enlace' => $enlace,
        ];
    }
}

// ── Listado (futuros primero) ──
$sql = 'SELECT a.id_evento, a.titulo, a.etiqueta, a.tipo, a.fecha_evento, a.hora_inicio,
               a.lugar, a.id_usuario, CONCAT(us.nombre," ",us.apellido) AS autor
        FROM agenda a JOIN usuarios us ON us.id_usuario = a.id_usuario
        ORDER BY a.fecha_evento DESC, a.hora_inicio ASC';
$lista = $pdo->query($sql)->fetchAll();

function color_etiqueta_ag(string $e): string {
    return match ($e) {
        'Inicial' => '#E63946', 'Primario' => '#1D3557', 'Secundario' => '#2a9d8f',
        'Técnica' => '#0d1b2a', 'Orientada' => '#457B9D',
        default => '#6d6875',
    };
}

// Sanitizar horas para el value (vienen como HH:MM:SS de la BD)
$hi_val = $edit['hora_inicio'] ? substr((string)$edit['hora_inicio'], 0, 5) : '';
$hf_val = $edit['hora_fin']    ? substr((string)$edit['hora_fin'], 0, 5)    : '';

$panel_page_title = 'Gestión de Agenda';
$panel_heading    = 'Gestión de <span>Agenda</span>';
$panel_sub        = 'Cargá, editá o eliminá los eventos del calendario.';
require __DIR__ . '/panel_header.php';
?>

    <div class="panel-toolbar">
      <a href="panel.php" class="back-link">
        <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg> Volver al panel
      </a>
    </div>

    <div class="form-card" style="margin-bottom:2.5rem;">
      <h2 style="font-family:var(--font-display);color:var(--blue-dark);font-size:1.35rem;margin-bottom:1.3rem;">
        <?= $modo === 'editar' ? 'Editar evento' : 'Nuevo evento' ?>
      </h2>

      <?php if ($errores): ?>
        <div class="alert alert-error"><?= implode('<br>', array_map('e', $errores)) ?></div>
      <?php endif; ?>

      <form method="post" action="gestion_agenda.php">
        <?= csrf_input() ?>
        <input type="hidden" name="accion" value="<?= $modo === 'editar' ? 'editar' : 'crear' ?>">
        <input type="hidden" name="id" value="<?= (int)$edit['id_evento'] ?>">

        <div class="field">
          <label for="titulo">Título</label>
          <input type="text" id="titulo" name="titulo" maxlength="180" required
                 value="<?= e($edit['titulo']) ?>" placeholder="Ej.: Reunión de padres — 6° grado">
        </div>

        <div class="field-row">
          <div class="field">
            <label for="etiqueta">Nivel / Etiqueta</label>
            <select id="etiqueta" name="etiqueta">
              <?php foreach (ETIQUETAS_VALIDAS as $et): ?>
                <option value="<?= e($et) ?>" <?= $edit['etiqueta'] === $et ? 'selected' : '' ?>><?= e($et) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label for="tipo">Tipo de evento</label>
            <select id="tipo" name="tipo">
              <?php foreach (TIPOS_EVENTO as $tp): ?>
                <option value="<?= e($tp) ?>" <?= $edit['tipo'] === $tp ? 'selected' : '' ?>><?= e($tp) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="fecha_evento">Fecha</label>
            <input type="date" id="fecha_evento" name="fecha_evento" required
                   value="<?= e((string)$edit['fecha_evento']) ?>">
          </div>
          <div class="field">
            <label for="lugar">Lugar <span class="hint">(opcional)</span></label>
            <input type="text" id="lugar" name="lugar" maxlength="180"
                   value="<?= e((string)($edit['lugar'] ?? '')) ?>" placeholder="Ej.: Patio central">
          </div>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="hora_inicio">Hora de inicio <span class="hint">(opcional)</span></label>
            <input type="time" id="hora_inicio" name="hora_inicio" value="<?= e($hi_val) ?>">
          </div>
          <div class="field">
            <label for="hora_fin">Hora de fin <span class="hint">(opcional)</span></label>
            <input type="time" id="hora_fin" name="hora_fin" value="<?= e($hf_val) ?>">
          </div>
        </div>

        <div class="field">
          <label for="enlace">Enlace <span class="hint">(opcional — formularios, etc.)</span></label>
          <input type="url" id="enlace" name="enlace"
                 value="<?= e((string)($edit['enlace'] ?? '')) ?>" placeholder="https://forms.gle/...">
        </div>

        <div class="field">
          <label for="descripcion">Descripción <span class="hint">(opcional)</span></label>
          <textarea id="descripcion" name="descripcion"
                    placeholder="Detalles del evento..."><?= e((string)($edit['descripcion'] ?? '')) ?></textarea>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">
            <?= $modo === 'editar' ? 'Guardar cambios' : 'Crear evento' ?>
          </button>
          <?php if ($modo === 'editar'): ?>
            <a href="gestion_agenda.php" class="btn btn-outline">Cancelar</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <h2 style="font-family:var(--font-display);color:var(--blue-dark);font-size:1.25rem;margin-bottom:1rem;">
      Eventos cargados
    </h2>

    <?php if (!$lista): ?>
      <div class="empty-state">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <p>Todavía no hay eventos cargados.</p>
      </div>
    <?php else: ?>
      <div style="overflow-x:auto;">
      <table class="mng-table">
        <thead>
          <tr><th>Fecha</th><th>Título</th><th>Tipo</th><th>Etiqueta</th><th>Autor</th><th style="text-align:right;">Acciones</th></tr>
        </thead>
        <tbody>
        <?php foreach ($lista as $row): ?>
          <?php $puede = puede_gestionar((int)$row['id_usuario']); ?>
          <tr>
            <td style="white-space:nowrap;">
              <?= e(date('d/m/Y', strtotime($row['fecha_evento']))) ?>
              <?php if ($row['hora_inicio']): ?>
                <span style="color:#889;font-size:.8rem;"><?= e(substr($row['hora_inicio'],0,5)) ?></span>
              <?php endif; ?>
            </td>
            <td><?= e($row['titulo']) ?></td>
            <td style="color:#667;"><?= e($row['tipo']) ?></td>
            <td><span class="mng-badge" style="background:<?= color_etiqueta_ag($row['etiqueta']) ?>"><?= e($row['etiqueta']) ?></span></td>
            <td><?= e($row['autor']) ?></td>
            <td>
              <div class="mng-actions" style="justify-content:flex-end;">
                <?php if ($puede): ?>
                  <a href="gestion_agenda.php?editar=<?= (int)$row['id_evento'] ?>" class="icon-btn" title="Editar">
                    <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                  </a>
                  <form method="post" action="gestion_agenda.php" style="display:inline;"
                        onsubmit="return confirm('¿Eliminar este evento? Esta acción no se puede deshacer.');">
                    <?= csrf_input() ?>
                    <input type="hidden" name="accion" value="borrar">
                    <input type="hidden" name="id" value="<?= (int)$row['id_evento'] ?>">
                    <button type="submit" class="icon-btn danger" title="Eliminar">
                      <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                  </form>
                <?php else: ?>
                  <button class="icon-btn" disabled title="Solo el autor o un admin pueden gestionarlo">
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
