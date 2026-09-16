<?php
// panel_header.php — cabecera común de las páginas internas del panel.
// Requiere que panel_config.php ya esté incluido y el usuario logueado.
// Variables opcionales antes de incluir:
//   $panel_page_title (string)  → <title>
//   $panel_heading    (string)  → título grande (con <span> para el acento en rojo)
//   $panel_sub        (string)  → subtítulo
if (!function_exists('usuario_actual')) { die('panel_config.php no incluido.'); }
$u = usuario_actual();
$ini = strtoupper(mb_substr($u['nombre'], 0, 1) . mb_substr($u['apellido'], 0, 1));
$titulo_head = $panel_page_title ?? 'Panel de Control';
$fl = flash_get();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= e($titulo_head) ?> — Colegio Parroquial Juan XXIII</title>
<?php require __DIR__ . '/partials/favicon.php'; ?>
  <link rel="stylesheet" href="styles.css"/>
  <link rel="stylesheet" href="panel.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet"/>
</head>
<body style="background:var(--gray-100);">
  <div class="panel-wrap">
    <div class="panel-header">
      <div>
        <h1 class="panel-title"><?= $panel_heading ?? 'Panel de <span>Control</span>' ?></h1>
        <?php if (!empty($panel_sub)): ?><p class="panel-subtitle"><?= e($panel_sub) ?></p><?php endif; ?>
      </div>
      <div class="user-chip">
        <div class="avatar"><?= e($ini) ?></div>
        <div class="u-meta">
          <div class="u-name"><?= e($u['nombre'] . ' ' . $u['apellido']) ?></div>
          <div class="u-rol"><?= e(nivel_nombre_actual()) ?></div>
        </div>
        <a href="logout.php" class="logout" title="Cerrar sesión" aria-label="Cerrar sesión">
          <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </a>
      </div>
    </div>

    <?php if ($fl): ?>
      <div class="alert alert-<?= $fl['tipo'] === 'ok' ? 'ok' : ($fl['tipo'] === 'error' ? 'error' : 'info') ?>">
        <?= e($fl['msg']) ?>
      </div>
    <?php endif; ?>
