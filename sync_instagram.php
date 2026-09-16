<?php
// ============================================================
//  sync_instagram.php
//  Colegio Parroquial Juan XXIII
//  Dispara la sincronización de todas las cuentas de Instagram.
//
//  Se puede correr de tres formas:
//
//   1) Tarea programada (lo normal, 19:00 todos los días):
//         php sync_instagram.php
//      o por URL, con la clave que genera el panel:
//         http://localhost/JUAN23CLAUDE/sync_instagram.php?clave=XXXX
//
//   2) A mano desde el panel: botón "Sincronizar ahora"
//      (Panel → Instagram). Requiere sesión de administrador.
//
//   3) Desde la consola, para probar: php sync_instagram.php
//
//  Es idempotente: si un post ya se importó no se repite, así que
//  se puede correr las veces que haga falta.
// ============================================================

declare(strict_types=1);

$es_cli = (PHP_SAPI === 'cli');

require_once __DIR__ . '/ig_sync.php';

// ============================================================
//  CONTROL DE ACCESO
//  Antes este archivo se podía ejecutar desde el navegador sin
//  ningún control: cualquiera podía dispararlo y llenar la base.
// ============================================================
$origen = 'cli';

if (!$es_cli) {
    $clave_config = trim(cfg('ig_clave_tarea', ''));
    $clave_url    = (string) ($_GET['clave'] ?? '');

    $por_clave = ($clave_config !== '' && hash_equals($clave_config, $clave_url));

    if ($por_clave) {
        $origen = 'tarea';
    } else {
        // Sin clave válida exigimos sesión de administrador
        require_once __DIR__ . '/panel_config.php';
        if (!esta_logueado() || !es_admin()) {
            http_response_code(403);
            header('Content-Type: text/html; charset=utf-8');
            exit('<p style="font-family:sans-serif;padding:2rem;color:#c1121f;">'
               . 'Acceso denegado. Entrá al panel como administrador y usá el botón '
               . '«Sincronizar ahora», o llamá a esta página con su clave de tarea programada.</p>');
        }
        $origen = 'manual';
    }
}

// ============================================================
//  CORRIDA
// ============================================================
@set_time_limit(300);
$log = ig_sincronizar_todo($origen);

// ── Salida por consola: ya la fue imprimiendo IgLog ──
if ($es_cli) {
    exit($log->errores > 0 ? 1 : 0);
}

// ── Salida por navegador ──
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sincronización con Instagram</title>
  <style>
    body { font-family: Menlo, Consolas, monospace; background:#0d1b2a; color:#e6e6e6;
           padding:2rem; line-height:1.65; margin:0; }
    h1   { font-family: system-ui, sans-serif; color:#E63946; font-size:1.3rem; margin:0 0 1.2rem; }
    .consola { background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.1);
               border-radius:10px; padding:1.2rem 1.4rem; white-space:pre-wrap;
               font-size:.86rem; overflow-x:auto; }
    .resumen { display:flex; gap:1rem; flex-wrap:wrap; margin:1.4rem 0; }
    .chip { background:rgba(255,255,255,.07); border:1px solid rgba(255,255,255,.14);
            border-radius:999px; padding:.4rem 1rem; font-size:.84rem; }
    .chip b { color:#fff; font-size:1rem; }
    .chip.err { border-color:#E63946; }
    a { color:#8ecae6; }
    .links { margin-top:1.6rem; display:flex; gap:1.4rem; flex-wrap:wrap; font-family:system-ui,sans-serif; font-size:.9rem; }
  </style>
</head>
<body>
  <h1>Sincronización con Instagram</h1>

  <div class="resumen">
    <span class="chip"><b><?= $log->importadas ?></b> novedades nuevas</span>
    <span class="chip"><b><?= $log->omitidas ?></b> omitidas</span>
    <span class="chip<?= $log->errores > 0 ? ' err' : '' ?>"><b><?= $log->errores ?></b> errores</span>
  </div>

  <div class="consola"><?= htmlspecialchars($log->texto(), ENT_QUOTES, 'UTF-8') ?></div>

  <div class="links">
    <a href="novedades.php">Ver el tablón de novedades</a>
    <a href="gestion_instagram.php">Configurar las cuentas</a>
    <a href="panel.php">Volver al panel</a>
  </div>
</body>
</html>
