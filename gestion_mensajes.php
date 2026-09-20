<?php
// ============================================================
//  gestion_mensajes.php
//  Panel de Control — Mensajes de contacto
//
//  - Bandeja con lo que llega desde contacto.php.
//  - Clasificación por carpeta (entrada, destacados, respondidos,
//    archivados), por asunto y por nivel.
//  - Estado de cada mensaje, notas internas y respuesta por correo.
//  - Acciones masivas y exportación a CSV.
//
//  Acceso: usuarios con el permiso "Mensajes de contacto" (o administradores).
// ============================================================
require_once __DIR__ . '/panel_config.php';
require_once __DIR__ . '/partials/mensajes.php';
exigir_login();

$pdo = db();
if (!msj_asegurar_tabla($pdo)) {
    die('<p style="font-family:sans-serif;padding:2rem;color:#c1121f;">No se pudo crear la tabla de mensajes. Ejecutá <b>migracion_mensajes.sql</b> en phpMyAdmin.</p>');
}
if (!puede('mensajes')) {
    http_response_code(403);
    die('<p style="font-family:sans-serif;padding:2rem;color:#c1121f;">Acceso denegado: tu nivel de permisos no incluye Mensajes de contacto.</p>');
}

$yo = (int) usuario_actual()['id_usuario'];

// ── Carpetas: cada una es un filtro fijo sobre estado/destacado ──
const MSJ_CARPETAS = [
    'entrada'     => ['Bandeja de entrada', "estado <> 'Archivado'"],
    'nuevos'      => ['Sin leer',           "estado = 'Nuevo'"],
    'destacados'  => ['Destacados',         'destacado = 1'],
    'respondidos' => ['Respondidos',        "estado = 'Respondido'"],
    'archivados'  => ['Archivados',         "estado = 'Archivado'"],
    'todos'       => ['Todos',              '1 = 1'],
];

// ============================================================
//  ACCIONES (POST)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $accion = (string) ($_POST['accion'] ?? '');
    $id     = (int) ($_POST['id'] ?? 0);
    $volver = 'gestion_mensajes';
    $qs     = preg_replace('/[^\w=&%.\-+]/u', '', (string) ($_POST['qs'] ?? ''));

    if ($accion === 'guardar') {
        $estado = (string) ($_POST['estado'] ?? '');
        $asunto = (string) ($_POST['asunto'] ?? '');
        $nivel  = (string) ($_POST['nivel'] ?? '');
        if (in_array($estado, MSJ_ESTADOS, true) && in_array($asunto, MSJ_ASUNTOS, true) && in_array($nivel, MSJ_NIVELES, true)) {
            $pdo->prepare('UPDATE mensajes_contacto SET estado = ?, asunto = ?, nivel = ?, notas = ?, id_usuario = ? WHERE id_mensaje = ?')
                ->execute([$estado, $asunto, $nivel, mb_substr(trim((string) ($_POST['notas'] ?? '')), 0, 5000), $yo, $id]);
            flash('ok', 'Mensaje actualizado.');
        }
        $volver = 'gestion_mensajes?ver=' . $id;
    }

    if ($accion === 'estado_rapido') {
        $estado = (string) ($_POST['estado'] ?? '');
        if (in_array($estado, MSJ_ESTADOS, true)) {
            $pdo->prepare('UPDATE mensajes_contacto SET estado = ?, id_usuario = ? WHERE id_mensaje = ?')
                ->execute([$estado, $yo, $id]);
            flash('ok', match ($estado) {
                'Respondido' => 'Marcado como respondido.',
                'Archivado'  => 'Mensaje archivado.',
                'Nuevo'      => 'Marcado como no leído.',
                default      => 'Estado actualizado.',
            });
        }
        // Archivar o marcar como no leído vuelve al listado; lo demás queda en el detalle
        $volver = in_array($estado, ['Archivado', 'Nuevo'], true) ? 'gestion_mensajes' : 'gestion_mensajes?ver=' . $id;
    }

    if ($accion === 'destacar') {
        $pdo->prepare('UPDATE mensajes_contacto SET destacado = 1 - destacado WHERE id_mensaje = ?')->execute([$id]);
        if (!empty($_POST['desde_detalle'])) $volver = 'gestion_mensajes?ver=' . $id;
    }

    if ($accion === 'masivo') {
        $ids = array_values(array_filter(array_map('intval', (array) ($_POST['ids'] ?? []))));
        $op  = (string) ($_POST['op'] ?? '');
        if (!$ids) {
            flash('info', 'No seleccionaste ningún mensaje.');
        } else {
            $in = implode(',', array_fill(0, count($ids), '?'));
            $n  = count($ids);
            $txt = $n . ' mensaje' . ($n === 1 ? '' : 's');
            $mapa = ['leido' => 'Leído', 'nuevo' => 'Nuevo', 'respondido' => 'Respondido', 'archivar' => 'Archivado'];
            if (isset($mapa[$op])) {
                $pdo->prepare("UPDATE mensajes_contacto SET estado = ?, id_usuario = ? WHERE id_mensaje IN ($in)")
                    ->execute(array_merge([$mapa[$op], $yo], $ids));
                flash('ok', "$txt → " . mb_strtolower($mapa[$op]) . '.');
            } elseif ($op === 'desarchivar') {
                $pdo->prepare("UPDATE mensajes_contacto SET estado = 'Leído', id_usuario = ? WHERE estado = 'Archivado' AND id_mensaje IN ($in)")
                    ->execute(array_merge([$yo], $ids));
                flash('ok', "$txt de vuelta en la bandeja de entrada.");
            } elseif ($op === 'eliminar') {
                if (!es_admin()) {
                    flash('error', 'Solo un administrador puede eliminar mensajes.');
                } else {
                    $pdo->prepare("DELETE FROM mensajes_contacto WHERE id_mensaje IN ($in)")->execute($ids);
                    flash('ok', "$txt eliminado" . ($n === 1 ? '' : 's') . '.');
                }
            }
        }
    }

    if ($accion === 'eliminar') {
        if (!es_admin()) {
            flash('error', 'Solo un administrador puede eliminar mensajes.');
            $volver = 'gestion_mensajes?ver=' . $id;
        } else {
            $pdo->prepare('DELETE FROM mensajes_contacto WHERE id_mensaje = ?')->execute([$id]);
            flash('ok', 'Mensaje eliminado.');
        }
    }

    // Conserva la carpeta y los filtros con los que se estaba trabajando
    if ($volver === 'gestion_mensajes' && $qs !== '') $volver .= '?' . $qs;
    header('Location: ' . $volver);
    exit;
}

