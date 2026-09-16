<?php
// Página generada sobre partials/header.php + partials/footer.php.
// El menú se edita en partials/menu.php; los datos de contacto,
// desde el panel (Datos de contacto).
$page_title      = 'Nivel Primario';
$page_desc       = 'Nivel Primario del Colegio Parroquial Juan XXIII: 1° a 6° grado, jornada, propuesta pedagógica y actividades.';
$nav_active      = 'niveles';
$nav_active_link = 'nivel-primario.php';
$page_style = <<<'CSS'
/* ============================================================
       NIVEL PRIMARIO — estilos propios
       Paleta: azul oscuro como color dominante (en lugar del rojo del Inicial)
       ============================================================ */

    /* ---- HERO ---- */
    .np-hero {
      position: relative;
      min-height: 60vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background-image: url('primario-hero.jpg');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      overflow: hidden;
    }
    .np-hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(
        110deg,
        rgba(14,26,45,.90) 0%,
        rgba(29,53,87,.65) 55%,
        rgba(14,26,45,.75) 100%
      );
    }
    .np-hero-shapes {
      position: absolute;
      inset: 0;
      pointer-events: none;
      overflow: hidden;
    }
    .np-hero-shapes span {
      position: absolute;
      border-radius: 50%;
      opacity: .14;
    }
    .np-hero-shapes span:nth-child(1){ width:380px;height:380px;background:var(--blue-mid);top:-100px;right:-80px; }
    .np-hero-shapes span:nth-child(2){ width:200px;height:200px;background:var(--blue-light);bottom:-50px;left:6%; }
    .np-hero-shapes span:nth-child(3){ width:110px;height:110px;background:#fff;top:35%;left:22%; }

    .np-hero-content {
      position: relative;
      z-index: 1;
      text-align: center;
      padding: 4rem 2rem;
      max-width: 780px;
    }
    .np-breadcrumb {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      color: var(--blue-light);
      font-size: .8rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .12em;
      margin-bottom: 1.2rem;
    }
    .np-breadcrumb a { color: var(--blue-light); transition: color .2s; }
    .np-breadcrumb a:hover { color: #fff; }
    .np-breadcrumb svg { width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round; }
    .np-hero-title {
      font-family: var(--font-display);
      font-size: clamp(2.2rem, 5vw, 3.8rem);
      color: #fff;
      line-height: 1.1;
      text-shadow: 0 2px 20px rgba(0,0,0,.4);
      margin-bottom: 1rem;
    }
    .np-hero-title em { color: #a8dadc; font-style: italic; }
    .np-hero-desc {
      color: rgba(255,255,255,.82);
      font-size: 1.08rem;
      line-height: 1.8;
      max-width: 600px;
      margin: 0 auto 2rem;
    }

    /* ---- ANCHOR NAV ---- */
    .np-anchor-nav {
      position: sticky;
      top: 78px;
      z-index: 900;
      background: #fff;
      border-bottom: 2px solid var(--gray-200);
      box-shadow: 0 2px 12px rgba(29,53,87,.08);
    }
    .np-anchor-list {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: .5rem;
      padding: .75rem 2rem;
      max-width: 1300px;
      margin: 0 auto;
      overflow-x: auto;
      scrollbar-width: none;
    }
    .np-anchor-list::-webkit-scrollbar { display: none; }
    .np-anchor-link {
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      padding: .45rem 1.1rem;
      border-radius: 50px;
      font-weight: 700;
      font-size: .82rem;
      color: var(--blue-dark);
      white-space: nowrap;
      transition: var(--transition);
      border: 2px solid transparent;
    }
    .np-anchor-link:hover,
    .np-anchor-link.active {
      background: rgba(29,53,87,.1);
      color: var(--blue-dark);
      border-color: rgba(29,53,87,.25);
    }
    .np-anchor-link svg {
      width:15px;height:15px;stroke:currentColor;fill:none;
      stroke-width:2;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0;
    }

    /* ---- LAYOUT COMPARTIDO ---- */
    .np-section {
      padding: 5.5rem 2rem;
      max-width: 1200px;
      margin: 0 auto;
    }
    .np-section-alt { background: var(--gray-100); }
    .np-section-alt > .np-section { padding: 5.5rem 2rem; }

    /* Tag en azul */
    .section-tag.blue {
      background: rgba(29,53,87,.1);
      color: var(--blue-dark);
    }

    /* ============================================================
       1. PROYECTO EDUCATIVO
       ============================================================ */
    .proyecto-grid {
      display: grid;
      grid-template-columns: 1.1fr 1fr;
      gap: 4rem;
      align-items: center;
    }
    .proyecto-text .section-tag { display: inline-block; margin-bottom: 1rem; }
    .proyecto-text h2 {
      font-family: var(--font-display);
      font-size: clamp(1.7rem, 3.5vw, 2.5rem);
      color: var(--blue-dark);
      line-height: 1.2;
      margin-bottom: 1.25rem;
    }
    .proyecto-text p {
      color: #555;
      font-size: .98rem;
      line-height: 1.8;
      margin-bottom: 1rem;
    }
    .proyecto-pillars {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
      margin-top: 2rem;
    }
    .pillar-card {
      background: #fff;
      border-radius: var(--radius);
      padding: 1.25rem;
      border-left: 4px solid var(--blue-dark);
      box-shadow: var(--shadow-sm);
      transition: var(--transition);
    }
    .pillar-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
    .pillar-icon { font-size: 1.6rem; margin-bottom: .5rem; }
    .pillar-card h4 {
      font-family: var(--font-display);
      color: var(--blue-dark);
      font-size: 1rem;
      margin-bottom: .35rem;
    }
    .pillar-card p { font-size: .82rem; color: #666; line-height: 1.5; margin: 0; }

    .proyecto-visual { position: relative; }
    .proyecto-img-wrap { border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-lg); }
    .proyecto-img-placeholder {
      width: 100%;
      aspect-ratio: 4/5;
      background: linear-gradient(135deg, #dde3ec 0%, #c8d8e8 100%);
      display: flex; align-items: center; justify-content: center;
      font-style: italic; color: var(--gray-500); font-size: .85rem;
    }
    .proyecto-badge {
      position: absolute;
      bottom: -1.5rem; left: -1.5rem;
      background: var(--blue-dark);
      color: #fff;
      border-radius: var(--radius);
      padding: 1.25rem 1.5rem;
      box-shadow: var(--shadow-md);
      text-align: center;
      min-width: 130px;
    }
    .proyecto-badge strong {
      display: block;
      font-family: var(--font-display);
      font-size: 2rem;
      font-weight: 900;
      line-height: 1;
    }
    .proyecto-badge span { font-size: .75rem; opacity: .85; text-transform: uppercase; letter-spacing: .06em; }

    /* ============================================================
       2. INSCRIPCIONES
       ============================================================ */
    .inscripciones-header { text-align: center; margin-bottom: 3rem; }
    .inscripciones-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 3rem;
      align-items: start;
    }

    /* Calendario de fechas */
    .fechas-panel h3 {
      font-family: var(--font-display);
      color: var(--blue-dark);
      font-size: 1.3rem;
      margin-bottom: 1.25rem;
    }
    .fecha-item {
      display: flex;
      gap: 1.1rem;
      align-items: flex-start;
      padding: 1rem 0;
      border-bottom: 1px solid var(--gray-200);
    }
    .fecha-item:last-child { border-bottom: none; }
    .fecha-dot {
      flex-shrink: 0;
      margin-top: .25rem;
      width: 38px; height: 38px;
      border-radius: var(--radius);
      background: var(--blue-dark);
      color: #fff;
      display: flex; flex-direction: column;
      align-items: center; justify-content: center;
      font-size: .62rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .05em;
      line-height: 1.1;
    }
    .fecha-dot strong { font-size: 1rem; line-height: 1; }
    .fecha-dot.highlight { background: var(--red); }
    .fecha-info h4 { font-weight: 700; color: var(--blue-dark); font-size: .95rem; margin-bottom: .2rem; }
    .fecha-info p { font-size: .85rem; color: #666; line-height: 1.5; margin: 0; }
    .fecha-badge {
      display: inline-block;
      font-size: .7rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .06em;
      padding: .15rem .6rem;
      border-radius: 50px;
      margin-top: .35rem;
    }
    .badge-abierto  { background: rgba(34,197,94,.12); color: #15803d; }
    .badge-proximo  { background: rgba(249,115,22,.12); color: #c2410c; }
    .badge-cerrado  { background: rgba(100,116,139,.12); color: #475569; }

    .insc-nota {
      background: rgba(29,53,87,.07);
      border-left: 4px solid var(--blue-dark);
      border-radius: 0 var(--radius) var(--radius) 0;
      padding: 1rem 1.25rem;
      font-size: .85rem;
      color: var(--blue-dark);
      margin-top: 1.5rem;
    }
    .insc-nota strong { display: block; margin-bottom: .2rem; }

    /* Pasos + formularios */
    .insc-forms-col { display: flex; flex-direction: column; gap: 1.25rem; }
    .insc-steps {
      counter-reset: step;
      display: flex;
      flex-direction: column;
      gap: .9rem;
      margin-bottom: 1.5rem;
    }
    .insc-step { display: flex; align-items: flex-start; gap: 1rem; }
    .step-num {
      counter-increment: step;
      flex-shrink: 0;
      width: 36px; height: 36px;
      background: var(--blue-dark);
      color: #fff;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-weight: 700; font-size: .88rem;
    }
    .step-num::before { content: counter(step); }
    .insc-step div h4 { font-weight: 700; color: var(--blue-dark); font-size: .95rem; margin-bottom: .2rem; }
    .insc-step div p { font-size: .85rem; color: #666; margin: 0; line-height: 1.5; }

    .form-card {
      background: #fff;
      border-radius: var(--radius-lg);
      padding: 1.6rem;
      box-shadow: var(--shadow-sm);
      border-top: 4px solid var(--blue-dark);
      transition: var(--transition);
    }
    .form-card:nth-child(2) { border-top-color: var(--blue-mid); }
    .form-card:nth-child(3) { border-top-color: var(--red); }
    .form-card:hover { box-shadow: var(--shadow-md); transform: translateY(-3px); }
    .form-card-icon { font-size: 2rem; margin-bottom: .75rem; }
    .form-card h4 { font-family: var(--font-display); color: var(--blue-dark); font-size: 1.05rem; margin-bottom: .4rem; }
    .form-card p { font-size: .85rem; color: #666; line-height: 1.6; margin-bottom: 1.1rem; }
    .btn-form {
      display: inline-flex; align-items: center; gap: .5rem;
      padding: .6rem 1.4rem; border-radius: 50px;
      font-weight: 700; font-size: .85rem;
      cursor: pointer; transition: var(--transition);
      border: 2px solid transparent;
      background: var(--blue-dark); color: #fff;
    }
    .btn-form:hover { background: var(--blue-mid); transform: translateY(-2px); box-shadow: 0 8px 20px rgba(29,53,87,.3); }
    .btn-form svg { width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round; }

    /* ============================================================
       3. MATERIALES
       ============================================================ */
    .materiales-intro { text-align: center; margin-bottom: 3rem; }
    .materiales-intro p { max-width: 600px; margin: 1rem auto 0; color: #555; font-size: .97rem; line-height: 1.8; }

    .grados-tabs {
      display: flex;
      gap: .5rem;
      flex-wrap: wrap;
      justify-content: center;
      margin-bottom: 2.5rem;
    }
    .grado-tab {
      padding: .5rem 1.2rem;
      border-radius: 50px;
      font-weight: 700;
      font-size: .84rem;
      cursor: pointer;
      border: 2px solid var(--gray-200);
      background: #fff;
      color: var(--blue-dark);
      transition: var(--transition);
    }
    .grado-tab:hover { border-color: var(--blue-mid); }
    .grado-tab.active {
      background: var(--blue-dark);
      color: #fff;
      border-color: var(--blue-dark);
    }

    .materiales-panel { display: none; }
    .materiales-panel.active { display: block; }

    .materiales-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.25rem;
    }
    .materia-card {
      background: #fff;
      border-radius: var(--radius);
      overflow: hidden;
      box-shadow: var(--shadow-sm);
    }
    .materia-head {
      background: var(--blue-dark);
      padding: .85rem 1.25rem;
      display: flex;
      align-items: center;
      gap: .7rem;
    }
    .materia-head span { font-size: 1.1rem; }
    .materia-head h4 { font-family: var(--font-display); color: #fff; font-size: .95rem; }
    .materia-items { padding: 1rem 1.25rem; }
    .materia-item {
      display: flex;
      align-items: center;
      gap: .6rem;
      padding: .4rem 0;
      font-size: .87rem;
      color: #555;
      border-bottom: 1px dashed var(--gray-200);
    }
    .materia-item:last-child { border-bottom: none; }
    .materia-item::before {
      content: '';
      width: 7px; height: 7px;
      border-radius: 50%;
      background: var(--blue-mid);
      flex-shrink: 0;
    }
    .materia-nota {
      margin-top: 1.5rem;
      background: rgba(29,53,87,.06);
      border-radius: var(--radius);
      padding: 1rem 1.25rem;
      font-size: .85rem;
      color: var(--blue-dark);
      display: flex;
      gap: .75rem;
      align-items: flex-start;
    }
    .materia-nota span { font-size: 1.1rem; flex-shrink: 0; }

    /* ============================================================
       4. INGLÉS / AACI
       ============================================================ */
    .aaci-section {
      background: var(--blue-dark);
      padding: 5.5rem 2rem;
    }
    .aaci-inner {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 4rem;
      align-items: start;
    }
    .aaci-content .section-tag.light { display: inline-block; margin-bottom: 1rem; }
    .aaci-content h2 {
      font-family: var(--font-display);
      font-size: clamp(1.7rem,3.5vw,2.5rem);
      color: #fff;
      line-height: 1.2;
      margin-bottom: 1.25rem;
    }
    .aaci-content > p {
      color: rgba(255,255,255,.78);
      font-size: .97rem;
      line-height: 1.8;
      margin-bottom: 1.5rem;
    }

    .aaci-niveles {
      display: flex;
      flex-direction: column;
      gap: .85rem;
    }
    .aaci-nivel {
      background: rgba(255,255,255,.08);
      border: 1px solid rgba(255,255,255,.14);
      border-radius: var(--radius);
      padding: 1rem 1.25rem;
      display: grid;
      grid-template-columns: auto 1fr auto;
      align-items: center;
      gap: 1rem;
    }
    .aaci-nivel-icon {
      width: 40px; height: 40px;
      background: var(--blue-mid);
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.1rem;
      flex-shrink: 0;
    }
    .aaci-nivel h4 { font-weight: 700; color: #fff; font-size: .92rem; margin-bottom: .15rem; }
    .aaci-nivel p { font-size: .8rem; color: rgba(255,255,255,.65); margin: 0; }
    .aaci-nivel-grado {
      font-size: .75rem;
      font-weight: 700;
      background: rgba(255,255,255,.15);
      color: var(--blue-light);
      padding: .25rem .7rem;
      border-radius: 50px;
      white-space: nowrap;
    }

    .aaci-info-panel {
      display: flex;
      flex-direction: column;
      gap: 1.25rem;
    }
    .aaci-info-card {
      background: rgba(255,255,255,.08);
      border: 1px solid rgba(255,255,255,.12);
      border-radius: var(--radius-lg);
      padding: 1.5rem;
    }
    .aaci-info-card h4 {
      font-family: var(--font-display);
      color: #fff;
      font-size: 1rem;
      margin-bottom: .75rem;
      display: flex;
      align-items: center;
      gap: .5rem;
    }
    .aaci-info-card h4 span { font-size: 1.2rem; }
    .aaci-info-card p,
    .aaci-info-card li {
      font-size: .86rem;
      color: rgba(255,255,255,.72);
      line-height: 1.65;
    }
    .aaci-info-card ul { padding-left: 1.1rem; display: flex; flex-direction: column; gap: .3rem; }
    .aaci-link {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      margin-top: 1rem;
      color: var(--blue-light);
      font-weight: 700;
      font-size: .85rem;
      transition: color .2s;
    }
    .aaci-link:hover { color: #fff; }
    .aaci-link svg { width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round; }

    /* ============================================================
       5. BECAS
       ============================================================ */
    .becas-header { text-align: center; margin-bottom: 3rem; }
    .becas-header p { max-width: 600px; margin: 1rem auto 0; color: #555; font-size: .97rem; line-height: 1.8; }

    .becas-grid {
      display: grid;
      grid-template-columns: 1.2fr 1fr;
      gap: 3rem;
      align-items: start;
    }

    .beca-tipos { display: flex; flex-direction: column; gap: 1.25rem; }
    .beca-card {
      background: #fff;
      border-radius: var(--radius-lg);
      overflow: hidden;
      box-shadow: var(--shadow-sm);
      transition: var(--transition);
    }
    .beca-card:hover { box-shadow: var(--shadow-md); transform: translateY(-3px); }
    .beca-head {
      padding: 1.25rem 1.5rem;
      display: flex;
      align-items: center;
      gap: .9rem;
    }
    .beca-head.azul   { background: var(--blue-dark); }
    .beca-head.azul2  { background: var(--blue-mid); }
    .beca-head.rojo   { background: var(--red); }
    .beca-head-icon {
      width: 40px; height: 40px;
      background: rgba(255,255,255,.2);
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.2rem;
      flex-shrink: 0;
    }
    .beca-head h3 { font-family: var(--font-display); color: #fff; font-size: 1rem; }
    .beca-head p  { color: rgba(255,255,255,.78); font-size: .78rem; margin: 0; }
    .beca-body { padding: 1.25rem 1.5rem; }
    .beca-body p { font-size: .88rem; color: #555; line-height: 1.7; margin-bottom: .75rem; }

    .beca-req {
      display: flex;
      flex-direction: column;
      gap: .4rem;
      margin-bottom: .9rem;
    }
    .beca-req-item {
      display: flex;
      align-items: center;
      gap: .6rem;
      font-size: .84rem;
      color: #555;
    }
    .beca-req-item::before {
      content: '✓';
      color: var(--blue-dark);
      font-weight: 700;
      font-size: .8rem;
      flex-shrink: 0;
    }

    /* Panel solicitud */
    .becas-solicitud {
      display: flex;
      flex-direction: column;
      gap: 1.25rem;
    }
    .solicitud-card {
      background: #fff;
      border-radius: var(--radius-lg);
      padding: 1.5rem;
      box-shadow: var(--shadow-sm);
      border-left: 4px solid var(--blue-dark);
    }
    .solicitud-card h4 {
      font-family: var(--font-display);
      color: var(--blue-dark);
      font-size: 1rem;
      margin-bottom: .75rem;
      display: flex;
      align-items: center;
      gap: .5rem;
    }
    .solicitud-card h4 span { font-size: 1.1rem; }
    .doc-list {
      display: flex;
      flex-direction: column;
      gap: .4rem;
    }
    .doc-item {
      display: flex;
      align-items: center;
      gap: .6rem;
      font-size: .84rem;
      color: #555;
      padding: .3rem 0;
      border-bottom: 1px dashed var(--gray-200);
    }
    .doc-item:last-child { border-bottom: none; }
    .doc-item svg {
      width:14px;height:14px;stroke:var(--blue-mid);fill:none;
      stroke-width:2;stroke-linecap:round;stroke-linejoin:round;
      flex-shrink: 0;
    }

    .becas-cta {
      background: rgba(29,53,87,.06);
      border-radius: var(--radius);
      padding: 1.25rem;
      text-align: center;
    }
    .becas-cta p { font-size: .87rem; color: #555; line-height: 1.6; margin-bottom: 1rem; }

    /* ============================================================
       CONTACTO RÁPIDO
       ============================================================ */
    .contacto-strip {
      background: var(--blue-dark);
      padding: 3.5rem 2rem;
      text-align: center;
    }
    .contacto-strip h2 {
      font-family: var(--font-display);
      color: #fff;
      font-size: clamp(1.6rem,3vw,2.2rem);
      margin-bottom: .75rem;
    }
    .contacto-strip p { color: rgba(255,255,255,.82); font-size: 1rem; margin-bottom: 1.75rem; }
    .contacto-btns { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }

    /* ============================================================
       RESPONSIVE
       ============================================================ */
    @media (max-width: 1024px) {
      .proyecto-grid     { grid-template-columns: 1fr; gap: 2.5rem; }
      .proyecto-badge    { position: static; margin-top: 1rem; display: inline-block; }
      .inscripciones-grid { grid-template-columns: 1fr; }
      .materiales-grid   { grid-template-columns: 1fr 1fr; }
      .aaci-inner        { grid-template-columns: 1fr; }
      .becas-grid        { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
      .np-anchor-list    { justify-content: flex-start; }
      .proyecto-pillars  { grid-template-columns: 1fr; }
      .materiales-grid   { grid-template-columns: 1fr; }
      .np-hero           { background-attachment: scroll; min-height: 55vh; }
    }
    @media (max-width: 480px) {
      .np-section        { padding: 3.5rem 1.25rem; }
      .aaci-nivel        { grid-template-columns: auto 1fr; }
      .aaci-nivel-grado  { display: none; }
    }
CSS;
require __DIR__ . '/partials/header.php';
?>
  <!-- ===== HERO ===== -->
  <section class="np-hero">
    <div class="np-hero-shapes"><span></span><span></span><span></span></div>
    <div class="np-hero-content">
      <div class="np-breadcrumb">
        <a href="index.php">Inicio</a>
        <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
        <span>Nivel Primario</span>
      </div>
      <h1 class="np-hero-title">Construyendo saberes,<br/><em>formando ciudadanos.</em></h1>
      <p class="np-hero-desc">
        Seis años de aprendizaje sólido, valores y acompañamiento personalizado para que cada alumno desarrolle todo su potencial académico y humano.
      </p>
      <div class="hero-cta">
        <a href="#inscripciones" class="btn btn-primary">Inscribirse ahora</a>
        <a href="#proyecto" class="btn btn-outline">Nuestro proyecto educativo</a>
      </div>
    </div>
  </section>

  <!-- ===== ANCHOR NAV ===== -->
  <div class="np-anchor-nav">
    <ul class="np-anchor-list">
      <li>
        <a href="#proyecto" class="np-anchor-link">
          <svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          Proyecto Educativo
        </a>
      </li>
      <li>
        <a href="#inscripciones" class="np-anchor-link">
          <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          Fechas e Inscripciones
        </a>
      </li>
      <li>
        <a href="#materiales" class="np-anchor-link">
          <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
          Materiales
        </a>
      </li>
      <li>
        <a href="#ingles" class="np-anchor-link">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
          Inglés AACI
        </a>
      </li>
      <li>
        <a href="#becas" class="np-anchor-link">
          <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          Becas
        </a>
      </li>
    </ul>
  </div>

  <!-- ===== 1. PROYECTO EDUCATIVO ===== -->
  <section id="proyecto" class="np-section">
    <div class="proyecto-grid">
      <div class="proyecto-text">
        <span class="section-tag blue">Nuestro enfoque</span>
        <h2>Una educación que integra excelencia y valores</h2>
        <p>
          El Nivel Primario del Colegio Juan XXIII ofrece una propuesta educativa sólida que combina la excelencia académica con la formación en valores evangélicos. Trabajamos con metodologías activas y un enfoque integral que pone al alumno en el centro del proceso de aprendizaje.
        </p>
        <p>
          Nuestro equipo docente, altamente capacitado y comprometido, acompaña a cada alumno en su desarrollo cognitivo, emocional y social a lo largo de los seis años de la escolaridad primaria, preparándolos para afrontar los desafíos del nivel secundario con confianza y autonomía.
        </p>
        <!-- Completar con texto institucional específico del colegio -->
        <div class="proyecto-pillars">
          <div class="pillar-card">
            <div class="pillar-icon">📖</div>
            <h4>Excelencia académica</h4>
            <p>Contenidos curriculares actualizados con metodologías de enseñanza innovadoras.</p>
          </div>
          <div class="pillar-card">
            <div class="pillar-icon">🤝</div>
            <h4>Convivencia</h4>
            <p>Formación en valores, respeto y ciudadanía activa desde el primer grado.</p>
          </div>
          <div class="pillar-card">
            <div class="pillar-icon">💻</div>
            <h4>Tecnología educativa</h4>
            <p>Integración de herramientas digitales como soporte del aprendizaje.</p>
          </div>
          <div class="pillar-card">
            <div class="pillar-icon">🌍</div>
            <h4>Bilingüismo</h4>
            <p>Inglés desde primer grado con certificación AACI al finalizar el nivel.</p>
          </div>
        </div>
      </div>
      <div class="proyecto-visual">
        <div class="proyecto-img-wrap">
          <div class="proyecto-img-placeholder">
            <!-- Reemplazá con: <img src="primario-proyecto.jpg" alt="Alumnos en clase"> -->
            Foto de aula / actividad Nivel Primario
          </div>
        </div>
        <div class="proyecto-badge">
          <strong>1°–6°</strong>
          <span>grado</span>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== 2. INSCRIPCIONES ===== -->
  <div class="np-section-alt" id="inscripciones">
    <div class="np-section">
      <div class="inscripciones-header">
        <span class="section-tag blue">Períodos y requisitos</span>
        <h2 class="section-title">Fechas e Inscripciones</h2>
      </div>
      <div class="inscripciones-grid">

        <!-- Columna izquierda: calendario -->
        <div class="fechas-panel">
          <h3>Calendario de inscripciones</h3>

          <div class="fecha-item">
            <div class="fecha-dot">
              <strong>XX</strong>
              <span>Mes</span>
            </div>
            <div class="fecha-info">
              <h4>Apertura de inscripciones — alumnos nuevos</h4>
              <p>Inicio del período de pre-inscripción para alumnos que ingresan por primera vez al colegio. Completar el formulario online y aguardar contacto de secretaría.</p>
              <span class="fecha-badge badge-abierto">Completar fecha</span>
            </div>
          </div>

          <div class="fecha-item">
            <div class="fecha-dot">
              <strong>XX</strong>
              <span>Mes</span>
            </div>
            <div class="fecha-info">
              <h4>Re-inscripción — alumnos actuales</h4>
              <p>Período de confirmación de vacante para alumnos que ya asisten al colegio. Se realiza a través de secretaría o del formulario habilitado.</p>
              <span class="fecha-badge badge-proximo">Completar fecha</span>
            </div>
          </div>

          <div class="fecha-item">
            <div class="fecha-dot highlight">
              <strong>XX</strong>
              <span>Mes</span>
            </div>
            <div class="fecha-info">
              <h4>Cierre de inscripciones</h4>
              <p>Fecha límite para presentar la documentación completa y confirmar la vacante. Las inscripciones fuera de término quedan sujetas a disponibilidad.</p>
              <span class="fecha-badge badge-cerrado">Completar fecha</span>
            </div>
          </div>

          <div class="fecha-item">
            <div class="fecha-dot">
              <strong>XX</strong>
              <span>Mes</span>
            </div>
            <div class="fecha-info">
              <h4>Inicio del ciclo lectivo</h4>
              <p>Primer día de clases del año. Se informará oportunamente el horario de ingreso y las indicaciones para los alumnos que ingresan por primera vez.</p>
              <span class="fecha-badge badge-proximo">Completar fecha</span>
            </div>
          </div>

          <div class="insc-nota">
            <strong>📌 Importante:</strong>
            Se priorizan las vacantes para hermanos de alumnos actuales y para alumnos provenientes de Nivel Inicial del colegio. Las vacantes son limitadas por sección.
          </div>
        </div>

        <!-- Columna derecha: pasos + formularios -->
        <div class="insc-forms-col">
          <div class="insc-steps">
            <div class="insc-step">
              <div class="step-num"></div>
              <div>
                <h4>Completá el formulario de pre-inscripción</h4>
                <p>Ingresá los datos del alumno y los tutores a través del formulario online. Recibirás un email de confirmación.</p>
              </div>
            </div>
            <div class="insc-step">
              <div class="step-num"></div>
              <div>
                <h4>Entrevista con la dirección</h4>
                <p>Secretaría te contactará para coordinar la entrevista institucional con la directora del nivel.</p>
              </div>
            </div>
            <div class="insc-step">
              <div class="step-num"></div>
              <div>
                <h4>Entrega de documentación</h4>
                <p>DNI del alumno, partida de nacimiento, último boletín de calificaciones, certificado de vacunación y libreta sanitaria.</p>
              </div>
            </div>
            <div class="insc-step">
              <div class="step-num"></div>
              <div>
                <h4>Confirmación de vacante</h4>
                <p>Una vez aprobada la documentación se confirma la vacante y se informa la liquidación de la matrícula.</p>
              </div>
            </div>
          </div>

          <div class="form-card">
            <div class="form-card-icon">📋</div>
            <h4>Pre-inscripción — Alumnos nuevos (1° a 6° grado)</h4>
            <p>Para familias que ingresan al colegio por primera vez. Completar con los datos del alumno y ambos tutores.</p>
            <a href="https://forms.google.com" target="_blank" rel="noopener" class="btn-form">
              <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
              Abrir formulario
            </a>
          </div>
          <div class="form-card">
            <div class="form-card-icon">🔄</div>
            <h4>Re-inscripción — Alumnos actuales</h4>
            <p>Para confirmar la continuidad de alumnos que ya asisten al colegio durante el ciclo lectivo en curso.</p>
            <a href="https://forms.google.com" target="_blank" rel="noopener" class="btn-form">
              <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
              Abrir formulario
            </a>
          </div>
          <div class="form-card">
            <div class="form-card-icon">❓</div>
            <h4>Consultas e información</h4>
            <p>¿Tenés preguntas sobre el proceso de inscripción? Envianos tu consulta y te respondemos a la brevedad.</p>
            <a href="https://forms.google.com" target="_blank" rel="noopener" class="btn-form" style="background:var(--blue-mid);">
              <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
              Enviar consulta
            </a>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- ===== 3. MATERIALES ===== -->
  <section id="materiales" class="np-section">
    <div class="materiales-intro">
      <span class="section-tag blue">Lista de útiles</span>
      <h2 class="section-title">Materiales requeridos</h2>
      <p>La lista de materiales se organiza por grado. Seleccioná el año de tu hijo para ver los útiles específicos. Las listas se actualizan al inicio de cada ciclo lectivo.</p>
    </div>

    <!-- Tabs de grado -->
    <div class="grados-tabs">
      <button class="grado-tab active" data-grado="g1">1° Grado</button>
      <button class="grado-tab" data-grado="g2">2° Grado</button>
      <button class="grado-tab" data-grado="g3">3° Grado</button>
      <button class="grado-tab" data-grado="g4">4° Grado</button>
      <button class="grado-tab" data-grado="g5">5° Grado</button>
      <button class="grado-tab" data-grado="g6">6° Grado</button>
    </div>

    <!-- Panel 1° Grado -->
    <div class="materiales-panel active" id="panel-g1">
      <div class="materiales-grid">
        <div class="materia-card">
          <div class="materia-head">
            <span>✏️</span><h4>Escritura y Lengua</h4>
          </div>
          <div class="materia-items">
            <div class="materia-item">Carpeta de 3 anillos tamaño oficio (completar con cantidad)</div>
            <div class="materia-item">Cuaderno de 48 hojas cuadriculado (completar con cantidad)</div>
            <div class="materia-item">Lápices negros HB Nº 2 (completar con cantidad)</div>
            <div class="materia-item">Goma de borrar blanca</div>
            <div class="materia-item">Sacapuntas con depósito</div>
            <div class="materia-item">Marcadores y crayones (completar colores)</div>
          </div>
        </div>
        <div class="materia-card">
          <div class="materia-head">
            <span>📐</span><h4>Matemática</h4>
          </div>
          <div class="materia-items">
            <div class="materia-item">Cuaderno de 48 hojas cuadriculado (completar)</div>
            <div class="materia-item">Regla de 20 cm plástica</div>
            <div class="materia-item">Compás básico (completar si aplica)</div>
            <div class="materia-item">Calculadora básica (completar si aplica)</div>
          </div>
        </div>
        <div class="materia-card">
          <div class="materia-head">
            <span>🎨</span><h4>Plástica y Arte</h4>
          </div>
          <div class="materia-items">
            <div class="materia-item">Témperas (set de colores básicos)</div>
            <div class="materia-item">Pinceles Nº 6, 10 y 14</div>
            <div class="materia-item">Plasticola blanca 60 g</div>
            <div class="materia-item">Tijera punta redonda</div>
            <div class="materia-item">Cartulinas de colores (completar cantidad)</div>
          </div>
        </div>
      </div>
      <div class="materia-nota">
        <span>📌</span>
        <p>Esta lista es orientativa. La lista definitiva y actualizada para el ciclo lectivo se enviará por cuaderno de comunicaciones antes del inicio de clases. <strong>Reemplazá los ítems marcados con los datos reales.</strong></p>
      </div>
    </div>

    <!-- Panels 2°–6° (misma estructura, completar por el colegio) -->
    <div class="materiales-panel" id="panel-g2">
      <div class="materiales-grid">
        <div class="materia-card">
          <div class="materia-head"><span>✏️</span><h4>Escritura y Lengua</h4></div>
          <div class="materia-items">
            <div class="materia-item">Carpeta de 3 anillos tamaño oficio</div>
            <div class="materia-item">Cuaderno de 48 hojas (completar tipo)</div>
            <div class="materia-item">Lápices negros HB (completar cantidad)</div>
            <div class="materia-item">Birome azul y negra</div>
            <div class="materia-item">Goma y sacapuntas</div>
          </div>
        </div>
        <div class="materia-card">
          <div class="materia-head"><span>📐</span><h4>Matemática</h4></div>
          <div class="materia-items">
            <div class="materia-item">Cuaderno de 48 hojas cuadriculado</div>
            <div class="materia-item">Regla de 30 cm</div>
            <div class="materia-item">Escuadra y transportador (completar)</div>
          </div>
        </div>
        <div class="materia-card">
          <div class="materia-head"><span>🎨</span><h4>Plástica y Arte</h4></div>
          <div class="materia-items">
            <div class="materia-item">Témperas y pinceles (completar)</div>
            <div class="materia-item">Plasticola blanca</div>
            <div class="materia-item">Tijera y materiales varios (completar)</div>
          </div>
        </div>
      </div>
      <div class="materia-nota"><span>📌</span><p>Lista orientativa. La lista definitiva se enviará por cuaderno de comunicaciones. <strong>Completar con los materiales reales de 2° grado.</strong></p></div>
    </div>

    <div class="materiales-panel" id="panel-g3">
      <div class="materiales-grid">
        <div class="materia-card"><div class="materia-head"><span>✏️</span><h4>Escritura y Lengua</h4></div><div class="materia-items"><div class="materia-item">Carpeta tamaño oficio</div><div class="materia-item">Cuadernos (completar cantidad y tipo)</div><div class="materia-item">Birome azul, negra y roja</div><div class="materia-item">Resaltadores (completar colores)</div></div></div>
        <div class="materia-card"><div class="materia-head"><span>📐</span><h4>Matemática</h4></div><div class="materia-items"><div class="materia-item">Cuaderno cuadriculado</div><div class="materia-item">Regla, escuadra y compás</div><div class="materia-item">Calculadora (completar si aplica)</div></div></div>
        <div class="materia-card"><div class="materia-head"><span>🎨</span><h4>Plástica y Arte</h4></div><div class="materia-items"><div class="materia-item">Materiales de plástica (completar)</div><div class="materia-item">Carpeta de dibujo (completar)</div></div></div>
      </div>
      <div class="materia-nota"><span>📌</span><p>Lista orientativa. <strong>Completar con los materiales reales de 3° grado.</strong></p></div>
    </div>

    <div class="materiales-panel" id="panel-g4">
      <div class="materiales-grid">
        <div class="materia-card"><div class="materia-head"><span>✏️</span><h4>Escritura y Lengua</h4></div><div class="materia-items"><div class="materia-item">Carpeta tamaño oficio con folios</div><div class="materia-item">Cuadernos (completar)</div><div class="materia-item">Birome azul, negra y roja</div></div></div>
        <div class="materia-card"><div class="materia-head"><span>📐</span><h4>Matemática y Ciencias</h4></div><div class="materia-items"><div class="materia-item">Cuaderno cuadriculado</div><div class="materia-item">Regla, compás, transportador</div><div class="materia-item">Calculadora científica (completar)</div></div></div>
        <div class="materia-card"><div class="materia-head"><span>💻</span><h4>Tecnología</h4></div><div class="materia-items"><div class="materia-item">Cuaderno de tecnología (completar)</div><div class="materia-item">Materiales adicionales (completar)</div></div></div>
      </div>
      <div class="materia-nota"><span>📌</span><p>Lista orientativa. <strong>Completar con los materiales reales de 4° grado.</strong></p></div>
    </div>

    <div class="materiales-panel" id="panel-g5">
      <div class="materiales-grid">
        <div class="materia-card"><div class="materia-head"><span>✏️</span><h4>Lengua y Sociales</h4></div><div class="materia-items"><div class="materia-item">Carpeta con folios</div><div class="materia-item">Cuadernos (completar cantidad y tipo)</div><div class="materia-item">Biromes y resaltadores</div></div></div>
        <div class="materia-card"><div class="materia-head"><span>📐</span><h4>Matemática y Ciencias</h4></div><div class="materia-items"><div class="materia-item">Cuaderno cuadriculado</div><div class="materia-item">Regla, escuadra, compás</div><div class="materia-item">Calculadora (completar)</div></div></div>
        <div class="materia-card"><div class="materia-head"><span>🌍</span><h4>Inglés</h4></div><div class="materia-items"><div class="materia-item">Cuaderno o carpeta de inglés (completar)</div><div class="materia-item">Material AACI si corresponde (completar)</div></div></div>
      </div>
      <div class="materia-nota"><span>📌</span><p>Lista orientativa. <strong>Completar con los materiales reales de 5° grado.</strong></p></div>
    </div>

    <div class="materiales-panel" id="panel-g6">
      <div class="materiales-grid">
        <div class="materia-card"><div class="materia-head"><span>✏️</span><h4>Lengua y Sociales</h4></div><div class="materia-items"><div class="materia-item">Carpeta con folios tamaño oficio</div><div class="materia-item">Cuadernos (completar cantidad y tipo)</div><div class="materia-item">Biromes, lápiz y resaltadores</div></div></div>
        <div class="materia-card"><div class="materia-head"><span>📐</span><h4>Matemática y Ciencias</h4></div><div class="materia-items"><div class="materia-item">Cuaderno cuadriculado</div><div class="materia-item">Calculadora científica (completar)</div><div class="materia-item">Regla, escuadra y compás</div></div></div>
        <div class="materia-card"><div class="materia-head"><span>🌍</span><h4>Inglés / AACI</h4></div><div class="materia-items"><div class="materia-item">Material de preparación AACI (completar)</div><div class="materia-item">Cuaderno o carpeta de inglés</div><div class="materia-item">Diccionario bilingüe (completar si aplica)</div></div></div>
      </div>
      <div class="materia-nota"><span>📌</span><p>Lista orientativa. <strong>Completar con los materiales reales de 6° grado.</strong></p></div>
    </div>

  </section>

  <!-- ===== 4. INGLÉS AACI ===== -->
  <div class="aaci-section" id="ingles">
    <div class="aaci-inner">
      <div class="aaci-content">
        <span class="section-tag light">Certificación internacional</span>
        <h2>Examen de Inglés AACI</h2>
        <p>
          Nuestro colegio participa en el programa de exámenes de inglés propuesto por la <strong style="color:#fff;">AACI (Asociación Argentina de Cultura Inglesa)</strong>, que permite a los alumnos obtener una certificación internacional de su nivel de dominio del idioma al finalizar el nivel primario.
        </p>
        <p>
          Los exámenes evalúan las cuatro habilidades del idioma: comprensión lectora, expresión escrita, comprensión auditiva y expresión oral, siguiendo estándares del Marco Común Europeo de Referencia.
        </p>

        <div class="aaci-niveles">
          <div class="aaci-nivel">
            <div class="aaci-nivel-icon">🌱</div>
            <div>
              <h4>Starters / Movers</h4>
              <p>Primer acercamiento al inglés formal con vocabulario básico y situaciones cotidianas.</p>
            </div>
            <span class="aaci-nivel-grado">2° – 3° grado</span>
          </div>
          <div class="aaci-nivel">
            <div class="aaci-nivel-icon">📗</div>
            <div>
              <h4>Flyers / KET</h4>
              <p>Nivel elemental a básico, lectura y escritura de textos simples, conversación guiada.</p>
            </div>
            <span class="aaci-nivel-grado">4° – 5° grado</span>
          </div>
          <div class="aaci-nivel">
            <div class="aaci-nivel-icon">🏆</div>
            <div>
              <h4>PET (B1)</h4>
              <p>Nivel intermedio. Capacidad de comunicarse con fluidez en situaciones cotidianas y laborales.</p>
            </div>
            <span class="aaci-nivel-grado">6° grado</span>
          </div>
        </div>

        <!-- Completar con el nombre del examen real que rinde el colegio -->
        <p style="font-size:.82rem;opacity:.6;margin-top:1.25rem;">
          * Los niveles de examen son referenciales. El colegio indicará oportunamente el nivel correspondiente a cada grado para el ciclo lectivo en curso.
        </p>
      </div>

      <div class="aaci-info-panel">
        <div class="aaci-info-card">
          <h4><span>📅</span> Fechas del examen</h4>
          <p>Los exámenes se realizan habitualmente durante el segundo semestre del año. Las fechas exactas son informadas por la AACI y comunicadas a las familias con anticipación a través del cuaderno de comunicaciones.</p>
          <!-- Completar con fechas reales cuando estén disponibles -->
          <ul>
            <li>Fecha tentativa: <strong style="color:#fff;">XX de mes XXXX</strong> (completar)</li>
            <li>Inscripción al examen: hasta el <strong style="color:#fff;">XX de mes</strong> (completar)</li>
          </ul>
        </div>
        <div class="aaci-info-card">
          <h4><span>💰</span> Aranceles y pago</h4>
          <p>El arancel del examen es fijado anualmente por la AACI y varía según el nivel de certificación. El colegio actúa como centro de inscripción.</p>
          <ul>
            <li>El arancel se abona directamente (completar: al colegio / a la AACI)</li>
            <li>Modalidad de pago: (completar)</li>
            <li>Fecha límite de pago: (completar)</li>
          </ul>
        </div>
        <div class="aaci-info-card">
          <h4><span>📚</span> Preparación</h4>
          <p>Los alumnos reciben preparación específica para el examen durante las clases de inglés a lo largo del año. Se recomienda además práctica adicional en casa.</p>
          <ul>
            <li>Material de práctica: (completar si se distribuye)</li>
            <li>Clases de apoyo: (completar si se ofrecen)</li>
          </ul>
          <a href="https://www.aaci.org.ar" target="_blank" rel="noopener" class="aaci-link">
            Sitio oficial AACI
            <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- ===== 5. BECAS ===== -->
  <div class="np-section-alt" id="becas">
    <div class="np-section">
      <div class="becas-header">
        <span class="section-tag blue">Acceso e igualdad</span>
        <h2 class="section-title">Solicitudes de Beca</h2>
        <p>El colegio cuenta con un programa de becas y ayudas económicas destinado a garantizar el acceso a la educación de calidad a familias que lo necesiten. Las becas son evaluadas de forma individualizada y confidencial.</p>
      </div>

      <div class="becas-grid">
        <!-- Tipos de beca -->
        <div class="beca-tipos">
          <div class="beca-card">
            <div class="beca-head azul">
              <div class="beca-head-icon">🎓</div>
              <div>
                <h3>Beca por mérito académico</h3>
                <p>Reconocimiento a la trayectoria escolar</p>
              </div>
            </div>
            <div class="beca-body">
              <p>Destinada a alumnos con desempeño académico sobresaliente sostenido, que presenten promedio de calificaciones igual o superior a (completar). Cubre un porcentaje de la cuota mensual.</p>
              <div class="beca-req">
                <div class="beca-req-item">Promedio de calificaciones: (completar)</div>
                <div class="beca-req-item">Boletín del ciclo anterior sin aplazos</div>
                <div class="beca-req-item">Carta de postulación (completar si aplica)</div>
                <div class="beca-req-item">Sin sanciones disciplinarias</div>
              </div>
              <a href="https://forms.google.com" target="_blank" rel="noopener" class="btn-form" style="font-size:.82rem;padding:.5rem 1.2rem;">
                <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                Solicitar esta beca
              </a>
            </div>
          </div>

          <div class="beca-card">
            <div class="beca-head azul2">
              <div class="beca-head-icon">🏠</div>
              <div>
                <h3>Beca socioeconómica</h3>
                <p>Apoyo a familias con necesidades acreditadas</p>
              </div>
            </div>
            <div class="beca-body">
              <p>Dirigida a familias que atraviesen dificultades económicas documentadas. La cobertura se establece según la evaluación del servicio social del colegio y puede cubrir entre el (completar)% de la cuota.</p>
              <div class="beca-req">
                <div class="beca-req-item">Recibos de sueldo o constancia de ingresos</div>
                <div class="beca-req-item">Composición y tamaño del grupo familiar</div>
                <div class="beca-req-item">Informe socioeconómico (completar si aplica)</div>
                <div class="beca-req-item">Entrevista con asistente social</div>
              </div>
              <a href="https://forms.google.com" target="_blank" rel="noopener" class="btn-form" style="font-size:.82rem;padding:.5rem 1.2rem;background:var(--blue-mid);">
                <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                Solicitar esta beca
              </a>
            </div>
          </div>

          <div class="beca-card">
            <div class="beca-head rojo">
              <div class="beca-head-icon">👨‍👩‍👧‍👦</div>
              <div>
                <h3>Descuento familiar</h3>
                <p>Para familias con más de un hijo en el colegio</p>
              </div>
            </div>
            <div class="beca-body">
              <p>Las familias con dos o más hijos matriculados en cualquier nivel del colegio acceden a un descuento en la cuota mensual del segundo hijo en adelante. El porcentaje de descuento es de (completar)%.</p>
              <div class="beca-req">
                <div class="beca-req-item">Tener 2 o más hijos matriculados</div>
                <div class="beca-req-item">La solicitud se realiza en secretaría</div>
                <div class="beca-req-item">DNI de ambos alumnos (completar si aplica)</div>
              </div>
              <a href="https://forms.google.com" target="_blank" rel="noopener" class="btn-form" style="font-size:.82rem;padding:.5rem 1.2rem;background:var(--red);">
                <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                Solicitar descuento
              </a>
            </div>
          </div>
        </div>

        <!-- Panel de solicitud -->
        <div class="becas-solicitud">
          <div class="solicitud-card">
            <h4><span>📄</span> Documentación general requerida</h4>
            <div class="doc-list">
              <div class="doc-item">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                DNI del alumno (copia)
              </div>
              <div class="doc-item">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                DNI de los tutores (copia)
              </div>
              <div class="doc-item">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Último boletín de calificaciones
              </div>
              <div class="doc-item">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Recibos de sueldo (últimos 3 meses)
              </div>
              <div class="doc-item">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Formulario de solicitud completo
              </div>
              <div class="doc-item">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                (Completar con documentación adicional)
              </div>
            </div>
          </div>

          <div class="solicitud-card" style="border-left-color:var(--blue-mid);">
            <h4><span>📅</span> Período de solicitud</h4>
            <div class="doc-list">
              <div class="doc-item">
                <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Apertura: (completar fecha)
              </div>
              <div class="doc-item">
                <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Cierre de solicitudes: (completar fecha)
              </div>
              <div class="doc-item">
                <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Resolución: (completar fecha aproximada)
              </div>
              <div class="doc-item">
                <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Vigencia de la beca: ciclo lectivo completo
              </div>
            </div>
          </div>

          <div class="becas-cta">
            <p>Las becas se otorgan de manera confidencial y son renovables anualmente. Para consultas personalizadas o situaciones particulares, comunicarse con la dirección del nivel.</p>
            <a href="https://forms.google.com" target="_blank" rel="noopener" class="btn-form" style="display:inline-flex;">
              <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
              Iniciar solicitud de beca
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ===== CONTACTO RÁPIDO ===== -->
  <div class="contacto-strip">
    <h2>¿Querés conocer el Nivel Primario?</h2>
    <p>Coordiná una visita guiada para conocer las aulas, los docentes y nuestra propuesta educativa en persona.</p>
    <div class="contacto-btns">
      <a href="https://forms.google.com" target="_blank" rel="noopener" class="btn btn-white">Solicitar visita guiada</a>
      <a href="index.php" class="btn btn-outline">Volver al inicio</a>
    </div>
  </div>

  <!-- ===== FOOTER ===== -->
  
<?php require __DIR__ . '/partials/footer.php';
