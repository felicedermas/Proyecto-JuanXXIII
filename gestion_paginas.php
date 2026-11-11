<?php
// ============================================================
//  gestion_paginas.php
//  Panel de Control — Páginas del sitio
//
//  Lista las páginas que el usuario puede editar y lo lleva a cada
//  una en modo edición (la edición se hace sobre la página misma,
//  ver partials/paginas.php). Desde acá además se ve qué se cambió
//  y se puede volver una página a sus textos originales.
//
//  Acceso: permiso "Edición de páginas" (cada página pide su categoría).
// ============================================================
require_once __DIR__ . '/panel_config.php';
require_once __DIR__ . '/partials/paginas.php';
exigir_login();

$pdo = db();
if (!pag_asegurar_tablas($pdo)) {
    die('<p style="font-family:sans-serif;padding:2rem;color:#c1121f;">No se pudieron crear las tablas de páginas. Ejecutá <b>sql/migracion_paginas.sql</b> en phpMyAdmin.</p>');
}

$paginas = pag_paginas_permitidas();
if (!$paginas) {
    http_response_code(403);
    die('<p style="font-family:sans-serif;padding:2rem;color:#c1121f;">Acceso denegado: tu nivel de permisos no incluye la edición de páginas.</p>'
      . '<p style="font-family:sans-serif;padding:0 2rem;"><a href="panel">Volver al panel</a></p>');
}

// ── Volver una página a sus textos originales ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $slug = (string) ($_POST['pagina'] ?? '');
    if (($_POST['accion'] ?? '') === 'restaurar_todo' && isset($paginas[$slug])) {
        $st = $pdo->prepare('DELETE FROM pagina_textos WHERE pagina = ?');
        $st->execute([$slug]);
        flash('ok', '«' . $paginas[$slug]['titulo'] . '»: ' . ($st->rowCount()
            ? 'los textos volvieron a su versión original.' : 'no había textos cambiados.'));
    }
    header('Location: gestion_paginas' . (isset($paginas[$slug]) ? '?ver=' . rawurlencode($slug) : ''));
    exit;
}

// ── Conteos ──
$n_textos  = $pdo->query('SELECT pagina, COUNT(*) FROM pagina_textos GROUP BY pagina')->fetchAll(PDO::FETCH_KEY_PAIR);
$n_bloques = $pdo->query('SELECT pagina, COUNT(*) FROM pagina_bloques GROUP BY pagina')->fetchAll(PDO::FETCH_KEY_PAIR);