// ============================================================
//  FILTROS
// ============================================================
$g = fn(string $k): string => is_string($_GET[$k] ?? null) ? $_GET[$k] : '';
$f_carpeta = isset(MSJ_CARPETAS[$g('carpeta')]) ? $g('carpeta') : 'entrada';
$f_asunto  = in_array($g('asunto'), MSJ_ASUNTOS, true) ? $g('asunto') : '';
$f_nivel   = in_array($g('nivel'), MSJ_NIVELES, true) ? $g('nivel') : '';
$f_q       = trim(mb_substr($g('q'), 0, 80));
$f_orden   = $g('orden') === 'antiguos' ? 'antiguos' : 'recientes';

/** Query string con los filtros actuales, pisando los indicados. */
function qs(array $cambios = []): string {
    global $f_carpeta, $f_asunto, $f_nivel, $f_q, $f_orden;
    $p = array_merge([
        'carpeta' => $f_carpeta === 'entrada' ? '' : $f_carpeta,
        'asunto'  => $f_asunto, 'nivel' => $f_nivel, 'q' => $f_q,
        'orden'   => $f_orden === 'recientes' ? '' : $f_orden,
    ], $cambios);
    if (($p['carpeta'] ?? '') === 'entrada') $p['carpeta'] = '';
    $p = array_filter($p, fn($v) => $v !== '' && $v !== null);
    return $p ? '?' . http_build_query($p) : '';
}

$where = [MSJ_CARPETAS[$f_carpeta][1]];
$args  = [];
if ($f_asunto) { $where[] = 'asunto = ?'; $args[] = $f_asunto; }
if ($f_nivel)  { $where[] = 'nivel = ?';  $args[] = $f_nivel; }
if ($f_q !== '') {
    $where[] = '(nombre LIKE ? OR email LIKE ? OR telefono LIKE ? OR mensaje LIKE ?)';
    array_push($args, ...array_fill(0, 4, '%' . $f_q . '%'));
}
$sql_where = 'WHERE ' . implode(' AND ', $where);
$sql_orden = $f_orden === 'antiguos' ? 'creado_en ASC' : 'creado_en DESC';

