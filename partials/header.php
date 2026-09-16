<?php
// ============================================================
//  partials/header.php
//  Cabecera única de TODO el sitio público (<head> + <header>).
//  Antes había 4 versiones distintas copiadas en 30 archivos.
//
//  Variables opcionales, definidas ANTES del include:
//    $page_title      string  título de la pestaña (sin el nombre del colegio)
//    $page_desc       string  meta description (SEO)
//    $nav_active      string  clave de MENU_PRINCIPAL a resaltar, o 'inicio'
//    $nav_active_link string  href del item del submenú a resaltar
//    $header_compacto bool    versión de menor altura (agenda, novedades, fichas)
//    $page_style      string  CSS extra propio de la página
//    $body_class      string  clases extra para el <body>
//    $og_image        string  imagen para compartir en redes
// ============================================================

require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/menu.php';
require_once __DIR__ . '/helpers.php';

$page_title      = $page_title      ?? '';
$page_desc       = $page_desc       ?? 'Colegio Parroquial Juan XXIII — Nivel Inicial, Primario y Secundario (Orientada y Técnica) en el oeste del Gran Buenos Aires. Educando desde 1962.';
$nav_active      = $nav_active      ?? '';
$nav_active_link = $nav_active_link ?? '';
$header_compacto = $header_compacto ?? false;
$page_style      = $page_style      ?? '';
$page_head_extra = $page_head_extra ?? '';
$body_class      = $body_class      ?? '';
$og_image        = $og_image        ?? 'img/colegio.jpg';

$colegio    = cfg('nombre_colegio');
$titulo_tab = $page_title !== '' ? $page_title . ' — ' . $colegio : $colegio;

// URL absoluta de la página (para canonical y Open Graph)
$__esquema = (($_SERVER['HTTPS'] ?? '') === 'on') ? 'https' : 'http';
$__host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
$__base    = rtrim($__esquema . '://' . $__host . dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/') . '/';
$url_pagina = $__base . basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');

/** ¿Este href es la página que se está viendo? */
function menu_es_actual(string $href): bool {
    global $nav_active_link;
    return $href === $nav_active_link
        || $href === basename($_SERVER['SCRIPT_NAME'] ?? '');
}
?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= e($titulo_tab) ?></title>
  <meta name="description" content="<?= e($page_desc) ?>"/>
  <link rel="canonical" href="<?= e($url_pagina) ?>"/>

  <!-- Open Graph: cómo se ve al compartir por WhatsApp, Facebook, etc. -->
  <meta property="og:type" content="website"/>
  <meta property="og:site_name" content="<?= e($colegio) ?>"/>
  <meta property="og:title" content="<?= e($titulo_tab) ?>"/>
  <meta property="og:description" content="<?= e($page_desc) ?>"/>
  <meta property="og:url" content="<?= e($url_pagina) ?>"/>
  <meta property="og:image" content="<?= e($__base . $og_image) ?>"/>
  <meta property="og:locale" content="es_AR"/>
  <meta name="twitter:card" content="summary_large_image"/>

<?php require __DIR__ . '/favicon.php'; ?>
  <meta name="theme-color" content="#1D3557"/>

  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link rel="stylesheet" href="styles.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet"/>
<?= $page_head_extra ?>
<?php if ($page_style !== ''): ?>
  <style>
<?= $page_style ?>
  </style>
<?php endif; ?>
</head>
<body<?= $body_class !== '' ? ' class="' . e($body_class) . '"' : '' ?>>

  <a href="#contenido" class="skip-link">Saltar al contenido</a>

  <header class="site-header<?= $header_compacto ? ' site-header--compacto' : '' ?>">

    <!-- Fila 1: accesos rápidos -->
    <div class="topbar">
      <div class="topbar-inner">
        <div class="topbar-quick">
          <a href="novedades.php">Novedades</a>
          <a href="agenda.php">Agenda</a>
          <a class="tq-plataforma" href="plataforma.php">Plataforma</a>
        </div>
      </div>
    </div>

    <!-- Fila 2: logo + navegación -->
    <div class="header-inner two-row">
      <div class="header-top-row">
        <a href="index.php" class="brand">
          <img src="img/logo.png" alt="Logo del <?= cfg_e('nombre_colegio') ?>" class="logo" width="86" height="90"/>
          <span class="brand-text">
            <span class="school-name"><?= cfg_e('nombre_colegio') ?></span>
            <span class="school-motto"><?= cfg_e('lema') ?></span>
          </span>
        </a>

        <div class="header-right">
          <!-- Personita: solo aparece con sesión iniciada (se completa por JS) -->
          <a href="panel.php" id="userBadge" class="user-badge" style="display:none;" title="Ir al panel de control">
            <span class="ub-avatar" id="ubAvatar">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
            <span class="ub-meta">
              <span class="ub-name" id="ubName">Mi cuenta</span>
              <span class="ub-rol" id="ubRol"></span>
            </span>
          </a>

          <button class="hamburger" id="menuToggle" aria-label="Abrir menú" aria-expanded="false" aria-controls="mainNav">
            <span></span><span></span><span></span>
          </button>
        </div>
      </div>

      <nav class="main-nav nav-row" id="mainNav" aria-label="Menú principal">
        <ul class="nav-list">

          <li class="nav-item">
            <a href="index.php" class="nav-link<?= $nav_active === 'inicio' ? ' active-section' : '' ?>">Inicio</a>
          </li>

          <?php foreach (MENU_PRINCIPAL as $clave => $seccion):
              $abierta = ($nav_active === $clave);
              if (isset($seccion['href'])): ?>
            <li class="nav-item">
              <a href="<?= e($seccion['href']) ?>" class="nav-link<?= $abierta ? ' active-section' : '' ?>"<?= $abierta ? ' aria-current="page"' : '' ?>><?= e($seccion['titulo']) ?></a>
            </li>
            <?php continue; endif; ?>
            <li class="nav-item has-dropdown">
              <button class="nav-dropdown-btn<?= $abierta ? ' active-section' : '' ?>"
                      aria-expanded="false" aria-haspopup="true">
                <?= e($seccion['titulo']) ?>
                <svg class="dropdown-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
              </button>
              <div class="nav-dropdown<?= $seccion['cols'] ? ' nav-dropdown--cols' : '' ?>">
                <?php
                $col_abierta = false;
                foreach ($seccion['items'] as $item):
                    if ($item[0] === 'divider') {
                        echo '<div class="dropdown-divider"></div>';
                        continue;
                    }
                    if ($item[0] === 'heading') {
                        if ($col_abierta) echo '</div>';
                        echo '<div class="dd-col"><span class="dd-heading">' . e($item[1]) . '</span>';
                        $col_abierta = true;
                        continue;
                    }
                    [$href, $texto, $sub] = $item;
                    $act = menu_es_actual($href) ? ' active-link' : '';
                    echo '<a href="' . e($href) . '" class="' . trim($act) . '">' . e($texto)
                       . '<span class="dd-sub">' . e($sub) . '</span></a>';
                endforeach;
                if ($col_abierta) echo '</div>';
                ?>
              </div>
            </li>
          <?php endforeach; ?>

          <li class="nav-item mobile-only"><a href="novedades.php" class="nav-link">Novedades</a></li>
          <li class="nav-item mobile-only"><a href="agenda.php" class="nav-link">Agenda</a></li>

        </ul>
      </nav>
    </div>
  </header>

  <main id="contenido">
