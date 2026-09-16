<?php
// ============================================================
//  gestion_inscripciones.php
//  Panel de Control — Inscripciones
//
//  - Habilitar / deshabilitar cada formulario (solo administradores).
//  - Ver lo que se envió, clasificado por nivel → modalidad → año.
//  - Cambiar el estado de cada inscripción y dejar notas internas.
//  - Exportar a CSV (se abre con Excel).
//
//  Acceso: usuarios con el permiso "Inscripciones" (o administradores).
// ============================================================
require_once __DIR__ . '/panel_config.php';
require_once __DIR__ . '/partials/inscripciones.php';
exigir_login();

if (!puede('inscripciones')) {
    http_response_code(403);
    die('<p style="font-family:sans-serif;padding:2rem;color:#c1121f;">Acceso denegado: tu nivel de permisos no incluye Inscripciones.</p>');
}

$pdo = db();
if (!insc_asegurar_tablas($pdo)) {
    die('<p style="font-family:sans-serif;padding:2rem;color:#c1121f;">No se pudieron crear las tablas de inscripciones. Ejecutá <b>migracion_inscripciones.sql</b> en phpMyAdmin.</p>');
}

$formularios = insc_formularios();
$yo          = (int) usuario_actual()['id_usuario'];

// ============================================================
//  ACCIONES (POST)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $accion = $_POST['accion'] ?? '';
    $volver = 'gestion_inscripciones.php';

    if ($accion === 'formulario') {
        if (!es_admin()) {
            flash('error', 'Solo un administrador puede habilitar o deshabilitar formularios.');
        } else {
            $clave = (string) ($_POST['clave'] ?? '');
            $nuevo = ($_POST['habilitar'] ?? '') === '1' ? 1 : 0;
            if (isset($formularios[$clave])) {
                $pdo->prepare('UPDATE inscripcion_formularios SET habilitado = ?, id_usuario = ? WHERE clave = ?')
                    ->execute([$nuevo, $yo, $clave]);
                flash('ok', 'Formulario «' . $formularios[$clave]['nombre'] . '» ' . ($nuevo ? 'habilitado: ya se puede completar desde el sitio.' : 'deshabilitado: el sitio muestra "Actualmente el formulario no está habilitado".'));
            }
        }
    }

    if ($accion === 'estado') {
        $id     = (int) ($_POST['id'] ?? 0);
        $estado = (string) ($_POST['estado'] ?? '');
        $notas  = trim((string) ($_POST['notas'] ?? ''));
        if (in_array($estado, INSC_ESTADOS, true)) {
            $pdo->prepare('UPDATE inscripciones SET estado = ?, notas = ? WHERE id_inscripcion = ?')
                ->execute([$estado, mb_substr($notas, 0, 5000), $id]);
            flash('ok', 'Inscripción actualizada.');
        }
        $volver = 'gestion_inscripciones.php?ver=' . $id;
    }

    if ($accion === 'eliminar') {
        if (!es_admin()) {
            flash('error', 'Solo un administrador puede eliminar inscripciones.');
            $volver = 'gestion_inscripciones.php?ver=' . (int) ($_POST['id'] ?? 0);
        } else {
            $pdo->prepare('DELETE FROM inscripciones WHERE id_inscripcion = ?')->execute([(int) ($_POST['id'] ?? 0)]);
            flash('ok', 'Inscripción eliminada.');
        }
    }

    // Conserva los filtros con los que se estaba trabajando
    if ($volver === 'gestion_inscripciones.php' && !empty($_POST['qs'])) {
        $volver .= '?' . preg_replace('/[^\w=&%.\-+]/u', '', (string) $_POST['qs']);
    }
    header('Location: ' . $volver);
    exit;
}

// ============================================================
//  FILTROS
// ============================================================
$g = fn(string $k): string => is_string($_GET[$k] ?? null) ? $_GET[$k] : '';
$f_nivel  = isset(INSC_NIVELES[$g('nivel')]) ? $g('nivel') : '';
$f_mod    = ($f_nivel && in_array($g('modalidad'), INSC_NIVELES[$f_nivel]['modalidades'], true)) ? $g('modalidad') : '';
$f_anio   = ($f_nivel && isset(INSC_NIVELES[$f_nivel]['anios'][(int) $g('anio')])) ? (int) $g('anio') : 0;
$f_estado = in_array($g('estado'), INSC_ESTADOS, true) ? $g('estado') : '';
$f_form   = isset($formularios[$g('form')]) ? $g('form') : '';
$f_q      = trim(mb_substr($g('q'), 0, 80));