// ============================================================
//  EXPORTAR CSV
// ============================================================
if (isset($_GET['exportar'])) {
    $st = $pdo->prepare("SELECT * FROM mensajes_contacto $sql_where ORDER BY $sql_orden");
    $st->execute($args);
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="mensajes_contacto_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");   // BOM: Excel reconoce los acentos
    fputcsv($out, ['ID', 'Fecha', 'Nombre', 'Email', 'Teléfono', 'Asunto', 'Nivel', 'Mensaje', 'Estado', 'Destacado', 'Notas'], ';');
    while ($r = $st->fetch()) {
        fputcsv($out, [
            $r['id_mensaje'], date('d/m/Y H:i', strtotime($r['creado_en'])), $r['nombre'], $r['email'], $r['telefono'],
            $r['asunto'], $r['nivel'], $r['mensaje'], $r['estado'], $r['destacado'] ? 'Sí' : '', (string) $r['notas'],
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
$otros   = [];
if ($ver) {
    $st = $pdo->prepare('SELECT m.*, u.nombre AS u_nombre, u.apellido AS u_apellido
                           FROM mensajes_contacto m LEFT JOIN usuarios u ON u.id_usuario = m.id_usuario
                          WHERE m.id_mensaje = ?');
    $st->execute([$ver]);
    $detalle = $st->fetch() ?: null;
    if ($detalle) {
        // Abrirlo lo marca como leído
        if ($detalle['estado'] === 'Nuevo') {
            $pdo->prepare("UPDATE mensajes_contacto SET estado = 'Leído', id_usuario = ? WHERE id_mensaje = ?")->execute([$yo, $ver]);
            $detalle['estado'] = 'Leído';
        }
        // Otros mensajes de la misma persona
        $st = $pdo->prepare('SELECT id_mensaje, asunto, estado, creado_en FROM mensajes_contacto WHERE email = ? AND id_mensaje <> ? ORDER BY creado_en DESC LIMIT 10');
        $st->execute([$detalle['email'], $ver]);
        $otros = $st->fetchAll();
    }
}

// Conteos para la barra lateral (sobre todo, sin filtros)
$cnt_carpeta = [];
foreach (MSJ_CARPETAS as $k => [, $cond]) {
    $cnt_carpeta[$k] = (int) $pdo->query("SELECT COUNT(*) FROM mensajes_contacto WHERE $cond")->fetchColumn();
}
$cond_carpeta = MSJ_CARPETAS[$f_carpeta][1];
$cnt_asunto = $pdo->query("SELECT asunto, COUNT(*) total, SUM(estado = 'Nuevo') nuevos FROM mensajes_contacto WHERE $cond_carpeta GROUP BY asunto")->fetchAll(PDO::FETCH_UNIQUE);
$cnt_nivel  = $pdo->query("SELECT nivel, COUNT(*) total, SUM(estado = 'Nuevo') nuevos FROM mensajes_contacto WHERE $cond_carpeta GROUP BY nivel")->fetchAll(PDO::FETCH_UNIQUE);

$lista = [];
if (!$detalle) {
    $st = $pdo->prepare("SELECT *, " . MSJ_SQL_ANTIGUEDAD . " FROM mensajes_contacto $sql_where ORDER BY $sql_orden LIMIT 500");
    $st->execute($args);
    $lista = $st->fetchAll();
}

$qs_actual = ltrim(qs(), '?');
$hay_filtros = $f_asunto || $f_nivel || $f_q !== '';

$panel_page_title = 'Mensajes de contacto';
$panel_heading    = 'Mensajes de <span>contacto</span>';
$panel_sub        = 'Lo que escriben las familias desde la página de Contacto, clasificado por asunto, nivel y estado.';
require __DIR__ . '/panel_header.php';

$ico_estrella = '<svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';
?>

<div class="panel-toolbar">
  <a href="<?= $detalle ? 'gestion_mensajes' . e(qs()) : 'panel' ?>" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    <?= $detalle ? 'Volver a los mensajes' : 'Volver al panel' ?>
  </a>
  <a href="contacto" target="_blank" rel="noopener" class="msj-link-sitio">Ver el formulario en el sitio ↗</a>
</div>

<!-- Formulario compartido por las estrellas (evita formularios anidados) -->
<form method="post" id="f-destacar" hidden>
  <?= csrf_input() ?>
  <input type="hidden" name="accion" value="destacar"/>
  <input type="hidden" name="qs" value="<?= e($qs_actual) ?>"/>
  <?php if ($detalle): ?><input type="hidden" name="desde_detalle" value="1"/><?php endif; ?>
</form>

<?php if ($detalle):
  // ========================================================
  //  DETALLE DE UN MENSAJE
  // ========================================================
  $d = $detalle;
  $asunto_mail = 'Re: ' . $d['asunto'] . ' — ' . cfg('nombre_colegio');
  $cuerpo_mail = "Hola, {$d['nombre']}:\n\n\n\n---\nEl " . date('d/m/Y \a \l\a\s H:i', strtotime($d['creado_en'])) . " escribiste:\n> "
               . str_replace("\n", "\n> ", $d['mensaje']);
  $mailto = 'mailto:' . rawurlencode($d['email']) . '?subject=' . rawurlencode($asunto_mail) . '&body=' . rawurlencode($cuerpo_mail);
?>
  <div class="msj-detalle">
    <article class="msj-det-main">
      <header class="msj-det-head">
        <div>
          <div class="msj-chips">
            <span class="msj-asunto" style="--c:<?= MSJ_COLOR_ASUNTO[$d['asunto']] ?>"><?= e($d['asunto']) ?></span>
            <?php if ($d['nivel'] !== 'General'): ?><span class="msj-nivel">Nivel <?= e($d['nivel']) ?></span><?php endif; ?>
            <span class="msj-estado" style="--c:<?= MSJ_COLOR_ESTADO[$d['estado']] ?>"><?= e($d['estado']) ?></span>
          </div>
          <h2><?= e($d['nombre']) ?></h2>
          <p class="msj-meta">
            Recibido el <?= e(date('d/m/Y \a \l\a\s H:i', strtotime($d['creado_en']))) ?>
            <?php if ($d['u_nombre']): ?>· Última gestión: <?= e($d['u_nombre'] . ' ' . $d['u_apellido']) ?><?php endif; ?>
          </p>
        </div>
        <button type="submit" form="f-destacar" name="id" value="<?= (int) $d['id_mensaje'] ?>"
                class="msj-estrella<?= $d['destacado'] ? ' on' : '' ?>"
                title="<?= $d['destacado'] ? 'Quitar de destacados' : 'Destacar' ?>" aria-label="<?= $d['destacado'] ? 'Quitar de destacados' : 'Destacar' ?>"
                aria-pressed="<?= $d['destacado'] ? 'true' : 'false' ?>"><?= $ico_estrella ?></button>
      </header>

      <div class="msj-cuerpo"><?= nl2br(e($d['mensaje'])) ?></div>

      <div class="msj-acciones">
        <a href="<?= e($mailto) ?>" class="btn btn-primary">
          Responder por correo
        </a>
        <form method="post">
          <?= csrf_input() ?>
          <input type="hidden" name="accion" value="estado_rapido"/>
          <input type="hidden" name="id" value="<?= (int) $d['id_mensaje'] ?>"/>
          <?php if ($d['estado'] !== 'Respondido'): ?>
            <button type="submit" name="estado" value="Respondido" class="btn btn-secundario">Marcar como respondido</button>
          <?php endif; ?>
          <?php if ($d['estado'] !== 'Archivado'): ?>
            <button type="submit" name="estado" value="Archivado" class="btn btn-secundario">Archivar</button>
          <?php else: ?>
            <button type="submit" name="estado" value="Leído" class="btn btn-secundario">Volver a la bandeja</button>
          <?php endif; ?>
          <button type="submit" name="estado" value="Nuevo" class="btn btn-secundario">Marcar como no leído</button>
        </form>
      </div>
      <p class="msj-ayuda">«Responder por correo» abre tu programa de correo con la respuesta armada. Cuando la envíes, marcá el mensaje como respondido.</p>

      <?php if ($otros): ?>
        <section class="msj-otros">
          <h3>Otros mensajes de esta persona</h3>
          <ul>
            <?php foreach ($otros as $o): ?>
              <li>
                <a href="gestion_mensajes?ver=<?= (int) $o['id_mensaje'] ?>"><?= e($o['asunto']) ?></a>
                <span><?= e(date('d/m/Y', strtotime($o['creado_en']))) ?></span>
                <span class="msj-estado" style="--c:<?= MSJ_COLOR_ESTADO[$o['estado']] ?>"><?= e($o['estado']) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        </section>
      <?php endif; ?>
    </article>

    <aside class="msj-det-side">
      <div class="msj-card">
        <h3>Datos de contacto</h3>
        <p><a href="mailto:<?= e($d['email']) ?>"><?= e($d['email']) ?></a></p>
        <?php if ($d['telefono'] !== ''):
          $tel = preg_replace('/[^0-9+]/', '', $d['telefono']); ?>
          <p><a href="tel:<?= e($tel) ?>"><?= e($d['telefono']) ?></a></p>
        <?php endif; ?>
      </div>

      <form method="post" class="msj-card">
        <?= csrf_input() ?>
        <input type="hidden" name="accion" value="guardar"/>
        <input type="hidden" name="id" value="<?= (int) $d['id_mensaje'] ?>"/>
        <h3>Clasificación y seguimiento</h3>
        <div class="field">
          <label for="m-estado">Estado</label>
          <select id="m-estado" name="estado">
            <?php foreach (MSJ_ESTADOS as $op): ?><option<?= $op === $d['estado'] ? ' selected' : '' ?>><?= e($op) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label for="m-asunto">Asunto</label>
          <select id="m-asunto" name="asunto">
            <?php foreach (MSJ_ASUNTOS as $op): ?><option<?= $op === $d['asunto'] ? ' selected' : '' ?>><?= e($op) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label for="m-nivel">Nivel</label>
          <select id="m-nivel" name="nivel">
            <?php foreach (MSJ_NIVELES as $op): ?><option<?= $op === $d['nivel'] ? ' selected' : '' ?>><?= e($op) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label for="m-notas">Notas internas <span class="hint">(no las ve quien escribió)</span></label>
          <textarea id="m-notas" name="notas" rows="5"><?= e((string) $d['notas']) ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;">Guardar</button>
      </form>

      <?php if (es_admin()): ?>
        <form method="post" onsubmit="return confirm('¿Eliminar este mensaje? No se puede deshacer.');">
          <?= csrf_input() ?>
          <input type="hidden" name="accion" value="eliminar"/>
          <input type="hidden" name="id" value="<?= (int) $d['id_mensaje'] ?>"/>
          <button type="submit" class="msj-eliminar">Eliminar mensaje</button>
        </form>
      <?php endif; ?>
    </aside>
  </div>

<?php elseif ($ver): ?>
  <div class="alert alert-error">El mensaje no existe o fue eliminado.</div>

<?php else:
  // ========================================================
  //  LISTADO
  // ========================================================
?>
  <div class="msj-layout">

    <nav class="msj-side" aria-label="Clasificación de mensajes">
      <p class="msj-side-tit">Carpetas</p>
      <?php foreach (MSJ_CARPETAS as $k => [$nombre]): ?>
        <a href="gestion_mensajes<?= e(qs(['carpeta' => $k, 'asunto' => '', 'nivel' => ''])) ?>"
           class="msj-nodo<?= $f_carpeta === $k && !$f_asunto && !$f_nivel ? ' act' : ($f_carpeta === $k ? ' act-suave' : '') ?>">
          <?= e($nombre) ?>
          <span class="msj-cnt<?= $k === 'nuevos' && $cnt_carpeta[$k] ? ' hay-nuevos' : '' ?>"><?= $cnt_carpeta[$k] ?></span>
        </a>
      <?php endforeach; ?>

      <p class="msj-side-tit">Por asunto</p>
      <?php foreach (MSJ_ASUNTOS as $a):
        $c = $cnt_asunto[$a] ?? ['total' => 0, 'nuevos' => 0]; ?>
        <a href="gestion_mensajes<?= e(qs(['asunto' => $f_asunto === $a ? '' : $a])) ?>"
           class="msj-nodo msj-nodo-color<?= $f_asunto === $a ? ' act' : '' ?><?= $c['total'] ? '' : ' vacio' ?>" style="--c:<?= MSJ_COLOR_ASUNTO[$a] ?>">
          <?= e($a) ?>
          <span class="msj-cnt<?= $c['nuevos'] ? ' hay-nuevos' : '' ?>" title="<?= (int) $c['nuevos'] ?> sin leer"><?= (int) $c['total'] ?></span>
        </a>
      <?php endforeach; ?>

      <p class="msj-side-tit">Por nivel</p>
      <?php foreach (MSJ_NIVELES as $n):
        $c = $cnt_nivel[$n] ?? ['total' => 0, 'nuevos' => 0]; ?>
        <a href="gestion_mensajes<?= e(qs(['nivel' => $f_nivel === $n ? '' : $n])) ?>"
           class="msj-nodo<?= $f_nivel === $n ? ' act' : '' ?><?= $c['total'] ? '' : ' vacio' ?>">
          <?= e($n) ?>
          <span class="msj-cnt<?= $c['nuevos'] ? ' hay-nuevos' : '' ?>" title="<?= (int) $c['nuevos'] ?> sin leer"><?= (int) $c['total'] ?></span>
        </a>
      <?php endforeach; ?>
      <p class="msj-side-ley"><span class="msj-cnt hay-nuevos">n</span> hay mensajes sin leer</p>
    </nav>

    <div class="msj-contenido">
      <form method="get" class="msj-filtros">
        <?php if ($f_carpeta !== 'entrada'): ?><input type="hidden" name="carpeta" value="<?= e($f_carpeta) ?>"/><?php endif; ?>
        <?php if ($f_asunto): ?><input type="hidden" name="asunto" value="<?= e($f_asunto) ?>"/><?php endif; ?>
        <?php if ($f_nivel): ?><input type="hidden" name="nivel" value="<?= e($f_nivel) ?>"/><?php endif; ?>
        <input type="search" name="q" value="<?= e($f_q) ?>" placeholder="Buscar por nombre, correo, teléfono o texto…" aria-label="Buscar"/>
        <select name="orden" aria-label="Orden">
          <option value="recientes">Más recientes primero</option>
          <option value="antiguos"<?= $f_orden === 'antiguos' ? ' selected' : '' ?>>Más antiguos primero</option>
        </select>
        <button type="submit" class="btn btn-primary">Buscar</button>
        <a href="gestion_mensajes<?= e(qs(['exportar' => 1])) ?>" class="btn btn-secundario" title="Descarga lo que estás viendo">Exportar CSV</a>
      </form>

      <p class="msj-resumen">
        <b><?= e(MSJ_CARPETAS[$f_carpeta][0]) ?></b>
        <?php if ($f_asunto): ?>› <?= e($f_asunto) ?><?php endif; ?>
        <?php if ($f_nivel): ?>› Nivel <?= e($f_nivel) ?><?php endif; ?>
        · <?= count($lista) ?> mensaje<?= count($lista) === 1 ? '' : 's' ?>
        <?php if ($hay_filtros): ?>· <a href="gestion_mensajes<?= e(qs(['asunto' => '', 'nivel' => '', 'q' => ''])) ?>">Quitar filtros</a><?php endif; ?>
      </p>

      <?php if (!$lista): ?>
        <div class="empty-state msj-vacio">
          <svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 5L2 7"/></svg>
          <p><?= $cnt_carpeta['todos'] ? 'No hay mensajes en esta vista.' : 'Todavía no llegó ningún mensaje desde la página de Contacto.' ?></p>
        </div>
      <?php else: ?>
        <form method="post" id="f-masivo">
          <?= csrf_input() ?>
          <input type="hidden" name="accion" value="masivo"/>
          <input type="hidden" name="qs" value="<?= e($qs_actual) ?>"/>
          <div class="msj-bulk">
            <label class="msj-check-todos"><input type="checkbox" id="check-todos"/> Seleccionar todos</label>
            <span class="msj-bulk-n" id="bulk-n"></span>
            <select name="op" aria-label="Acción para los seleccionados">
              <option value="">Acción con los seleccionados…</option>
              <option value="leido">Marcar como leídos</option>
              <option value="nuevo">Marcar como no leídos</option>
              <option value="respondido">Marcar como respondidos</option>
              <?php if ($f_carpeta === 'archivados'): ?>
                <option value="desarchivar">Volver a la bandeja</option>
              <?php else: ?>
                <option value="archivar">Archivar</option>
              <?php endif; ?>
              <?php if (es_admin()): ?><option value="eliminar">Eliminar</option><?php endif; ?>
            </select>
            <button type="submit" class="btn btn-secundario">Aplicar</button>
          </div>
        </form>

        <ul class="msj-lista">
          <?php foreach ($lista as $r):
            $nuevo = $r['estado'] === 'Nuevo'; ?>
            <li class="msj-item<?= $nuevo ? ' is-nuevo' : '' ?>">
              <input type="checkbox" name="ids[]" value="<?= (int) $r['id_mensaje'] ?>" form="f-masivo" class="msj-check" aria-label="Seleccionar mensaje de <?= e($r['nombre']) ?>"/>
              <button type="submit" form="f-destacar" name="id" value="<?= (int) $r['id_mensaje'] ?>"
                      class="msj-estrella<?= $r['destacado'] ? ' on' : '' ?>"
                      title="<?= $r['destacado'] ? 'Quitar de destacados' : 'Destacar' ?>" aria-label="<?= $r['destacado'] ? 'Quitar de destacados' : 'Destacar' ?>"
                      aria-pressed="<?= $r['destacado'] ? 'true' : 'false' ?>"><?= $ico_estrella ?></button>
              <a href="gestion_mensajes?ver=<?= (int) $r['id_mensaje'] ?>" class="msj-item-link">
                <span class="msj-item-quien">
                  <b><?= e($r['nombre']) ?></b>
                  <small><?= e($r['email']) ?></small>
                </span>
                <span class="msj-item-texto">
                  <span class="msj-chips">
                    <span class="msj-asunto" style="--c:<?= MSJ_COLOR_ASUNTO[$r['asunto']] ?>"><?= e($r['asunto']) ?></span>
                    <?php if ($r['nivel'] !== 'General'): ?><span class="msj-nivel"><?= e($r['nivel']) ?></span><?php endif; ?>
                    <?php if ($r['notas']): ?><span class="msj-nivel" title="Tiene notas internas">📝 Nota</span><?php endif; ?>
                  </span>
                  <span class="msj-extracto"><?= e(mb_strimwidth(preg_replace('/\s+/', ' ', $r['mensaje']), 0, 140, '…')) ?></span>
                </span>
                <span class="msj-item-der">
                  <time datetime="<?= e(date('c', strtotime($r['creado_en']))) ?>" title="<?= e(date('d/m/Y H:i', strtotime($r['creado_en']))) ?>"><?= e(msj_fecha_corta($r)) ?></time>
                  <span class="msj-estado" style="--c:<?= MSJ_COLOR_ESTADO[$r['estado']] ?>"><?= e($r['estado']) ?></span>
                </span>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
        <?php if (count($lista) === 500): ?>
          <p class="msj-resumen" style="margin-top:.8rem;">Se muestran los primeros 500. Usá los filtros o el buscador para acotar.</p>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <script>
    (function () {
      var todos = document.getElementById('check-todos');
      var checks = document.querySelectorAll('.msj-check');
      var n = document.getElementById('bulk-n');
      var form = document.getElementById('f-masivo');
      if (!form) return;
      function contar() {
        var sel = document.querySelectorAll('.msj-check:checked').length;
        n.textContent = sel ? sel + ' seleccionado' + (sel === 1 ? '' : 's') : '';
        todos.checked = sel > 0 && sel === checks.length;
        todos.indeterminate = sel > 0 && sel < checks.length;
      }
      todos.addEventListener('change', function () {
        checks.forEach(function (c) { c.checked = todos.checked; });
        contar();
      });
      checks.forEach(function (c) { c.addEventListener('change', contar); });
      form.addEventListener('submit', function (ev) {
        var op = form.op.value;
        if (!op) { ev.preventDefault(); alert('Elegí qué hacer con los mensajes seleccionados.'); return; }
        if (op === 'eliminar' && !confirm('¿Eliminar los mensajes seleccionados? No se puede deshacer.')) ev.preventDefault();
      });
    })();
  </script>
<?php endif; ?>

<style>
  .msj-link-sitio { font-size: .84rem; font-weight: 700; color: var(--blue-mid); }

  /* Chips */
  .msj-chips { display: inline-flex; flex-wrap: wrap; gap: .35rem; align-items: center; }
  .msj-asunto, .msj-estado {
    display: inline-block; font-size: .7rem; font-weight: 800; white-space: nowrap;
    color: var(--c); background: color-mix(in srgb, var(--c) 13%, #fff); padding: .15rem .55rem; border-radius: 999px;
  }
  .msj-nivel { font-size: .7rem; font-weight: 700; color: #556; background: var(--gray-200); padding: .15rem .5rem; border-radius: 999px; white-space: nowrap; }

  /* Estrella */
  .msj-estrella { background: none; border: none; cursor: pointer; padding: .25rem; border-radius: 8px; line-height: 0; color: #c3c8ce; flex-shrink: 0; }
  .msj-estrella svg { width: 19px; height: 19px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linejoin: round; }
  .msj-estrella:hover { color: #e09f3e; background: var(--gray-100); }
  .msj-estrella.on { color: #e09f3e; }
  .msj-estrella.on svg { fill: currentColor; }

  /* Layout listado */
  .msj-layout { display: grid; grid-template-columns: 230px minmax(0, 1fr); gap: 1.4rem; align-items: start; }
  .msj-side { background: #fff; border-radius: 16px; box-shadow: var(--shadow-sm); padding: .6rem .7rem .7rem; position: sticky; top: 1rem; }
  .msj-side-tit { font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: #8a9099; padding: .8rem .6rem .3rem; }
  .msj-side-tit:first-child { padding-top: .4rem; }
  .msj-nodo {
    display: flex; align-items: center; justify-content: space-between; gap: .5rem;
    padding: .42rem .6rem; border-radius: 8px; font-size: .87rem; font-weight: 600; color: var(--blue-dark); transition: background .15s;
  }
  .msj-nodo:hover { background: var(--gray-100); }
  .msj-nodo.act { background: var(--blue-dark); color: #fff; }
  .msj-nodo.act-suave { background: #e8edf3; }
  .msj-nodo-color { border-left: 3px solid var(--c); border-radius: 0 8px 8px 0; }
  .msj-nodo.vacio { color: #9aa0a6; }
  .msj-cnt { font-size: .72rem; font-weight: 800; min-width: 24px; text-align: center; padding: .05rem .4rem; border-radius: 999px; background: var(--gray-200); color: var(--blue-dark); }
  .msj-cnt.hay-nuevos { background: var(--red); color: #fff; }
  .msj-nodo.act .msj-cnt:not(.hay-nuevos) { background: rgba(255,255,255,.2); color: #fff; }
  .msj-side-ley { font-size: .72rem; color: #8a9099; padding: .6rem .6rem .1rem; border-top: 1px solid #eef0f3; margin-top: .6rem; display: flex; align-items: center; gap: .4rem; }

  .msj-filtros { display: flex; gap: .6rem; flex-wrap: wrap; margin-bottom: .8rem; }
  .msj-filtros input[type=search], .msj-filtros select, .msj-bulk select {
    font-family: var(--font-body); font-size: .88rem; padding: .6rem .8rem; border: 1.5px solid #dfe3e8; border-radius: 10px; background: #fff;
  }
  .msj-filtros input[type=search] { flex: 1 1 220px; }
  .msj-filtros .btn, .msj-bulk .btn { padding: .6rem 1.1rem; font-size: .85rem; }
  .msj-resumen { font-size: .85rem; color: #667; margin-bottom: .8rem; }
  .msj-resumen b { color: var(--blue-dark); }
  .msj-resumen a { color: var(--blue-mid); font-weight: 700; }
  .msj-vacio { background: #fff; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); }

  .msj-bulk {
    display: flex; align-items: center; gap: .6rem; flex-wrap: wrap;
    background: #fff; border-radius: 14px 14px 0 0; padding: .6rem .9rem; border-bottom: 1px solid #eef0f3; box-shadow: var(--shadow-sm);
  }
  .msj-check-todos { display: inline-flex; align-items: center; gap: .45rem; font-size: .84rem; font-weight: 700; color: var(--blue-dark); cursor: pointer; }
  .msj-bulk-n { font-size: .8rem; color: #667; margin-right: auto; }
  .msj-bulk select { padding: .45rem .7rem; font-size: .84rem; }
  .msj-bulk .btn { padding: .45rem .9rem; }

  .msj-lista { list-style: none; margin: 0; padding: 0; background: #fff; border-radius: 0 0 14px 14px; box-shadow: var(--shadow-sm); overflow: hidden; }
  .msj-item { display: flex; align-items: center; gap: .35rem; padding: 0 .9rem; border-bottom: 1px solid #eef0f3; transition: background .15s; }
  .msj-item:last-child { border-bottom: none; }
  .msj-item:hover { background: var(--gray-100); }
  .msj-item.is-nuevo { box-shadow: inset 3px 0 0 var(--red); background: #fffafa; }
  .msj-check { width: 16px; height: 16px; flex-shrink: 0; cursor: pointer; accent-color: var(--blue-dark); }
  .msj-item-link { flex: 1; min-width: 0; display: grid; grid-template-columns: 190px minmax(0, 1fr) auto; gap: 1rem; align-items: center; padding: .8rem 0 .8rem .3rem; color: inherit; }
  .msj-item-quien { min-width: 0; line-height: 1.25; }
  .msj-item-quien b { display: block; color: var(--blue-dark); font-weight: 600; font-size: .9rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .msj-item.is-nuevo .msj-item-quien b { font-weight: 800; }
  .msj-item-quien small { display: block; color: #8a9099; font-size: .75rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .msj-item-texto { min-width: 0; display: flex; flex-direction: column; gap: .25rem; }
  .msj-extracto { font-size: .84rem; color: #667; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .msj-item.is-nuevo .msj-extracto { color: #334; }
  .msj-item-der { display: flex; flex-direction: column; align-items: flex-end; gap: .3rem; }
  .msj-item-der time { font-size: .76rem; color: #8a9099; white-space: nowrap; }
  .msj-item.is-nuevo .msj-item-der time { color: var(--blue-dark); font-weight: 800; }

  /* Detalle */
  .msj-detalle { display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 1.4rem; align-items: start; }
  .msj-det-main { background: #fff; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); padding: 1.8rem; }
  .msj-det-head { display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; padding-bottom: 1.1rem; margin-bottom: 1.3rem; border-bottom: 1px solid #eef0f3; }
  .msj-det-head h2 { font-family: var(--font-display); font-size: 1.6rem; color: var(--blue-dark); line-height: 1.2; margin: .5rem 0 .25rem; overflow-wrap: anywhere; }
  .msj-det-head .msj-estrella svg { width: 24px; height: 24px; }
  .msj-meta { font-size: .82rem; color: #778; }
  .msj-cuerpo { font-size: 1rem; line-height: 1.7; color: var(--text); white-space: normal; overflow-wrap: anywhere; background: var(--gray-100); border-radius: 12px; padding: 1.1rem 1.3rem; }
  .msj-acciones { display: flex; flex-wrap: wrap; gap: .6rem; margin-top: 1.4rem; align-items: center; }
  .msj-acciones form { display: contents; }
  .msj-acciones .btn { padding: .6rem 1.1rem; font-size: .86rem; }
  .msj-ayuda { font-size: .76rem; color: #8a9099; margin-top: .6rem; }
  .msj-otros { margin-top: 1.6rem; padding-top: 1.2rem; border-top: 1px solid #eef0f3; }
  .msj-otros h3 { font-size: .8rem; text-transform: uppercase; letter-spacing: .05em; color: var(--blue-mid); margin-bottom: .6rem; }
  .msj-otros ul { list-style: none; margin: 0; padding: 0; }
  .msj-otros li { display: flex; gap: .7rem; align-items: center; padding: .4rem 0; border-bottom: 1px dashed #eceff3; font-size: .86rem; }
  .msj-otros li a { font-weight: 700; color: var(--blue-dark); margin-right: auto; }
  .msj-otros li span { color: #8a9099; font-size: .8rem; }
  .msj-det-side { display: flex; flex-direction: column; gap: 1rem; position: sticky; top: 1rem; }
  .msj-card { background: #fff; border-radius: 16px; box-shadow: var(--shadow-sm); padding: 1.2rem; }
  .msj-card h3 { font-family: var(--font-display); font-size: 1.05rem; color: var(--blue-dark); margin-bottom: .7rem; }
  .msj-card p { font-size: .88rem; margin-bottom: .2rem; overflow-wrap: anywhere; }
  .msj-card p a { color: var(--blue-mid); font-weight: 700; }
  .msj-card .field { margin-bottom: .85rem; }
  .msj-card textarea { min-height: 100px; }
  .msj-eliminar { width: 100%; background: none; border: 1.5px solid rgba(193,18,31,.35); color: #c1121f; font-weight: 700; font-family: var(--font-body); padding: .6rem; border-radius: 10px; cursor: pointer; }
  .msj-eliminar:hover { background: #fdecee; }

  @media (max-width: 860px) {
    .msj-layout, .msj-detalle { grid-template-columns: 1fr; }
    .msj-side, .msj-det-side { position: static; }
    .msj-item-link { grid-template-columns: minmax(0, 1fr) auto; }
    .msj-item-texto { grid-column: 1 / -1; grid-row: 2; }
  }
</style>

  </div>
</body>
</html>
