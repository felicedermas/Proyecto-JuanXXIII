<?php
// ============================================================
//  nivel-secundario.php — Selector de modalidad de Secundaria
//
//  Pantalla completa, sin header ni pie: solo dos botones que
//  ocupan cada uno la mitad de la pantalla (izquierda Técnica,
//  derecha Orientada). En el celular se apilan arriba/abajo.
//  El único agregado es el logo chico arriba a la izquierda,
//  para poder volver al inicio.
// ============================================================
require_once __DIR__ . '/conexion.php';

$colegio = cfg('nombre_colegio');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Nivel Secundario — <?= e($colegio) ?></title>
  <meta name="description" content="Secundaria del <?= e($colegio) ?>: Educación Técnica (7 años) y Bachillerato Orientado (6 años). Elegí la modalidad que querés conocer."/>
<?php require __DIR__ . '/partials/favicon.php'; ?>
  <meta name="theme-color" content="#0d1b2a"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Nunito:wght@600;700;800&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; }
    body {
      font-family: 'Nunito', system-ui, sans-serif;
      background: #0d1b2a;
      overflow: hidden;
    }

    .modalidades { display: flex; width: 100%; height: 100vh; height: 100dvh; }

    .mod {
      position: relative;
      flex: 1 1 50%;
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      gap: .6rem;
      padding: 2rem;
      color: #fff; text-decoration: none; text-align: center;
      overflow: hidden;
      transition: flex-grow .5s cubic-bezier(.4,0,.2,1);
    }
    .mod-tecnica   { background: linear-gradient(150deg, #0d1b2a 0%, #1D3557 55%, #b85a22 130%); }
    .mod-orientada { background: linear-gradient(210deg, #1D3557 0%, #457B9D 70%, #A8DADC 150%); }

    /* Brillo suave que se enciende al pasar */
    .mod::before {
      content: ''; position: absolute; inset: 0;
      background: radial-gradient(circle at 50% 50%, rgba(255,255,255,.14) 0%, transparent 60%);
      opacity: 0; transition: opacity .4s;
      pointer-events: none;
    }
    .mod > * { position: relative; }

    /* Línea divisoria entre las dos mitades */
    .mod-tecnica::after {
      content: ''; position: absolute; top: 12%; bottom: 12%; right: 0;
      width: 1px; background: rgba(255,255,255,.22);
    }

    .mod-eyebrow {
      font-size: clamp(.72rem, 1.2vw, .9rem); font-weight: 800;
      letter-spacing: .28em; text-transform: uppercase;
      color: rgba(255,255,255,.72);
    }
    .mod-titulo {
      font-family: 'Playfair Display', Georgia, serif; font-weight: 900;
      font-size: clamp(2.6rem, 7vw, 6rem); line-height: 1;
    }
    .mod-ramas {
      margin-top: .5rem;
      font-size: clamp(1rem, 1.8vw, 1.35rem); font-weight: 700;
      color: rgba(255,255,255,.9);
      max-width: 28ch; line-height: 1.4;
    }
    .mod-flecha {
      margin-top: 1.4rem;
      width: 58px; height: 58px; border-radius: 50%;
      border: 2px solid rgba(255,255,255,.55);
      display: flex; align-items: center; justify-content: center;
      transition: background .3s, border-color .3s, transform .3s;
    }
    .mod-flecha svg { width: 24px; height: 24px; stroke: #fff; fill: none; stroke-width: 2.4; stroke-linecap: round; stroke-linejoin: round; }

    @media (hover: hover) and (min-width: 761px) {
      .mod:hover { flex-grow: 1.35; }
    }
    .mod:hover::before, .mod:focus-visible::before { opacity: 1; }
    .mod:hover .mod-flecha, .mod:focus-visible .mod-flecha { background: #E63946; border-color: #E63946; transform: translateX(6px); }
    .mod:focus-visible { outline: 4px solid #E63946; outline-offset: -8px; }

    /* Logo para volver al inicio */
    .volver {
      position: fixed; top: 1rem; left: 1rem; z-index: 10;
      display: flex; align-items: center; justify-content: center;
      width: 52px; height: 52px; border-radius: 50%;
      background: rgba(255,255,255,.12); backdrop-filter: blur(6px);
      border: 1px solid rgba(255,255,255,.2);
      transition: background .3s;
    }
    .volver:hover, .volver:focus-visible { background: rgba(255,255,255,.28); }
    .volver:focus-visible { outline: 3px solid #E63946; outline-offset: 2px; }
    .volver img { width: 32px; height: auto; }

    /* Celular: mitad de arriba y mitad de abajo */
    @media (max-width: 760px) {
      .modalidades { flex-direction: column; }
      .mod-tecnica::after { top: auto; bottom: 0; left: 12%; right: 12%; width: auto; height: 1px; }
      .mod-flecha { margin-top: .8rem; width: 48px; height: 48px; }
    }

    @media (prefers-reduced-motion: reduce) {
      .mod, .mod::before, .mod-flecha { transition: none; }
    }
  </style>
</head>
<body>

  <a href="./" class="volver" title="Volver al inicio" aria-label="Volver al inicio">
    <img src="img/logo.png" alt="" width="86" height="90"/>
  </a>

  <main class="modalidades">
    <h1 style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);">Nivel Secundario: elegí la modalidad</h1>

    <a href="sec-tecnica" class="mod mod-tecnica">
      <span class="mod-eyebrow">Secundaria</span>
      <span class="mod-titulo">Técnica</span>
      <span class="mod-ramas">Electrónica · Informática · Multimedios</span>
      <span class="mod-flecha" aria-hidden="true"><svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></span>
    </a>

    <a href="sec-orientada" class="mod mod-orientada">
      <span class="mod-eyebrow">Secundaria</span>
      <span class="mod-titulo">Orientada</span>
      <span class="mod-ramas">Economía · Ciencias Naturales</span>
      <span class="mod-flecha" aria-hidden="true"><svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></span>
    </a>
  </main>

</body>
</html>