/** Arma la query string manteniendo los filtros actuales y pisando los indicados. */
function qs(array $cambios = []): string {
    global $f_nivel, $f_mod, $f_anio, $f_estado, $f_form, $f_q;
    $p = array_merge([
        'nivel' => $f_nivel, 'modalidad' => $f_mod, 'anio' => $f_anio ?: '',
        'estado' => $f_estado, 'form' => $f_form, 'q' => $f_q,
    ], $cambios);
    $p = array_filter($p, fn($v) => $v !== '' && $v !== null && $v !== 0);
    return $p ? '?' . http_build_query($p) : '';
}

$where = [];
$args  = [];
if ($f_nivel)  { $where[] = 'nivel = ?';      $args[] = $f_nivel; }
if ($f_mod)    { $where[] = 'modalidad = ?';  $args[] = $f_mod; }
if ($f_anio)   { $where[] = 'anio_orden = ?'; $args[] = $f_anio; }
if ($f_estado) { $where[] = 'estado = ?';     $args[] = $f_estado; }
if ($f_form)   { $where[] = 'formulario = ?'; $args[] = $f_form; }
if ($f_q !== '') {
    $where[] = '(alumno_nombre LIKE ? OR alumno_apellido LIKE ? OR alumno_dni LIKE ? OR tutor_nombre LIKE ? OR tutor_email LIKE ? OR grupo = ?)';
    $like = '%' . $f_q . '%';
    array_push($args, $like, $like, $like, $like, $like, strtoupper($f_q));
}
$sql_where = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$orden_sector = "FIELD(nivel,'Inicial','Primario','Secundario'), FIELD(modalidad,'General','Orientada','Técnica','A definir'), anio_orden";

// ============================================================
//  EXPORTAR CSV
// ============================================================
if (isset($_GET['exportar'])) {
    $st = $pdo->prepare("SELECT * FROM inscripciones $sql_where ORDER BY $orden_sector, creado_en");
    $st->execute($args);

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="inscripciones_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");   // BOM: Excel reconoce los acentos
    fputcsv($out, ['ID', 'Fecha', 'Código', 'Formulario', 'Nivel', 'Modalidad', 'Año', 'Apellidos', 'Nombres', 'DNI',
                   'Nacimiento', 'Responsable', 'Email', 'Teléfono', 'Estado', 'Notas', 'Datos completos'], ';');
    while ($r = $st->fetch()) {
        $completo = [];
        foreach (json_decode($r['datos'], true) ?: [] as $sec) {
            foreach ($sec['campos'] as [$l, $v]) $completo[] = "$l: $v";
        }
        fputcsv($out, [
            $r['id_inscripcion'], date('d/m/Y H:i', strtotime($r['creado_en'])), $r['grupo'],
            $formularios[$r['formulario']]['nombre'] ?? $r['formulario'],
            $r['nivel'], $r['modalidad'] === 'General' ? '' : $r['modalidad'], $r['anio'],
            $r['alumno_apellido'], $r['alumno_nombre'], $r['alumno_dni'],
            $r['alumno_fnac'] ? date('d/m/Y', strtotime($r['alumno_fnac'])) : '',
            $r['tutor_nombre'], $r['tutor_email'], $r['tutor_tel'], $r['estado'], (string) $r['notas'],
            implode(' | ', $completo),
        ], ';');
    }
    fclose($out);
    exit;
}

// ============================================================
//  DATOS PARA LA VISTA
// ============================================================
$ver = isset($_GET['ver']) ? (int) $_GET['ver'] : 0;
$detalle = null;
$hermanos = [];
if ($ver) {
    $st = $pdo->prepare('SELECT * FROM inscripciones WHERE id_inscripcion = ?');
    $st->execute([$ver]);
    $detalle = $st->fetch() ?: null;
    if ($detalle) {
        $st = $pdo->prepare('SELECT id_inscripcion, alumno_nombre, alumno_apellido, nivel, modalidad, anio FROM inscripciones WHERE grupo = ? AND id_inscripcion <> ? ORDER BY id_inscripcion');
        $st->execute([$detalle['grupo'], $detalle['id_inscripcion']]);
        $hermanos = $st->fetchAll();
    }
}

