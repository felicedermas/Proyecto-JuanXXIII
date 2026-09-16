<?php
// Página generada sobre partials/header.php + partials/footer.php.
// El menú se edita en partials/menu.php; los datos de contacto,
// desde el panel (Datos de contacto).
$page_title      = 'Plataforma Xhendra';
$page_desc       = 'Acceso a Xhendra, la plataforma de gestión académica del Colegio Parroquial Juan XXIII, para familias y alumnos.';
$nav_active      = 'comunidad';
$nav_active_link = 'plataforma.php';
$page_style = <<<'CSS'
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  /* ── DROPDOWN ── */
  .nav-item.has-dropdown { position: relative; }
  .nav-dropdown-btn { display:flex;align-items:center;gap:.45rem;padding:.55rem 1rem;border-radius:8px;color:rgba(255,255,255,.85);font-weight:600;font-size:.88rem;font-family:var(--font-body);background:none;border:none;cursor:pointer;transition:var(--transition);white-space:nowrap; }
  .nav-dropdown-btn:hover,.nav-item.has-dropdown.open .nav-dropdown-btn{background:rgba(255,255,255,.12);color:#fff;}
  .dropdown-chevron{width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round;transition:transform .3s cubic-bezier(.4,0,.2,1);flex-shrink:0;}
  .nav-item.has-dropdown.open .dropdown-chevron{transform:rotate(180deg);}
  .nav-dropdown{position:absolute;top:calc(100% + .5rem);left:50%;transform:translateX(-50%) translateY(-6px);background:#1d3557;border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:.5rem;min-width:230px;box-shadow:0 12px 40px rgba(0,0,0,.4);opacity:0;pointer-events:none;transition:opacity .22s ease,transform .22s cubic-bezier(.4,0,.2,1);z-index:200;}
  .nav-item.has-dropdown.open .nav-dropdown{opacity:1;pointer-events:auto;transform:translateX(-50%) translateY(0);}
  .nav-dropdown a{display:flex;align-items:center;gap:.55rem;padding:.6rem .9rem;border-radius:8px;color:rgba(255,255,255,.8);font-size:.88rem;font-weight:600;font-family:var(--font-body);transition:background .18s,color .18s;white-space:nowrap;}
  .nav-dropdown a:hover{background:rgba(255,255,255,.12);color:#fff;}
  .nav-dropdown .dropdown-divider{height:1px;background:rgba(255,255,255,.1);margin:.35rem .4rem;}
  .nav-dropdown a .dd-sub{font-size:.72rem;font-weight:400;color:rgba(255,255,255,.45);display:block;margin-top:.05rem;}
  @media(max-width:768px){
    .hamburger{display:flex;}
    .nav-dropdown{position:static;transform:none;opacity:1;pointer-events:auto;box-shadow:none;border:none;border-radius:0;background:rgba(0,0,0,.15);padding:0 0 0 1rem;max-height:0;overflow:hidden;transition:max-height .3s ease;}
    .nav-item.has-dropdown.open .nav-dropdown{max-height:420px;}
  }


  .plat-wrap { min-height: calc(100vh - 78px); display:flex; align-items:center; justify-content:center; padding: 3rem 1.5rem 4rem;
    background:
      radial-gradient(circle at 15% 20%, rgba(69,123,157,.12), transparent 45%),
      radial-gradient(circle at 85% 80%, rgba(230,57,70,.10), transparent 45%),
      var(--gray-100); }
  .plat-card { background:#fff; border-radius:var(--radius-lg); box-shadow:var(--shadow-lg); max-width:520px; width:100%; padding:3rem 2.5rem 2.75rem; text-align:center; border-top:5px solid var(--blue-mid); }
  .plat-eyebrow { font-size:.7rem; font-weight:800; letter-spacing:.2em; text-transform:uppercase; color:var(--blue-mid); margin-bottom:1.5rem; }

  /* Logo de la plataforma (SVG provisorio — reemplazar por el oficial) */
  .plat-logo { display:flex; align-items:center; justify-content:center; gap:.6rem; margin-bottom:1.75rem; }
  .plat-logo .mark { width:62px; height:62px; flex-shrink:0; }
  .plat-logo .word { font-family:var(--font-display); font-weight:900; font-size:2.2rem; letter-spacing:-.02em; color:var(--blue-dark); }
  .plat-logo .word span { color:var(--blue-mid); }

  .plat-card h1 { font-family:var(--font-display); font-size:1.5rem; color:var(--blue-dark); margin-bottom:.7rem; }
  .plat-card p { color:#666; font-size:.96rem; line-height:1.75; margin-bottom:1.9rem; }

  .plat-btn { display:inline-flex; align-items:center; gap:.6rem; background:var(--blue-mid); color:#fff; font-weight:800; font-size:1rem; padding:1rem 2.2rem; border-radius:50px; transition:background .2s, transform .2s, box-shadow .2s; box-shadow:0 6px 20px rgba(69,123,157,.35); }
  .plat-btn:hover { background:#3a6a8a; transform:translateY(-2px); box-shadow:0 10px 28px rgba(69,123,157,.45); }
  .plat-btn svg { width:20px; height:20px; stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }

  .plat-note { margin-top:1.6rem; font-size:.78rem; color:var(--gray-500); }
  .plat-help { margin-top:2.2rem; padding-top:1.6rem; border-top:1px solid var(--gray-200); font-size:.84rem; color:#777; }
  .plat-help strong { color:var(--blue-dark); }
CSS;
require __DIR__ . '/partials/header.php';
?>
<section class="plat-wrap">
  <div class="plat-card">
    <p class="plat-eyebrow">Conexión a la plataforma</p>

    <?php
    /* ── LOGO DE XHENDRA ──────────────────────────────────────
       El lugar ya está preparado: copiá el logo oficial en
           img/xhendra.png   (o .svg / .webp)
       y aparece solo, sin tocar código. Mientras el archivo no
       exista se muestra el lockup tipográfico de abajo.
       Tamaño recomendado: alto 84 px, fondo transparente. */
    $xh_logo = null;
    foreach (['img/xhendra.svg', 'img/xhendra.png', 'img/xhendra.webp'] as $cand) {
        if (is_file(__DIR__ . '/' . $cand)) { $xh_logo = $cand; break; }
    }
    ?>
    <div class="xh-logo-wrap">
      <?php if ($xh_logo): ?>
        <img src="<?= e($xh_logo) ?>" alt="Xhendra" class="xh-logo"/>
      <?php else: ?>
        <span class="xh-logo-ph">
          <span class="xh-word">X<em>hendra</em></span>
          <span class="xh-hint">Plataforma académica</span>
        </span>
      <?php endif; ?>
    </div>

    <h1>Accedé a tu cuenta</h1>
    <p>Ingresá a la plataforma educativa <strong>Xhendra</strong> para consultar calificaciones, comunicaciones, material de clase y todo lo relacionado con la gestión académica del colegio.</p>

    <a href="<?= cfg_e('url_xhendra', 'https://xhendra.ar/') ?>" class="plat-btn" target="_blank" rel="noopener noreferrer">
      Ir a la plataforma
      <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
    </a>

    <p class="plat-note">Se abrirá en una pestaña nueva.</p>

    <div class="plat-help">
      ¿Problemas para ingresar? Comunicate con la <strong>Secretaría del colegio</strong> para recuperar tus datos de acceso:
      <?php if (cfg('telefono')): ?><a href="tel:<?= e(tel_href()) ?>"><?= cfg_e('telefono') ?></a><?php endif; ?><?php if (cfg('email')): ?> · <a href="mailto:<?= cfg_e('email') ?>"><?= cfg_e('email') ?></a><?php endif; ?>.
    </div>
  </div>
</section>
<?php require __DIR__ . '/partials/footer.php';