// ── Detalle de una página ──
$ver = is_string($_GET['ver'] ?? null) && isset($paginas[$_GET['ver']]) ? $_GET['ver'] : '';
$det_textos = $det_bloques = [];
if ($ver !== '') {
    $st = $pdo->prepare('SELECT t.*, u.nombre AS u_nombre, u.apellido AS u_apellido
                           FROM pagina_textos t LEFT JOIN usuarios u ON u.id_usuario = t.id_usuario
                          WHERE t.pagina = ? ORDER BY t.actualizado DESC');
    $st->execute([$ver]);
    $det_textos = $st->fetchAll();
    $st = $pdo->prepare('SELECT b.*, (b.hasta IS NOT NULL AND b.hasta < CURDATE()) AS vencido, u.nombre AS u_nombre, u.apellido AS u_apellido
                           FROM pagina_bloques b LEFT JOIN usuarios u ON u.id_usuario = b.id_usuario
                          WHERE b.pagina = ? ORDER BY b.seccion, b.orden, b.id_bloque');
    $st->execute([$ver]);
    $det_bloques = $st->fetchAll();
}

$grupos = [];
foreach ($paginas as $slug => $p) $grupos[$p['grupo']][$slug] = $p;

$panel_page_title = 'Páginas del sitio';
$panel_heading    = 'Páginas del <span>sitio</span>';
$panel_sub        = 'Editá los textos sobre la página misma y agregá mensajes, documentos PDF e información a cada sección.';
require __DIR__ . '/panel_header.php';
?>

<div class="panel-toolbar">
  <a href="<?= $ver !== '' ? 'gestion_paginas' : 'panel' ?>" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    <?= $ver !== '' ? 'Volver a las páginas' : 'Volver al panel' ?>
  </a>
</div>

<?php if ($ver === ''): ?>

  <div class="pag-como">
    <h2>Cómo se edita</h2>
    <ol>
      <li><b>Abrí la página</b> con «Editar página». Abajo aparece una barra oscura: tocá <b>Editar esta página</b>.</li>
      <li><b>Hacé clic en cualquier texto</b> recuadrado y escribí encima. Con Enter bajás de línea.</li>
      <li>Al final de cada sección está <b>＋ Agregar mensaje, PDF o información</b>.</li>
      <li>Tocá <b>Guardar</b>. Los cambios se ven en el sitio al instante.</li>
    </ol>
    <p>Mientras tengas la sesión abierta, la barra aparece sola cada vez que entrás a una página que podés editar.</p>
  </div>

  <?php foreach ($grupos as $grupo => $lista): ?>
    <h2 class="pag-grupo"><?= e($grupo) ?></h2>
    <div class="pag-grid">
      <?php foreach ($lista as $slug => $p):
        $nt = (int) ($n_textos[$slug] ?? 0);
        $nb = (int) ($n_bloques[$slug] ?? 0); ?>
        <div class="pag-card">
          <h3><?= e($p['titulo']) ?></h3>
          <p class="pag-cnt">
            <?= $nt ?> texto<?= $nt === 1 ? '' : 's' ?> cambiado<?= $nt === 1 ? '' : 's' ?>
            <?php if (($p['bloques'] ?? true) !== false): ?> · <?= $nb ?> agregado<?= $nb === 1 ? '' : 's' ?><?php endif; ?>
          </p>
          <div class="pag-card-acc">
            <a href="<?= e($slug) ?>?editar=1" class="btn btn-primary">Editar página</a>
            <?php if ($nt || $nb): ?><a href="gestion_paginas?ver=<?= e($slug) ?>" class="btn btn-secundario">Ver cambios</a><?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>

<?php else:
  $p = $paginas[$ver]; ?>

  <div class="pag-det-head">
    <div>
      <h2><?= e($p['titulo']) ?></h2>
      <p><?= count($det_textos) ?> texto<?= count($det_textos) === 1 ? '' : 's' ?> cambiado<?= count($det_textos) === 1 ? '' : 's' ?>
         · <?= count($det_bloques) ?> contenido<?= count($det_bloques) === 1 ? '' : 's' ?> agregado<?= count($det_bloques) === 1 ? '' : 's' ?></p>
    </div>
    <a href="<?= e($ver) ?>?editar=1" class="btn btn-primary">Editar página</a>
  </div>

  <h3 class="pag-sub">Contenido agregado</h3>
  <?php if (!$det_bloques): ?>
    <p class="pag-nada">No hay mensajes, documentos ni información agregados en esta página.</p>
  <?php else: ?>
    <div style="overflow-x:auto;">
    <table class="mng-table">
      <thead><tr><th>Tipo</th><th>Título / texto</th><th>Sección</th><th>Estado</th><th>Última edición</th></tr></thead>
      <tbody>
      <?php foreach ($det_bloques as $b):
        $estado = (int) $b['visible'] !== 1 ? 'Oculto' : ((int) $b['vencido'] === 1 ? 'Vencido' : 'Visible'); ?>
        <tr>
          <td><span class="mng-badge" style="background:<?= ['mensaje' => '#457B9D', 'pdf' => '#c1121f', 'info' => '#1D3557'][$b['tipo']] ?>;"><?= e(PAG_TIPOS_BLOQUE[$b['tipo']]) ?></span></td>
          <td>
            <b><?= e($b['titulo'] !== '' ? $b['titulo'] : mb_strimwidth((string) $b['texto'], 0, 70, '…')) ?></b>
            <?php if ($b['tipo'] === 'pdf'): ?>
              <small class="pag-chico"><a href="<?= e($b['archivo']) ?>" target="_blank" rel="noopener"><?= e($b['archivo_nombre']) ?></a> · <?= e(pag_peso_legible((int) $b['archivo_peso'])) ?></small>
            <?php elseif ($b['titulo'] !== ''): ?>
              <small class="pag-chico"><?= e(mb_strimwidth((string) $b['texto'], 0, 110, '…')) ?></small>
            <?php endif; ?>
          </td>
          <td><?= e($b['seccion'] === PAG_SECCION_TOPE ? 'Comienzo de la página' : $b['seccion']) ?></td>
          <td>
            <?= e($estado) ?>
            <?php if ($b['hasta']): ?><small class="pag-chico">hasta el <?= e(date('d/m/Y', strtotime($b['hasta']))) ?></small><?php endif; ?>
          </td>
          <td style="white-space:nowrap;color:#667;">
            <?= e(date('d/m/Y', strtotime($b['actualizado']))) ?>
            <?php if ($b['u_nombre']): ?><small class="pag-chico"><?= e($b['u_nombre'] . ' ' . $b['u_apellido']) ?></small><?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <p class="pag-nada">Para modificarlos, ocultarlos o eliminarlos, entrá con «Editar página».</p>
  <?php endif; ?>

  <h3 class="pag-sub">Textos cambiados</h3>
  <?php if (!$det_textos): ?>
    <p class="pag-nada">Todos los textos de esta página están como en el original.</p>
  <?php else: ?>
    <div style="overflow-x:auto;">
    <table class="mng-table">
      <thead><tr><th>Decía</th><th>Ahora dice</th><th>Última edición</th></tr></thead>
      <tbody>
      <?php foreach ($det_textos as $t): ?>
        <tr>
          <td class="pag-antes"><?= e(mb_strimwidth($t['original'], 0, 160, '…')) ?></td>
          <td><?= e(mb_strimwidth(pag_extracto($t['contenido'], 400), 0, 160, '…')) ?: '<i>(vacío)</i>' ?></td>
          <td style="white-space:nowrap;color:#667;">
            <?= e(date('d/m/Y H:i', strtotime($t['actualizado']))) ?>
            <?php if ($t['u_nombre']): ?><small class="pag-chico"><?= e($t['u_nombre'] . ' ' . $t['u_apellido']) ?></small><?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>

    <form method="post" class="pag-restaurar"
          onsubmit="return confirm('¿Volver TODOS los textos de «<?= e(addslashes($p['titulo'])) ?>» a su versión original? Los mensajes, PDF e información agregados no se tocan.');">
      <?= csrf_input() ?>
      <input type="hidden" name="accion" value="restaurar_todo"/>
      <input type="hidden" name="pagina" value="<?= e($ver) ?>"/>
      <button type="submit" class="btn btn-secundario">Volver todos los textos al original</button>
      <span>Para volver uno solo: en la página, hacé clic en ese texto y tocá «Texto original».</span>
    </form>
  <?php endif; ?>

<?php endif; ?>

<style>
  .pag-como { background: #fff; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); padding: 1.4rem 1.6rem; margin-bottom: 2rem; border-left: 5px solid var(--blue-mid); }
  .pag-como h2 { font-family: var(--font-display); font-size: 1.15rem; color: var(--blue-dark); margin-bottom: .6rem; }
  .pag-como ol { margin: 0 0 .7rem 1.2rem; font-size: .92rem; color: #445; line-height: 1.75; }
  .pag-como p { font-size: .84rem; color: #667; }
  .pag-grupo { font-family: var(--font-display); font-size: 1.2rem; color: var(--blue-dark); margin: 1.6rem 0 .8rem; }
  .pag-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem; }
  .pag-card { background: #fff; border-radius: 16px; box-shadow: var(--shadow-sm); padding: 1.1rem 1.2rem 1.2rem; border-top: 4px solid var(--red); display: flex; flex-direction: column; gap: .5rem; }
  .pag-card h3 { font-family: var(--font-display); font-size: 1.08rem; color: var(--blue-dark); line-height: 1.25; }
  .pag-cnt { font-size: .82rem; color: #667; flex: 1; }
  .pag-card-acc { display: flex; gap: .5rem; flex-wrap: wrap; }
  .pag-card-acc .btn, .pag-det-head .btn, .pag-restaurar .btn { padding: .5rem 1rem; font-size: .84rem; }
  .pag-det-head { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; background: #fff; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); padding: 1.3rem 1.6rem; }
  .pag-det-head h2 { font-family: var(--font-display); font-size: 1.5rem; color: var(--blue-dark); }
  .pag-det-head p { font-size: .86rem; color: #667; }
  .pag-sub { font-family: var(--font-display); font-size: 1.15rem; color: var(--blue-dark); margin: 2rem 0 .8rem; }
  .pag-nada { font-size: .86rem; color: #667; margin-top: .6rem; }
  .pag-chico { display: block; font-size: .76rem; color: #8a9099; font-weight: 400; }
  .pag-chico a { color: var(--blue-mid); font-weight: 700; }
  .pag-antes { color: #8a9099; text-decoration: line-through; text-decoration-color: rgba(138,144,153,.5); }
  .pag-restaurar { margin-top: 1rem; display: flex; align-items: center; gap: .9rem; flex-wrap: wrap; }
  .pag-restaurar span { font-size: .8rem; color: #8a9099; }
</style>

  </div>
</body>
</html>