$estados_form = insc_estados_formularios($pdo);
$por_form = $pdo->query("SELECT formulario, COUNT(*) total, SUM(estado='Nueva') nuevas FROM inscripciones GROUP BY formulario")->fetchAll(PDO::FETCH_UNIQUE);

// Árbol de sectores (conteos sobre todo, sin filtros)
$arbol = [];
$total_general = 0;
$nuevas_general = 0;
foreach ($pdo->query("SELECT nivel, modalidad, anio_orden, COUNT(*) total, SUM(estado='Nueva') nuevas
                        FROM inscripciones GROUP BY nivel, modalidad, anio_orden") as $r) {
    $arbol[$r['nivel']]['total']  = ($arbol[$r['nivel']]['total']  ?? 0) + $r['total'];
    $arbol[$r['nivel']]['nuevas'] = ($arbol[$r['nivel']]['nuevas'] ?? 0) + $r['nuevas'];
    $m = &$arbol[$r['nivel']]['mods'][$r['modalidad']];
    $m['total']  = ($m['total']  ?? 0) + $r['total'];
    $m['nuevas'] = ($m['nuevas'] ?? 0) + $r['nuevas'];
    $m['anios'][(int) $r['anio_orden']] = ['total' => (int) $r['total'], 'nuevas' => (int) $r['nuevas']];
    unset($m);
    $total_general  += $r['total'];
    $nuevas_general += $r['nuevas'];
}

$lista = [];
if (!$detalle) {
    $st = $pdo->prepare("SELECT * FROM inscripciones $sql_where ORDER BY $orden_sector, creado_en DESC LIMIT 1000");
    $st->execute($args);
    $lista = $st->fetchAll();
}

const COLOR_ESTADO = [
    'Nueva'       => '#E63946',
    'En revisión' => '#e09f3e',
    'Contactada'  => '#457B9D',
    'Aceptada'    => '#1b7a44',
    'Descartada'  => '#9aa0a6',
];

$qs_actual = ltrim(qs(), '?');

$panel_page_title = 'Inscripciones';
$panel_heading    = 'Gestión de <span>inscripciones</span>';
$panel_sub        = 'Habilitá los formularios del sitio y revisá lo que enviaron las familias, por nivel, modalidad y año.';
require __DIR__ . '/panel_header.php';
?>

<div class="panel-toolbar">
  <a href="<?= $detalle ? 'gestion_inscripciones.php' . e(qs()) : 'panel.php' ?>" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    <?= $detalle ? 'Volver al listado' : 'Volver al panel' ?>
  </a>
</div>

<?php if ($detalle):
  // ========================================================
  //  DETALLE DE UNA INSCRIPCIÓN
  // ========================================================
  $d = $detalle;
  $datos = json_decode($d['datos'], true) ?: [];
?>
  <div class="ins-detalle">
    <div class="ins-det-main">
      <div class="ins-det-head" style="--nivel:<?= INSC_COLOR_NIVEL[$d['nivel']] ?>">
        <div>
          <p class="ins-sector"><?= e(insc_sector($d['nivel'], $d['modalidad'], $d['anio'])) ?></p>
          <h2><?= e($d['alumno_apellido'] . ', ' . $d['alumno_nombre']) ?></h2>
          <p class="ins-meta">
            Recibida el <?= e(date('d/m/Y \a \l\a\s H:i', strtotime($d['creado_en']))) ?>
            · Formulario <?= e($formularios[$d['formulario']]['nombre'] ?? $d['formulario']) ?>
            · Código <code><?= e($d['grupo']) ?></code>
          </p>
        </div>
        <span class="ins-estado" style="--c:<?= COLOR_ESTADO[$d['estado']] ?>"><?= e($d['estado']) ?></span>
      </div>

      <?php if ($hermanos): ?>
        <div class="ins-hermanos">
          <b>Enviada junto con sus hermanos/as:</b>
          <?php foreach ($hermanos as $h): ?>
            <a href="gestion_inscripciones.php?ver=<?= (int) $h['id_inscripcion'] ?>">
              <?= e($h['alumno_nombre'] . ' ' . $h['alumno_apellido']) ?>
              <small><?= e(insc_sector($h['nivel'], $h['modalidad'], $h['anio'])) ?></small>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php foreach ($datos as $sec): ?>
        <section class="ins-sec">
          <h3><?= e($sec['seccion']) ?></h3>
          <dl>
            <?php foreach ($sec['campos'] as [$l, $v]): ?>
              <div><dt><?= e($l) ?></dt><dd><?= nl2br(e((string) $v)) ?></dd></div>
            <?php endforeach; ?>
          </dl>
        </section>
      <?php endforeach; ?>
    </div>

    <aside class="ins-det-side">
      <div class="ins-card">
        <h3>Contacto rápido</h3>
        <p><b><?= e($d['tutor_nombre']) ?></b></p>
        <p><a href="mailto:<?= e($d['tutor_email']) ?>"><?= e($d['tutor_email']) ?></a></p>
        <p><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $d['tutor_tel'])) ?>"><?= e($d['tutor_tel']) ?></a></p>
      </div>

      <form method="post" class="ins-card">
        <?= csrf_input() ?>
        <input type="hidden" name="accion" value="estado"/>
        <input type="hidden" name="id" value="<?= (int) $d['id_inscripcion'] ?>"/>
        <h3>Seguimiento</h3>
        <div class="field">
          <label for="estado">Estado</label>
          <select id="estado" name="estado">
            <?php foreach (INSC_ESTADOS as $es): ?>
              <option<?= $es === $d['estado'] ? ' selected' : '' ?>><?= e($es) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label for="notas">Notas internas <span class="hint">(no las ve la familia)</span></label>
          <textarea id="notas" name="notas" rows="5"><?= e((string) $d['notas']) ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;">Guardar</button>
      </form>

      <?php if (es_admin()): ?>
        <form method="post" onsubmit="return confirm('¿Eliminar esta inscripción? No se puede deshacer.');">
          <?= csrf_input() ?>
          <input type="hidden" name="accion" value="eliminar"/>
          <input type="hidden" name="id" value="<?= (int) $d['id_inscripcion'] ?>"/>
          <button type="submit" class="ins-eliminar">Eliminar inscripción</button>
        </form>
      <?php endif; ?>
    </aside>
  </div>

<?php elseif ($ver): ?>
  <div class="alert alert-error">La inscripción no existe o fue eliminada.</div>

<?php else:
  // ========================================================
  //  LISTADO
  // ========================================================
?>

  <!-- ── Formularios del sitio ── -->
  <h2 class="ins-titulo">Formularios del sitio</h2>
  <?php if (!es_admin()): ?>
    <p class="ins-aclara">Solo un administrador puede habilitarlos o deshabilitarlos.</p>
  <?php endif; ?>
  <div class="ins-forms">
    <?php foreach ($formularios as $clave => $fd):
      $on = (int) ($estados_form[$clave]['habilitado'] ?? 0) === 1;
      $cnt = $por_form[$clave] ?? ['total' => 0, 'nuevas' => 0]; ?>
      <div class="ins-form-card<?= $on ? ' is-on' : '' ?>" style="--acc:<?= e($fd['accent']) ?>">
        <div class="ins-form-top">
          <span class="ins-form-ico" aria-hidden="true"><?= $fd['icono'] ?></span>
          <div>
            <h3><?= e($fd['nombre']) ?></h3>
            <a href="<?= e($fd['archivo']) ?>" target="_blank" rel="noopener" class="ins-form-link">Ver en el sitio ↗</a>
          </div>
        </div>
        <p class="ins-form-cnt">
          <b><?= (int) $cnt['total'] ?></b> recibida<?= (int) $cnt['total'] === 1 ? '' : 's' ?>
          <?php if ((int) $cnt['nuevas'] > 0): ?><span class="ins-pill-nueva"><?= (int) $cnt['nuevas'] ?> nueva<?= (int) $cnt['nuevas'] === 1 ? '' : 's' ?></span><?php endif; ?>
        </p>
        <?php if (es_admin()): ?>
          <form method="post" class="ins-toggle-form">
            <?= csrf_input() ?>
            <input type="hidden" name="accion" value="formulario"/>
            <input type="hidden" name="clave" value="<?= e($clave) ?>"/>
            <input type="hidden" name="habilitar" value="<?= $on ? '0' : '1' ?>"/>
            <input type="hidden" name="qs" value="<?= e($qs_actual) ?>"/>
            <button type="submit" class="ins-switch" role="switch" aria-checked="<?= $on ? 'true' : 'false' ?>"
                    title="<?= $on ? 'Deshabilitar' : 'Habilitar' ?> formulario">
              <span class="ins-switch-track"><span class="ins-switch-thumb"></span></span>
              <span class="ins-switch-txt"><?= $on ? 'Habilitado' : 'Deshabilitado' ?></span>
            </button>
          </form>
        <?php else: ?>
          <span class="ins-switch-txt ins-switch-ro"><?= $on ? '● Habilitado' : '○ Deshabilitado' ?></span>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- ── Inscripciones recibidas ── -->
  <h2 class="ins-titulo" style="margin-top:2.6rem;">Inscripciones recibidas</h2>

  <div class="ins-layout">

    <!-- Árbol: nivel → modalidad → año -->
    <nav class="ins-arbol" aria-label="Clasificación por nivel, modalidad y año">
      <a href="gestion_inscripciones.php<?= e(qs(['nivel' => '', 'modalidad' => '', 'anio' => ''])) ?>"
         class="ins-nodo n0<?= !$f_nivel ? ' act' : '' ?>">
        Todas <span class="ins-cnt"><?= $total_general ?></span>
      </a>
      <?php foreach (INSC_NIVELES as $nivel => $info):
        $nd = $arbol[$nivel] ?? ['total' => 0, 'nuevas' => 0, 'mods' => []];
        $sin_modalidad = $info['modalidades'] === ['General']; ?>
        <div class="ins-grupo" style="--nivel:<?= INSC_COLOR_NIVEL[$nivel] ?>">
          <a href="gestion_inscripciones.php<?= e(qs(['nivel' => $nivel, 'modalidad' => '', 'anio' => ''])) ?>"
             class="ins-nodo n1<?= $f_nivel === $nivel && !$f_mod && !$f_anio ? ' act' : '' ?>">
            <?= e($nivel) ?>
            <span class="ins-cnt<?= $nd['nuevas'] ? ' hay-nuevas' : '' ?>" title="<?= (int) $nd['nuevas'] ?> nuevas"><?= (int) $nd['total'] ?></span>
          </a>
          <?php if ($f_nivel === $nivel): // se despliega solo el nivel elegido ?>
            <?php foreach ($info['modalidades'] as $mod):
              $md = $nd['mods'][$mod] ?? ['total' => 0, 'nuevas' => 0, 'anios' => []];
              if (!$sin_modalidad): ?>
                <a href="gestion_inscripciones.php<?= e(qs(['modalidad' => $mod, 'anio' => ''])) ?>"
                   class="ins-nodo n2<?= $f_mod === $mod && !$f_anio ? ' act' : '' ?>">
                  <?= e($mod) ?> <span class="ins-cnt<?= $md['nuevas'] ? ' hay-nuevas' : '' ?>"><?= (int) $md['total'] ?></span>
                </a>
              <?php endif;
              if ($sin_modalidad || $f_mod === $mod):
                foreach ($info['anios'] as $ord => $txt):
                  if ($mod === 'Orientada' && $ord > 6) continue;
                  $ad = $md['anios'][$ord] ?? ['total' => 0, 'nuevas' => 0]; ?>
                  <a href="gestion_inscripciones.php<?= e(qs(['modalidad' => $mod, 'anio' => $ord])) ?>"
                     class="ins-nodo n3<?= $f_anio === $ord ? ' act' : '' ?><?= $ad['total'] ? '' : ' vacio' ?>">
                    <?= e($txt) ?> <span class="ins-cnt<?= $ad['nuevas'] ? ' hay-nuevas' : '' ?>"><?= $ad['total'] ?></span>
                  </a>
                <?php endforeach;
              endif;
            endforeach; ?>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
      <p class="ins-arbol-ley"><span class="ins-cnt hay-nuevas">n</span> hay inscripciones nuevas</p>
    </nav>

    <div class="ins-contenido">
      <form method="get" class="ins-filtros">
        <?php if ($f_nivel): ?><input type="hidden" name="nivel" value="<?= e($f_nivel) ?>"/><?php endif; ?>
        <?php if ($f_mod): ?><input type="hidden" name="modalidad" value="<?= e($f_mod) ?>"/><?php endif; ?>
        <?php if ($f_anio): ?><input type="hidden" name="anio" value="<?= $f_anio ?>"/><?php endif; ?>
        <input type="search" name="q" value="<?= e($f_q) ?>" placeholder="Buscar alumno, DNI, responsable o código…" aria-label="Buscar"/>
        <select name="estado" aria-label="Estado">
          <option value="">Todos los estados</option>
          <?php foreach (INSC_ESTADOS as $es): ?><option<?= $f_estado === $es ? ' selected' : '' ?>><?= e($es) ?></option><?php endforeach; ?>
        </select>
        <select name="form" aria-label="Formulario">
          <option value="">Todos los formularios</option>
          <?php foreach ($formularios as $k => $fd): ?><option value="<?= e($k) ?>"<?= $f_form === $k ? ' selected' : '' ?>><?= e($fd['nombre']) ?></option><?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="gestion_inscripciones.php<?= e(qs(['exportar' => 1])) ?>" class="btn btn-secundario" title="Descarga lo que estás viendo">Exportar CSV</a>
      </form>

      <p class="ins-resumen">
        <?= count($lista) ?> inscripcion<?= count($lista) === 1 ? '' : 'es' ?>
        <?php if ($f_nivel): ?>en <b><?= e(implode(' › ', array_filter([$f_nivel, $f_mod !== 'General' ? $f_mod : '', $f_anio ? INSC_NIVELES[$f_nivel]['anios'][$f_anio] : '']))) ?></b><?php endif; ?>
        <?php if ($f_nivel || $f_estado || $f_form || $f_q !== ''): ?>· <a href="gestion_inscripciones.php">Quitar filtros</a><?php endif; ?>
      </p>

      <?php if (!$lista): ?>
        <div class="empty-state" style="background:#fff;border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);">
          <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><polyline points="14 2 14 8 20 8"/></svg>
          <p><?= $total_general ? 'No hay inscripciones con estos filtros.' : 'Todavía no llegó ninguna inscripción.' ?></p>
        </div>
      <?php else: ?>
        <div class="ins-tabla-wrap">
        <table class="mng-table ins-tabla">
          <thead>
            <tr><th>Alumno/a</th><th>DNI</th><th>Responsable</th><th>Recibida</th><th>Estado</th><th></th></tr>
          </thead>
          <tbody>
          <?php $sector_prev = null;
          foreach ($lista as $r):
            $sector = insc_sector($r['nivel'], $r['modalidad'], $r['anio']);
            if ($sector !== $sector_prev): $sector_prev = $sector; ?>
              <tr class="ins-sector-row" style="--nivel:<?= INSC_COLOR_NIVEL[$r['nivel']] ?>">
                <td colspan="6"><?= e($sector) ?></td>
              </tr>
            <?php endif; ?>
            <tr class="<?= $r['estado'] === 'Nueva' ? 'is-nueva' : '' ?>">
              <td>
                <a href="gestion_inscripciones.php?ver=<?= (int) $r['id_inscripcion'] ?>" class="ins-nombre">
                  <?= e($r['alumno_apellido'] . ', ' . $r['alumno_nombre']) ?>
                </a>
                <?php if ($r['formulario'] === 'hermanos'): ?><span class="ins-tag">Hermanos</span><?php endif; ?>
              </td>
              <td><?= e($r['alumno_dni']) ?></td>
              <td>
                <?= e($r['tutor_nombre']) ?>
                <small class="ins-sub"><?= e($r['tutor_tel']) ?></small>
              </td>
              <td style="white-space:nowrap;color:#667;"><?= e(date('d/m/Y', strtotime($r['creado_en']))) ?></td>
              <td><span class="ins-estado" style="--c:<?= COLOR_ESTADO[$r['estado']] ?>"><?= e($r['estado']) ?></span></td>
              <td style="text-align:right;">
                <a href="gestion_inscripciones.php?ver=<?= (int) $r['id_inscripcion'] ?>" class="icon-btn" title="Ver inscripción" aria-label="Ver inscripción">
                  <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

<?php endif; ?>

<style>
  .ins-titulo { font-family: var(--font-display); font-size: 1.3rem; color: var(--blue-dark); margin-bottom: .9rem; }
  .ins-aclara { font-size: .85rem; color: #667; margin: -.5rem 0 .9rem; }

  /* Tarjetas de formularios */
  .ins-forms { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 1rem; }
  .ins-form-card {
    background: #fff; border-radius: 16px; padding: 1.1rem 1.2rem 1.2rem;
    box-shadow: var(--shadow-sm); border-top: 4px solid #cfd4da; display: flex; flex-direction: column; gap: .7rem;
  }
  .ins-form-card.is-on { border-top-color: var(--acc); }
  .ins-form-top { display: flex; gap: .75rem; align-items: center; }
  .ins-form-ico { width: 42px; height: 42px; border-radius: 12px; background: var(--gray-100); display: flex; align-items: center; justify-content: center; font-size: 1.35rem; flex-shrink: 0; }
  .ins-form-top h3 { font-family: var(--font-display); font-size: 1.05rem; color: var(--blue-dark); line-height: 1.2; }
  .ins-form-link { font-size: .76rem; color: var(--blue-mid); font-weight: 700; }
  .ins-form-cnt { font-size: .85rem; color: #556; display: flex; align-items: center; gap: .45rem; flex-wrap: wrap; }
  .ins-pill-nueva { background: rgba(230,57,70,.12); color: #c1121f; font-size: .7rem; font-weight: 800; padding: .1rem .5rem; border-radius: 999px; }

  .ins-switch { display: inline-flex; align-items: center; gap: .6rem; background: none; border: none; cursor: pointer; font-family: var(--font-body); padding: 0; }
  .ins-switch-track { width: 44px; height: 24px; border-radius: 999px; background: #cfd4da; position: relative; transition: background .2s; flex-shrink: 0; }
  .ins-switch-thumb { position: absolute; top: 3px; left: 3px; width: 18px; height: 18px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.25); transition: transform .2s; }
  .ins-switch[aria-checked="true"] .ins-switch-track { background: #1b7a44; }
  .ins-switch[aria-checked="true"] .ins-switch-thumb { transform: translateX(20px); }
  .ins-switch:focus-visible { outline: 3px solid var(--blue-mid); outline-offset: 3px; border-radius: 6px; }
  .ins-switch-txt { font-size: .84rem; font-weight: 700; color: #667; }
  .ins-switch[aria-checked="true"] .ins-switch-txt, .is-on .ins-switch-ro { color: #1b7a44; }

  /* Layout listado */
  .ins-layout { display: grid; grid-template-columns: 230px minmax(0, 1fr); gap: 1.4rem; align-items: start; }
  .ins-arbol { background: #fff; border-radius: 16px; box-shadow: var(--shadow-sm); padding: .7rem; position: sticky; top: 1rem; }
  .ins-grupo { border-top: 1px solid #eef0f3; padding: .3rem 0; }
  .ins-nodo {
    display: flex; align-items: center; justify-content: space-between; gap: .5rem;
    padding: .45rem .6rem; border-radius: 8px; font-size: .88rem; color: var(--blue-dark); transition: background .15s;
  }
  .ins-nodo:hover { background: var(--gray-100); }
  .ins-nodo.act { background: var(--blue-dark); color: #fff; }
  .ins-nodo.n0 { font-weight: 800; }
  .ins-nodo.n1 { font-weight: 800; border-left: 3px solid var(--nivel); border-radius: 0 8px 8px 0; }
  .ins-nodo.n2 { padding-left: 1.3rem; font-weight: 700; font-size: .85rem; }
  .ins-nodo.n3 { padding-left: 2.1rem; font-size: .82rem; }
  .ins-grupo .ins-nodo.n3:first-of-type { margin-top: 0; }
  .ins-nodo.vacio { color: #9aa0a6; }
  .ins-cnt { font-size: .72rem; font-weight: 800; min-width: 24px; text-align: center; padding: .05rem .4rem; border-radius: 999px; background: var(--gray-200); color: var(--blue-dark); }
  .ins-cnt.hay-nuevas { background: var(--red); color: #fff; }
  .ins-nodo.act .ins-cnt:not(.hay-nuevas) { background: rgba(255,255,255,.2); color: #fff; }
  .ins-arbol-ley { font-size: .72rem; color: #8a9099; padding: .6rem .6rem .2rem; border-top: 1px solid #eef0f3; margin-top: .3rem; display: flex; align-items: center; gap: .4rem; }

  .ins-filtros { display: flex; gap: .6rem; flex-wrap: wrap; margin-bottom: .8rem; }
  .ins-filtros input[type=search], .ins-filtros select {
    font-family: var(--font-body); font-size: .88rem; padding: .6rem .8rem; border: 1.5px solid #dfe3e8; border-radius: 10px; background: #fff;
  }
  .ins-filtros input[type=search] { flex: 1 1 220px; }
  .ins-filtros .btn { padding: .6rem 1.1rem; font-size: .85rem; }
  .ins-resumen { font-size: .85rem; color: #667; margin-bottom: .8rem; }
  .ins-resumen a { color: var(--blue-mid); font-weight: 700; }

  .ins-tabla-wrap { overflow-x: auto; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); }
  .ins-tabla { box-shadow: none; min-width: 640px; }
  .ins-sector-row td {
    background: #f1f4f8 !important; font-weight: 800; font-size: .8rem !important; color: var(--blue-dark);
    text-transform: uppercase; letter-spacing: .04em; border-left: 4px solid var(--nivel); padding: .55rem 1rem !important;
  }
  .ins-tabla tr.is-nueva td:first-child { box-shadow: inset 3px 0 0 var(--red); }
  .ins-nombre { font-weight: 700; color: var(--blue-dark); }
  .ins-nombre:hover { color: var(--red); }
  .ins-sub { display: block; color: #8a9099; font-size: .76rem; }
  .ins-tag { font-size: .66rem; font-weight: 800; color: #6d6875; background: #efedf1; padding: .1rem .45rem; border-radius: 999px; margin-left: .3rem; }
  .ins-estado {
    display: inline-block; font-size: .72rem; font-weight: 800; white-space: nowrap;
    color: var(--c); background: color-mix(in srgb, var(--c) 13%, #fff); padding: .2rem .6rem; border-radius: 999px;
  }

  /* Detalle */
  .ins-detalle { display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 1.4rem; align-items: start; }
  .ins-det-main { background: #fff; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); padding: 1.8rem; }
  .ins-det-head { display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; padding-bottom: 1.1rem; margin-bottom: 1.2rem; border-bottom: 1px solid #eef0f3; }
  .ins-sector { font-size: .78rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--nivel); }
  .ins-det-head h2 { font-family: var(--font-display); font-size: 1.6rem; color: var(--blue-dark); line-height: 1.2; margin: .15rem 0 .3rem; }
  .ins-meta { font-size: .82rem; color: #778; }
  .ins-meta code { background: var(--gray-100); padding: .05rem .35rem; border-radius: 4px; }
  .ins-hermanos { background: #f4f2f6; border-radius: 12px; padding: .8rem 1rem; margin-bottom: 1.2rem; font-size: .85rem; display: flex; flex-wrap: wrap; gap: .4rem .9rem; align-items: baseline; }
  .ins-hermanos a { color: var(--blue-dark); font-weight: 700; }
  .ins-hermanos a small { font-weight: 400; color: #778; }
  .ins-sec { margin-bottom: 1.4rem; }
  .ins-sec h3 { font-size: .8rem; text-transform: uppercase; letter-spacing: .05em; color: var(--blue-mid); margin-bottom: .6rem; }
  .ins-sec dl { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .1rem 1.4rem; }
  .ins-sec dl > div { padding: .45rem 0; border-bottom: 1px dashed #eceff3; }
  .ins-sec dt { font-size: .74rem; color: #8a9099; }
  .ins-sec dd { font-size: .92rem; color: var(--text); font-weight: 600; overflow-wrap: anywhere; }
  .ins-det-side { display: flex; flex-direction: column; gap: 1rem; position: sticky; top: 1rem; }
  .ins-card { background: #fff; border-radius: 16px; box-shadow: var(--shadow-sm); padding: 1.2rem; }
  .ins-card h3 { font-family: var(--font-display); font-size: 1.05rem; color: var(--blue-dark); margin-bottom: .7rem; }
  .ins-card p { font-size: .88rem; margin-bottom: .2rem; overflow-wrap: anywhere; }
  .ins-card p a { color: var(--blue-mid); font-weight: 700; }
  .ins-card textarea { min-height: 110px; }
  .ins-eliminar { width: 100%; background: none; border: 1.5px solid rgba(193,18,31,.35); color: #c1121f; font-weight: 700; font-family: var(--font-body); padding: .6rem; border-radius: 10px; cursor: pointer; }
  .ins-eliminar:hover { background: #fdecee; }

  @media (max-width: 860px) {
    .ins-layout, .ins-detalle { grid-template-columns: 1fr; }
    .ins-arbol, .ins-det-side { position: static; }
    .ins-sec dl { grid-template-columns: 1fr; }
  }
</style>

  </div>
</body>
</html>
