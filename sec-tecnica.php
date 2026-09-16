<?php
// Página generada sobre partials/header.php + partials/footer.php.
// El menú se edita en partials/menu.php; los datos de contacto,
// desde el panel (Datos de contacto).
$page_title      = 'Secundaria Técnica';
$page_desc       = 'Secundaria Técnica del Colegio Parroquial Juan XXIII: especialidades en Informática, Electrónica y Multimedios, título de Técnico.';
$nav_active      = 'niveles';
$nav_active_link = 'sec-tecnica.php';
$page_style = <<<'CSS'
/* ============================================================
       SECUNDARIA TÉCNICA — paleta industrial: azul profundo + naranja técnico
       ============================================================ */
    :root {
      --t-main:    #0d1b2a;
      --t-blue:    #1D3557;
      --t-mid:     #457B9D;
      --t-accent:  #e07b39;   /* naranja técnico */
      --t-info:    #3a86a8;
      --t-info:    #3d5a80;
      --t-inf:     #4361ee;   /* Informática */
      --t-ele:     #e07b39;   /* Electrónica */
      --t-mul:     #9b5de5;   /* Multimedios */
    }

    /* ---- HERO ---- */
    .st-hero {
      position: relative; min-height: 58vh;
      display: flex; align-items: center; justify-content: center;
      background-image: url('sec-tecnica-bg.jpg');
      background-size: cover; background-position: center;
      background-attachment: fixed; overflow: hidden;
    }
    .st-hero::before {
      content: ''; position: absolute; inset: 0;
      background: linear-gradient(120deg, rgba(13,27,42,.94) 0%, rgba(224,123,57,.45) 60%, rgba(13,27,42,.85) 100%);
    }
    .st-hero-content {
      position: relative; z-index: 1;
      text-align: center; padding: 4rem 2rem; max-width: 820px;
    }
    .st-breadcrumb {
      display: inline-flex; align-items: center; gap: .5rem;
      color: #A8DADC; font-size: .78rem; font-weight: 700;
      text-transform: uppercase; letter-spacing: .12em; margin-bottom: 1.2rem;
    }
    .st-breadcrumb a { color: #A8DADC; }
    .st-breadcrumb a:hover { color: #fff; }
    .st-breadcrumb svg { width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round; }
    .st-hero-title {
      font-family: var(--font-display);
      font-size: clamp(2rem, 5vw, 3.6rem);
      color: #fff; line-height: 1.1;
      text-shadow: 0 2px 20px rgba(0,0,0,.5); margin-bottom: 1rem;
    }
    .st-hero-title em { color: #f4a261; font-style: italic; }
    .st-hero-desc {
      color: rgba(255,255,255,.8); font-size: 1rem;
      line-height: 1.8; max-width: 620px; margin: 0 auto 2rem;
    }
    .director-chip {
      display: inline-flex; align-items: center; gap: .7rem;
      background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2);
      border-radius: 50px; padding: .4rem 1rem .4rem .5rem;
      color: rgba(255,255,255,.85); font-size: .82rem; margin-bottom: 1.5rem;
    }
    .director-chip-avatar {
      width: 30px; height: 30px; border-radius: 50%;
      background: var(--t-accent); display: flex; align-items: center; justify-content: center;
      font-size: .8rem; flex-shrink: 0;
    }
    .director-chip strong { color: #fff; }

    /* ---- ANCHOR NAV ---- */
    .st-anchor-nav {
      position: sticky; top: 78px; z-index: 900;
      background: var(--t-main); border-bottom: 2px solid rgba(255,255,255,.1);
      box-shadow: 0 4px 20px rgba(0,0,0,.3);
    }
    .st-anchor-list {
      display: flex; align-items: center; justify-content: center;
      gap: .4rem; padding: .65rem 2rem;
      max-width: 1400px; margin: 0 auto;
      overflow-x: auto; scrollbar-width: none;
    }
    .st-anchor-list::-webkit-scrollbar { display: none; }
    .st-anchor-link {
      display: inline-flex; align-items: center; gap: .4rem;
      padding: .4rem .95rem; border-radius: 50px;
      font-weight: 700; font-size: .78rem;
      color: rgba(255,255,255,.65); white-space: nowrap;
      transition: var(--transition); border: 2px solid transparent;
    }
    .st-anchor-link:hover, .st-anchor-link.active {
      background: rgba(224,123,57,.2); color: #f4a261;
      border-color: rgba(224,123,57,.4);
    }
    .st-anchor-link svg { width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0; }

    /* ---- SHARED ---- */
    .st-section { padding: 5rem 2rem; max-width: 1200px; margin: 0 auto; }
    .st-alt { background: var(--gray-100); }
    .st-alt > .st-section { padding: 5rem 2rem; }
    .st-dark { background: var(--t-main); }
    .st-dark > .st-section { padding: 5rem 2rem; }
    .st-mid-dark { background: var(--t-blue); }
    .st-mid-dark > .st-section { padding: 5rem 2rem; }

    .tag-orange {
      display: inline-block;
      background: rgba(224,123,57,.14); color: var(--t-accent);
      font-weight: 700; font-size: .78rem; letter-spacing: .12em;
      text-transform: uppercase; padding: .32rem .95rem;
      border-radius: 50px; margin-bottom: 1rem;
    }
    .tag-white {
      display: inline-block;
      background: rgba(255,255,255,.12); color: #fff;
      font-weight: 700; font-size: .78rem; letter-spacing: .12em;
      text-transform: uppercase; padding: .32rem .95rem;
      border-radius: 50px; margin-bottom: 1rem;
    }

    /* ============================================================
       1. QUÉ ES LA MODALIDAD TÉCNICA
       ============================================================ */
    .modal-header { text-align: center; margin-bottom: 3rem; }
    .modal-header h2 { font-family: var(--font-display); font-size: clamp(1.7rem,3.5vw,2.5rem); color: var(--blue-dark); margin-top: .5rem; }
    .modal-header p { max-width: 680px; margin: .75rem auto 0; color: #555; font-size: .97rem; line-height: 1.8; }

    .modalidad-grid {
      display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: start;
    }
    .modalidad-info p { font-size: .95rem; color: #555; line-height: 1.8; margin-bottom: .9rem; }
    .modalidad-diff {
      background: rgba(224,123,57,.07); border-left: 4px solid var(--t-accent);
      border-radius: 0 var(--radius) var(--radius) 0;
      padding: 1.1rem 1.25rem; margin-top: .5rem;
    }
    .modalidad-diff h4 { font-family: var(--font-display); color: var(--blue-dark); font-size: 1rem; margin-bottom: .6rem; }
    .modalidad-diff ul { display: flex; flex-direction: column; gap: .4rem; padding-left: 1rem; }
    .modalidad-diff li { font-size: .88rem; color: #555; line-height: 1.6; }

    .estructura-grid { display: flex; flex-direction: column; gap: 1.1rem; }
    .ciclo-blk {
      border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-sm);
    }
    .ciclo-blk-head {
      padding: 1rem 1.5rem; display: flex; align-items: center; gap: .8rem;
    }
    .ciclo-blk-head.basico  { background: var(--t-blue); }
    .ciclo-blk-head.inf     { background: #2d3a8c; }
    .ciclo-blk-head.ele     { background: #8a4010; }
    .ciclo-blk-head.mul     { background: #5a3280; }
    .ciclo-blk-head-num { font-family: var(--font-display); font-size: 1.4rem; font-weight: 900; color: rgba(255,255,255,.3); line-height: 1; }
    .ciclo-blk-head h4 { font-family: var(--font-display); color: #fff; font-size: .97rem; }
    .ciclo-blk-head p  { color: rgba(255,255,255,.7); font-size: .76rem; margin: 0; }
    .ciclo-blk-body { background: #fff; padding: 1rem 1.5rem; }
    .anios-row { display: flex; gap: .5rem; flex-wrap: wrap; margin-bottom: .6rem; }
    .anio-c {
      background: var(--gray-100); border-radius: var(--radius);
      padding: .35rem .75rem; font-size: .8rem; font-weight: 700;
      color: var(--blue-dark); border: 1px solid var(--gray-200);
    }
    .ciclo-blk-body p { font-size: .85rem; color: #555; line-height: 1.6; }

    /* ============================================================
       2. ESTRUCTURA + AUTORIDADES
       ============================================================ */
    .aut-header { text-align: center; margin-bottom: 3rem; }
    .aut-header h2 { font-family: var(--font-display); font-size: clamp(1.7rem,3.5vw,2.5rem); color: #fff; margin-top: .5rem; }

    .aut-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 3rem; align-items: start; }
    .aut-list { display: flex; flex-direction: column; gap: .75rem; }
    .aut-card {
      background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.12);
      border-radius: var(--radius); padding: 1rem 1.25rem;
      display: flex; align-items: center; gap: .9rem;
    }
    .aut-avatar {
      width: 44px; height: 44px; border-radius: 50%;
      background: var(--t-accent); display: flex; align-items: center; justify-content: center;
      font-size: 1.1rem; color: #fff; flex-shrink: 0;
    }
    .aut-card h4 { font-weight: 700; color: #fff; font-size: .9rem; margin-bottom: .15rem; }
    .aut-card p  { font-size: .78rem; color: rgba(255,255,255,.6); margin: 0; }
    .aut-card.director-card { border-color: var(--t-accent); border-width: 2px; }
    .aut-card.director-card .aut-avatar { width: 52px; height: 52px; font-size: 1.3rem; }

    .especialidades-tabs { display: flex; gap: .5rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
    .esp-tab {
      padding: .45rem 1.1rem; border-radius: 50px; font-weight: 700; font-size: .82rem;
      cursor: pointer; border: 2px solid transparent;
      background: rgba(255,255,255,.08); color: rgba(255,255,255,.75);
      transition: var(--transition);
    }
    .esp-tab[data-esp="inf"].active  { background: var(--t-inf); color: #fff; border-color: var(--t-inf); }
    .esp-tab[data-esp="ele"].active  { background: var(--t-ele); color: #fff; border-color: var(--t-ele); }
    .esp-tab[data-esp="mul"].active  { background: var(--t-mul); color: #fff; border-color: var(--t-mul); }
    .esp-tab:hover { background: rgba(255,255,255,.14); color: #fff; }

    .esp-panel { display: none; }
    .esp-panel.active { display: block; }

    .esp-content {
      background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.12);
      border-radius: var(--radius-lg); padding: 1.75rem;
    }
    .esp-content h3 { font-family: var(--font-display); color: #fff; font-size: 1.2rem; margin-bottom: .75rem; }
    .esp-content > p { color: rgba(255,255,255,.75); font-size: .92rem; line-height: 1.8; margin-bottom: 1.25rem; }
    .esp-anios-detail { display: flex; flex-direction: column; gap: .6rem; }
    .esp-año {
      display: flex; align-items: center; gap: .9rem;
      background: rgba(255,255,255,.05); border-radius: var(--radius);
      padding: .7rem 1rem;
    }
    .esp-año-num {
      flex-shrink: 0; width: 30px; height: 30px; border-radius: 50%;
      background: rgba(255,255,255,.15); color: #fff;
      display: flex; align-items: center; justify-content: center;
      font-weight: 700; font-size: .82rem;
    }
    .esp-año p { font-size: .85rem; color: rgba(255,255,255,.72); margin: 0; line-height: 1.5; }
    .esp-año strong { color: #fff; }
    .esp-tit {
      display: inline-block; background: rgba(255,255,255,.12); color: var(--t-accent);
      font-size: .7rem; font-weight: 700; text-transform: uppercase;
      letter-spacing: .06em; padding: .2rem .7rem; border-radius: 50px; margin-top: .75rem;
    }

    /* ============================================================
       3. DISEÑO CURRICULAR
       ============================================================ */
    .dc-header { text-align: center; margin-bottom: 3rem; }
    .dc-header h2 { font-family: var(--font-display); font-size: clamp(1.7rem,3.5vw,2.5rem); color: var(--blue-dark); margin-top: .5rem; }

    .dc-tabs { display: flex; gap: .5rem; flex-wrap: wrap; justify-content: center; margin-bottom: 2.5rem; }
    .dc-tab {
      padding: .5rem 1.2rem; border-radius: 50px; font-weight: 700; font-size: .83rem;
      cursor: pointer; border: 2px solid var(--gray-200);
      background: #fff; color: var(--blue-dark); transition: var(--transition);
    }
    .dc-tab[data-dc="inf"].active  { background: var(--t-inf); color: #fff; border-color: var(--t-inf); }
    .dc-tab[data-dc="ele"].active  { background: var(--t-ele); color: #fff; border-color: var(--t-ele); }
    .dc-tab[data-dc="mul"].active  { background: var(--t-mul); color: #fff; border-color: var(--t-mul); }

    .dc-panel { display: none; }
    .dc-panel.active { display: block; }
    .dc-grid {
      display: grid; grid-template-columns: repeat(3,1fr); gap: 1.1rem;
    }
    .dc-year-card {
      background: #fff; border-radius: var(--radius-lg); overflow: hidden;
      box-shadow: var(--shadow-sm);
    }
    .dc-year-head {
      padding: .9rem 1.25rem; text-align: center;
    }
    .dc-year-head.inf { background: var(--t-inf); }
    .dc-year-head.ele { background: var(--t-ele); }
    .dc-year-head.mul { background: var(--t-mul); }
    .dc-year-head h4 { font-family: var(--font-display); color: #fff; font-size: .95rem; }
    .dc-year-head p  { color: rgba(255,255,255,.75); font-size: .74rem; margin: 0; }
    .dc-materias { padding: 1rem 1.25rem; }
    .dc-materia {
      display: flex; align-items: center; gap: .6rem;
      padding: .38rem 0; font-size: .83rem; color: #555;
      border-bottom: 1px dashed var(--gray-200);
    }
    .dc-materia:last-child { border: none; }
    .dc-materia::before { content: ''; width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
    .dc-panel[id="dc-inf"] .dc-materia::before { background: var(--t-inf); }
    .dc-panel[id="dc-ele"] .dc-materia::before { background: var(--t-ele); }
    .dc-panel[id="dc-mul"] .dc-materia::before { background: var(--t-mul); }
    .dc-nota {
      margin-top: 1.25rem; background: rgba(29,53,87,.06);
      border-radius: var(--radius); padding: .9rem 1.1rem;
      font-size: .83rem; color: var(--blue-dark);
      display: flex; gap: .6rem; align-items: flex-start;
    }

    /* ============================================================
       4. TALLER
       ============================================================ */
    .taller-header { text-align: center; margin-bottom: 3rem; }
    .taller-header h2 { font-family: var(--font-display); font-size: clamp(1.7rem,3.5vw,2.5rem); color: #fff; margin-top: .5rem; }
    .taller-grid { display: grid; grid-template-columns: 1.1fr 1fr; gap: 3rem; align-items: start; }
    .taller-info p { color: rgba(255,255,255,.78); font-size: .94rem; line-height: 1.8; margin-bottom: .9rem; }
    .taller-items { display: flex; flex-direction: column; gap: .75rem; margin-top: 1.25rem; }
    .taller-item {
      background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.12);
      border-radius: var(--radius); padding: .85rem 1.1rem;
      display: flex; align-items: flex-start; gap: .75rem;
    }
    .taller-item-icon { font-size: 1.3rem; flex-shrink: 0; }
    .taller-item h4 { font-weight: 700; color: #fff; font-size: .88rem; margin-bottom: .2rem; }
    .taller-item p  { font-size: .82rem; color: rgba(255,255,255,.62); line-height: 1.5; margin: 0; }

    .taller-horarios { display: flex; flex-direction: column; gap: .85rem; }
    .taller-esp-card {
      border-radius: var(--radius-lg); overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,.3);
    }
    .taller-esp-head {
      padding: 1rem 1.25rem; display: flex; align-items: center; gap: .75rem;
    }
    .taller-esp-head.inf { background: var(--t-inf); }
    .taller-esp-head.ele { background: var(--t-ele); }
    .taller-esp-head.mul { background: var(--t-mul); }
    .taller-esp-head span { font-size: 1.3rem; }
    .taller-esp-head h4  { font-family: var(--font-display); color: #fff; font-size: .95rem; }
    .taller-esp-body { background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1); border-top: none; padding: 1rem 1.25rem; }
    .taller-row { display: flex; justify-content: space-between; align-items: center; padding: .4rem 0; border-bottom: 1px solid rgba(255,255,255,.08); font-size: .84rem; }
    .taller-row:last-child { border: none; }
    .taller-row .tr-label { color: rgba(255,255,255,.65); }
    .taller-row .tr-val   { color: #fff; font-weight: 700; }

    /* ============================================================
       5. PROYECTOS
       ============================================================ */
    .proyectos-header { text-align: center; margin-bottom: 3rem; }
    .proyectos-header h2 { font-family: var(--font-display); font-size: clamp(1.7rem,3.5vw,2.5rem); color: var(--blue-dark); margin-top: .5rem; }
    .proyectos-header p { max-width: 600px; margin: .75rem auto 0; color: #555; font-size: .96rem; line-height: 1.8; }

    .proyectos-grid {
      display: grid; grid-template-columns: repeat(3,1fr); gap: 1.5rem;
    }
    .proyecto-card {
      background: #fff; border-radius: var(--radius-lg);
      overflow: hidden; box-shadow: var(--shadow-sm); transition: var(--transition);
    }
    .proyecto-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); }
    .proyecto-img {
      width: 100%; aspect-ratio: 16/9;
      display: flex; align-items: center; justify-content: center;
      font-style: italic; color: rgba(255,255,255,.7); font-size: .82rem;
      position: relative; overflow: hidden;
    }
    .proyecto-img.inf { background: linear-gradient(135deg, #2d3a8c, #4361ee); }
    .proyecto-img.ele { background: linear-gradient(135deg, #8a4010, #e07b39); }
    .proyecto-img.mul { background: linear-gradient(135deg, #5a3280, #9b5de5); }
    .proyecto-img-icon { font-size: 3rem; }
    .proyecto-tag {
      position: absolute; top: .75rem; left: .75rem;
      font-size: .7rem; font-weight: 700; text-transform: uppercase;
      letter-spacing: .06em; padding: .22rem .7rem; border-radius: 50px;
      background: rgba(0,0,0,.35); color: #fff;
    }
    .proyecto-body { padding: 1.25rem; }
    .proyecto-body h4 { font-family: var(--font-display); color: var(--blue-dark); font-size: 1rem; margin-bottom: .4rem; }
    .proyecto-body p  { font-size: .84rem; color: #555; line-height: 1.65; margin-bottom: .75rem; }
    .proyecto-meta {
      display: flex; gap: .6rem; flex-wrap: wrap;
    }
    .proyecto-meta span {
      font-size: .72rem; font-weight: 700; padding: .2rem .65rem;
      border-radius: 50px; background: var(--gray-100); color: var(--blue-dark);
    }

    /* ============================================================
       6. AGENDA
       ============================================================ */
    .ag-header { text-align: center; margin-bottom: 3rem; }
    .ag-header h2 { font-family: var(--font-display); font-size: clamp(1.7rem,3.5vw,2.5rem); color: var(--blue-dark); margin-top: .5rem; }

    .ag-layout { display: grid; grid-template-columns: 1fr 2fr; gap: 2.5rem; align-items: start; }
    .ag-meses h3 { font-family: var(--font-display); color: var(--blue-dark); font-size: 1rem; margin-bottom: 1rem; }
    .ag-mes-btn {
      display: block; width: 100%; padding: .5rem 1rem; border-radius: var(--radius);
      font-weight: 700; font-size: .84rem; text-align: left; cursor: pointer;
      transition: var(--transition); border: 2px solid transparent;
      background: #fff; color: var(--blue-dark);
      box-shadow: var(--shadow-sm); margin-bottom: .4rem;
    }
    .ag-mes-btn:hover  { border-color: var(--t-accent); color: var(--t-accent); }
    .ag-mes-btn.active { background: var(--t-accent); color: #fff; border-color: var(--t-accent); }

    .ag-eventos { display: none; }
    .ag-eventos.active { display: block; }
    .ag-mes-title { font-family: var(--font-display); font-size: 1.4rem; color: var(--blue-dark); margin-bottom: 1.25rem; }
    .ag-item {
      display: grid; grid-template-columns: 56px 1fr; gap: 1rem; align-items: start;
      padding: .85rem 0; border-bottom: 1px solid var(--gray-200);
    }
    .ag-item:last-child { border: none; }
    .ag-dia {
      background: var(--t-blue); color: #fff; border-radius: var(--radius);
      text-align: center; padding: .4rem .3rem; flex-shrink: 0;
    }
    .ag-dia strong { display: block; font-family: var(--font-display); font-size: 1.3rem; line-height: 1; }
    .ag-dia span   { font-size: .62rem; text-transform: uppercase; letter-spacing: .05em; opacity: .75; }
    .ag-dia.nar    { background: var(--t-accent); }
    .ag-dia.fer    { background: #217a6e; }
    .ag-info h4    { font-weight: 700; color: var(--blue-dark); font-size: .92rem; margin-bottom: .18rem; }
    .ag-info p     { font-size: .82rem; color: #666; line-height: 1.5; margin: 0; }
    .ag-tag {
      display: inline-block; font-size: .7rem; font-weight: 700;
      text-transform: uppercase; letter-spacing: .06em;
      padding: .15rem .6rem; border-radius: 50px; margin-top: .3rem;
    }
    .ag-tag.acto   { background: rgba(29,53,87,.1); color: var(--blue-dark); }
    .ag-tag.examen { background: rgba(230,57,70,.1); color: var(--red); }
    .ag-tag.taller { background: rgba(224,123,57,.12); color: var(--t-accent); }
    .ag-tag.feria  { background: rgba(33,122,110,.12); color: #217a6e; }
    .ag-tag.inscr  { background: rgba(67,97,238,.12); color: var(--t-inf); }
    .ag-nota {
      background: rgba(224,123,57,.08); border-left: 4px solid var(--t-accent);
      border-radius: 0 var(--radius) var(--radius) 0;
      padding: .85rem 1.1rem; margin-top: 1.25rem;
      font-size: .83rem; color: #6b3a10;
    }

    /* ============================================================
       7. TABLÓN DE NOVEDADES
       ============================================================ */
    .tablon-header { text-align: center; margin-bottom: 3rem; }
    .tablon-header h2 { font-family: var(--font-display); font-size: clamp(1.7rem,3.5vw,2.5rem); color: #fff; margin-top: .5rem; }

    .tablon-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 2.5rem; }
    .tablon-main { display: flex; flex-direction: column; gap: 1rem; }
    .novedad-card {
      background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.11);
      border-radius: var(--radius-lg); padding: 1.25rem 1.5rem;
      display: flex; gap: 1.1rem; align-items: flex-start;
      transition: var(--transition);
    }
    .novedad-card:hover { background: rgba(255,255,255,.1); }
    .novedad-icon {
      flex-shrink: 0; width: 42px; height: 42px;
      border-radius: var(--radius); background: rgba(255,255,255,.1);
      display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
    }
    .novedad-card h4 { font-weight: 700; color: #fff; font-size: .93rem; margin-bottom: .3rem; }
    .novedad-card p  { font-size: .84rem; color: rgba(255,255,255,.65); line-height: 1.6; margin: 0; }
    .novedad-meta {
      display: flex; gap: .5rem; align-items: center; margin-top: .5rem;
    }
    .novedad-fecha { font-size: .74rem; color: rgba(255,255,255,.42); }
    .nov-tag {
      font-size: .7rem; font-weight: 700; text-transform: uppercase;
      letter-spacing: .05em; padding: .15rem .6rem; border-radius: 50px;
    }
    .nov-tag.imp { background: rgba(230,57,70,.3); color: #ff9999; }
    .nov-tag.info{ background: rgba(67,97,238,.25); color: #99b3ff; }
    .nov-tag.act { background: rgba(224,123,57,.25); color: #f4c49a; }

    .tablon-aside { display: flex; flex-direction: column; gap: 1rem; }
    .aside-widget {
      background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.11);
      border-radius: var(--radius-lg); padding: 1.25rem;
    }
    .aside-widget h4 {
      font-family: var(--font-display); color: #fff; font-size: .95rem;
      margin-bottom: .9rem; display: flex; align-items: center; gap: .4rem;
    }
    .aside-widget li { font-size: .83rem; color: rgba(255,255,255,.65); padding: .35rem 0; border-bottom: 1px solid rgba(255,255,255,.07); }
    .aside-widget li:last-child { border: none; }
    .aside-widget li strong { color: rgba(255,255,255,.85); }

    /* Acceso autorizado desplegable */
    .admin-toggle {
      display: flex; align-items: center; justify-content: space-between;
      width: 100%; background: rgba(255,255,255,.08);
      border: 1px solid rgba(255,255,255,.15); border-radius: var(--radius);
      padding: .75rem 1rem; cursor: pointer; color: rgba(255,255,255,.8);
      font-weight: 700; font-size: .85rem; transition: var(--transition);
      margin-top: .5rem;
    }
    .admin-toggle:hover { background: rgba(255,255,255,.14); }
    .admin-toggle svg { width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;transition:transform .3s; }
    .admin-toggle.open svg { transform: rotate(180deg); }
    .admin-panel {
      display: none; background: rgba(255,255,255,.04);
      border: 1px solid rgba(255,255,255,.1); border-top: none;
      border-radius: 0 0 var(--radius) var(--radius); padding: 1rem 1.25rem;
    }
    .admin-panel.open { display: block; }
    .admin-panel p { font-size: .83rem; color: rgba(255,255,255,.6); margin-bottom: .75rem; }
    .admin-panel input {
      width: 100%; background: rgba(255,255,255,.08);
      border: 1px solid rgba(255,255,255,.18); border-radius: 8px;
      padding: .5rem .8rem; color: #fff; font-size: .84rem;
      font-family: var(--font-body); margin-bottom: .5rem;
    }
    .admin-panel input::placeholder { color: rgba(255,255,255,.35); }
    .admin-btn {
      display: inline-flex; align-items: center; gap: .4rem;
      padding: .5rem 1.2rem; border-radius: 50px;
      background: var(--t-accent); color: #fff; font-weight: 700; font-size: .82rem;
      border: none; cursor: pointer; transition: var(--transition);
    }
    .admin-btn:hover { background: #c9662a; }

    /* ============================================================
       8. FERIA DE CIENCIAS + CENTRO EST.
       ============================================================ */
    .feria-header { text-align: center; margin-bottom: 3rem; }
    .feria-header h2 { font-family: var(--font-display); font-size: clamp(1.7rem,3.5vw,2.5rem); color: var(--blue-dark); margin-top: .5rem; }
    .feria-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: start; }

    .feria-info h3 { font-family: var(--font-display); color: var(--blue-dark); font-size: 1.15rem; margin-bottom: 1rem; }
    .feria-info p  { font-size: .93rem; color: #555; line-height: 1.8; margin-bottom: .85rem; }
    .feria-pasos  { counter-reset: fp; display: flex; flex-direction: column; gap: .75rem; margin: 1.25rem 0; }
    .feria-paso   { display: flex; gap: .85rem; align-items: flex-start; }
    .fp-num { counter-increment: fp; flex-shrink: 0; width: 32px; height: 32px; background: var(--t-accent); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .8rem; }
    .fp-num::before { content: counter(fp); }
    .feria-paso h4 { font-weight: 700; color: var(--blue-dark); font-size: .9rem; margin-bottom: .15rem; }
    .feria-paso p  { font-size: .83rem; color: #666; line-height: 1.5; margin: 0; }

    .ce-st { display: flex; flex-direction: column; gap: 1rem; }
    .ce-st-card {
      background: #fff; border-radius: var(--radius-lg); padding: 1.4rem;
      box-shadow: var(--shadow-sm); border-left: 4px solid var(--t-accent);
      transition: var(--transition);
    }
    .ce-st-card:hover { box-shadow: var(--shadow-md); transform: translateY(-3px); }
    .ce-st-card h4 { font-family: var(--font-display); color: var(--blue-dark); font-size: 1rem; margin-bottom: .4rem; }
    .ce-st-card p  { font-size: .86rem; color: #555; line-height: 1.65; margin-bottom: .6rem; }
    .ce-st-card p:last-child { margin: 0; }

    /* ============================================================
       9. MESAS DE EXAMEN + INGRESO ESCALONADO
       ============================================================ */
    .mesas-header { text-align: center; margin-bottom: 3rem; }
    .mesas-header h2 { font-family: var(--font-display); font-size: clamp(1.7rem,3.5vw,2.5rem); color: #fff; margin-top: .5rem; }
    .mesas-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: start; }

    .mesas-info h3  { font-family: var(--font-display); color: #fff; font-size: 1.1rem; margin-bottom: 1rem; }
    .mesas-info p   { color: rgba(255,255,255,.75); font-size: .92rem; line-height: 1.8; margin-bottom: .85rem; }
    .mesa-table     { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    .mesa-table th  { background: rgba(255,255,255,.1); color: rgba(255,255,255,.85); font-size: .8rem; text-transform: uppercase; letter-spacing: .06em; padding: .6rem .9rem; text-align: left; border-bottom: 1px solid rgba(255,255,255,.12); }
    .mesa-table td  { padding: .65rem .9rem; font-size: .86rem; color: rgba(255,255,255,.7); border-bottom: 1px solid rgba(255,255,255,.07); }
    .mesa-table tr:last-child td { border: none; }
    .mesa-table td strong { color: #fff; }

    .ingreso-col h3  { font-family: var(--font-display); color: #fff; font-size: 1.1rem; margin-bottom: 1rem; }
    .ingreso-col p   { color: rgba(255,255,255,.75); font-size: .92rem; line-height: 1.8; margin-bottom: .85rem; }
    .ing-toggle {
      width: 100%; background: rgba(255,255,255,.08);
      border: 1px solid rgba(255,255,255,.14); border-radius: var(--radius);
      padding: .8rem 1.1rem; cursor: pointer; color: #fff;
      font-weight: 700; font-size: .87rem; text-align: left;
      display: flex; justify-content: space-between; align-items: center;
      transition: var(--transition); margin-bottom: .5rem;
    }
    .ing-toggle:hover { background: rgba(255,255,255,.13); }
    .ing-toggle svg { width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;transition:transform .3s;flex-shrink:0; }
    .ing-toggle.open svg { transform: rotate(180deg); }
    .ing-panel {
      display: none; background: rgba(255,255,255,.04);
      border: 1px solid rgba(255,255,255,.1); border-top: none;
      border-radius: 0 0 var(--radius) var(--radius);
      padding: 1rem 1.25rem; margin-top: -.5rem; margin-bottom: .75rem;
    }
    .ing-panel.open { display: block; }
    .ing-panel p { font-size: .85rem; color: rgba(255,255,255,.65); line-height: 1.65; margin: 0; }

    /* ============================================================
       CONTACTO + TRABAJAR
       ============================================================ */
    .contact-strip {
      background: var(--t-accent); padding: 3.5rem 2rem; text-align: center;
    }
    .contact-strip h2 { font-family: var(--font-display); color: #fff; font-size: clamp(1.5rem,3vw,2.1rem); margin-bottom: .75rem; }
    .contact-strip p  { color: rgba(255,255,255,.85); font-size: 1rem; margin-bottom: 1.75rem; }
    .contact-btns     { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
    .btn-dark { background: var(--t-main); color: #fff; }
    .btn-dark:hover { background: #000; transform: translateY(-2px); }

    /* ============================================================
       RESPONSIVE
       ============================================================ */
    @media (max-width: 1024px) {
      .modalidad-grid { grid-template-columns: 1fr; }
      .aut-grid       { grid-template-columns: 1fr; }
      .dc-grid        { grid-template-columns: 1fr 1fr; }
      .proyectos-grid { grid-template-columns: 1fr 1fr; }
      .ag-layout      { grid-template-columns: 1fr; }
      .tablon-grid    { grid-template-columns: 1fr; }
      .feria-grid     { grid-template-columns: 1fr; }
      .mesas-grid     { grid-template-columns: 1fr; }
      .taller-grid    { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
      .st-hero { background-attachment: scroll; min-height: 52vh; }
      .dc-grid        { grid-template-columns: 1fr; }
      .proyectos-grid { grid-template-columns: 1fr; }
      .st-section { padding: 3.5rem 1.25rem; }
    }
  
  /* ── DROPDOWN NIVELES ── */
  .nav-item.has-dropdown { position: relative; }

  .nav-dropdown-btn {
    display: flex;
    align-items: center;
    gap: .45rem;
    padding: .55rem 1rem;
    border-radius: 8px;
    color: rgba(255,255,255,.85);
    font-weight: 600;
    font-size: .88rem;
    font-family: var(--font-body);
    background: none;
    border: none;
    cursor: pointer;
    transition: var(--transition);
    white-space: nowrap;
  }
  .nav-dropdown-btn:hover,
  .nav-item.has-dropdown.open .nav-dropdown-btn {
    background: rgba(255,255,255,.12);
    color: #fff;
  }
  .nav-dropdown-btn.active-section {
    background: rgba(255,255,255,.15);
    color: #fff;
  }
  .dropdown-chevron {
    width: 14px; height: 14px;
    stroke: currentColor; fill: none;
    stroke-width: 2.5;
    stroke-linecap: round; stroke-linejoin: round;
    transition: transform .3s cubic-bezier(.4,0,.2,1);
    flex-shrink: 0;
  }
  .nav-item.has-dropdown.open .dropdown-chevron { transform: rotate(180deg); }

  .nav-dropdown {
    position: absolute;
    top: calc(100% + .5rem);
    left: 50%;
    transform: translateX(-50%) translateY(-6px);
    background: #1d3557;
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 12px;
    padding: .5rem;
    min-width: 220px;
    box-shadow: 0 12px 40px rgba(0,0,0,.4);
    opacity: 0;
    pointer-events: none;
    transition: opacity .22s ease, transform .22s cubic-bezier(.4,0,.2,1);
    z-index: 200;
  }
  .nav-item.has-dropdown.open .nav-dropdown {
    opacity: 1;
    pointer-events: auto;
    transform: translateX(-50%) translateY(0);
  }
  .nav-dropdown a {
    display: flex;
    align-items: center;
    gap: .55rem;
    padding: .6rem .9rem;
    border-radius: 8px;
    color: rgba(255,255,255,.8);
    font-size: .88rem;
    font-weight: 600;
    font-family: var(--font-body);
    transition: background .18s, color .18s;
    white-space: nowrap;
  }
  .nav-dropdown a:hover { background: rgba(255,255,255,.12); color: #fff; }
  .nav-dropdown a.active-link {
    background: rgba(255,255,255,.18);
    color: #fff;
  }
  .nav-dropdown .dropdown-divider {
    height: 1px;
    background: rgba(255,255,255,.1);
    margin: .35rem .4rem;
  }
  /* sublabel dentro del dropdown */
  .nav-dropdown a .dd-sub {
    font-size: .72rem;
    font-weight: 400;
    color: rgba(255,255,255,.45);
    display: block;
    margin-top: .05rem;
  }

  /* Mobile: dropdown se expande inline */
  @media (max-width: 768px) {
    .nav-dropdown {
      position: static;
      transform: none;
      opacity: 1;
      pointer-events: auto;
      box-shadow: none;
      border: none;
      border-radius: 0;
      background: rgba(0,0,0,.15);
      padding: 0 0 0 1rem;
      max-height: 0;
      overflow: hidden;
      transition: max-height .3s ease;
    }
    .nav-item.has-dropdown.open .nav-dropdown { max-height: 300px; }
    .nav-item.has-dropdown.open .dropdown-chevron { transform: rotate(180deg); }
  }
CSS;
require __DIR__ . '/partials/header.php';
?>
  <!-- HERO -->
  <section class="st-hero">
    <div class="st-hero-content">
      <div class="st-breadcrumb">
        <a href="index.php">Inicio</a>
        <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
        <a href="nivel-secundario.php">Secundario</a>
        <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
        <span>Técnica</span>
      </div>
      <div class="director-chip">
        <div class="director-chip-avatar">👤</div>
        <span>Dir. <strong>Oscar Villarruel</strong> — Modalidad Técnica</span>
      </div>
      <h1 class="st-hero-title">Secundaria<br/><em>Técnica</em></h1>
      <p class="st-hero-desc">Siete años de formación técnico-profesional con especialidades en Informática, Electrónica y Multimedios. Combinamos teoría, taller y práctica profesional para formar técnicos competentes.</p>
      <div class="hero-cta">
        <a href="#modalidad" class="btn btn-primary">¿Qué es la modalidad técnica?</a>
        <a href="nivel-secundario.php" class="btn btn-outline">← Ver modalidad Orientada</a>
      </div>
    </div>
  </section>

  <!-- ANCHOR NAV -->
  <div class="st-anchor-nav">
    <ul class="st-anchor-list">
      <li><a href="#modalidad"    class="st-anchor-link"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>Modalidad</a></li>
      <li><a href="#autoridades"  class="st-anchor-link"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>Estructura</a></li>
      <li><a href="#curricula"    class="st-anchor-link"><svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>Diseño Curricular</a></li>
      <li><a href="#taller"       class="st-anchor-link"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93l-1.41 1.41M4.93 4.93l1.41 1.41M22 12h-2M4 12H2M19.07 19.07l-1.41-1.41M4.93 19.07l1.41-1.41M12 22v-2M12 4V2"/></svg>Taller</a></li>
      <li><a href="#proyectos"    class="st-anchor-link"><svg viewBox="0 0 24 24"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>Proyectos</a></li>
      <li><a href="#agenda"       class="st-anchor-link"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>Agenda</a></li>
      <li><a href="novedades.php"    class="st-anchor-link"><svg viewBox="0 0 24 24"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/></svg>Novedades</a></li>
      <li><a href="#feria"        class="st-anchor-link"><svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>Feria y CE</a></li>
      <li><a href="#mesas"        class="st-anchor-link"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>Mesas</a></li>
    </ul>
  </div>

  <!-- 1. MODALIDAD TÉCNICA -->
  <section id="modalidad" class="st-section">
    <div class="modal-header">
      <span class="tag-orange">Educación técnico-profesional</span>
      <h2>¿Qué es la modalidad técnica?</h2>
      <p>La modalidad técnica es una rama del nivel secundario orientada a la formación técnico-profesional. A diferencia del bachillerato orientado, los alumnos reciben formación específica en una especialidad con fuerte componente de taller y práctica real.</p>
    </div>
    <div class="modalidad-grid">
      <div class="modalidad-info">
        <p>La Secundaria Técnica tiene una duración de <strong>7 años</strong> (dos años más que la orientada), lo que permite una formación más profunda en la especialidad elegida. Al finalizar, los egresados obtienen el título de <strong>Técnico</strong> en la especialidad, habilitante tanto para ejercer la profesión como para continuar estudios universitarios.</p>
        <p>El plan de estudios combina materias del área general (comunes a todo nivel secundario) con materias técnicas específicas de la especialidad y horas de taller práctico, donde los alumnos aplican lo aprendido en proyectos reales.</p>
        <div class="modalidad-diff">
          <h4>¿Qué diferencia a un técnico de un bachiller?</h4>
          <ul>
            <li>El título de técnico habilita para el <strong>ejercicio profesional inmediato</strong> en el área de la especialidad.</li>
            <li>La formación incluye horas de <strong>taller obligatorio</strong> donde se desarrollan proyectos reales.</li>
            <li>Los alumnos cursan materias específicas de la especialidad desde 1° año y profundizan desde 4°.</li>
            <li>Al egresar, pueden validar conocimientos para acceder a carreras universitarias con equivalencias.</li>
            <li>La duración es de <strong>7 años</strong> versus 6 del bachillerato.</li>
          </ul>
        </div>
      </div>
      <div>
        <h3 style="font-family:var(--font-display);color:var(--blue-dark);font-size:1.1rem;margin-bottom:1.25rem;">División por ciclos</h3>
        <div class="estructura-grid">
          <div class="ciclo-blk">
            <div class="ciclo-blk-head basico">
              <span class="ciclo-blk-head-num">I</span>
              <div><h4>Ciclo Básico — 1° a 3° año</h4><p>Común a todas las especialidades</p></div>
            </div>
            <div class="ciclo-blk-body">
              <div class="anios-row"><span class="anio-c">1° año</span><span class="anio-c">2° año</span><span class="anio-c">3° año</span></div>
              <p>Formación general común: Lengua, Matemática, Ciencias, Historia, Geografía, Inglés, Ed. Física, y materias técnicas introductorias. Al finalizar 3°, el alumno elige su especialidad para el ciclo técnico.</p>
            </div>
          </div>
          <div class="ciclo-blk">
            <div class="ciclo-blk-head inf">
              <span class="ciclo-blk-head-num">II</span>
              <div><h4>Ciclo Técnico — Informática</h4><p>4° a 7° año</p></div>
            </div>
            <div class="ciclo-blk-body">
              <div class="anios-row"><span class="anio-c">4°</span><span class="anio-c">5°</span><span class="anio-c">6°</span><span class="anio-c">7°</span></div>
              <p>Programación, redes, bases de datos, desarrollo web, sistemas operativos y proyectos de software con clientes reales.</p>
            </div>
          </div>
          <div class="ciclo-blk">
            <div class="ciclo-blk-head ele">
              <span class="ciclo-blk-head-num">II</span>
              <div><h4>Ciclo Técnico — Electrónica</h4><p>4° a 7° año</p></div>
            </div>
            <div class="ciclo-blk-body">
              <div class="anios-row"><span class="anio-c">4°</span><span class="anio-c">5°</span><span class="anio-c">6°</span><span class="anio-c">7°</span></div>
              <p>Circuitos, microcontroladores, automatización, robótica y proyectos de aplicación electrónica industrial y domótica.</p>
            </div>
          </div>
          <div class="ciclo-blk">
            <div class="ciclo-blk-head mul">
              <span class="ciclo-blk-head-num">II</span>
              <div><h4>Ciclo Técnico — Multimedios</h4><p>4° a 7° año</p></div>
            </div>
            <div class="ciclo-blk-body">
              <div class="anios-row"><span class="anio-c">4°</span><span class="anio-c">5°</span><span class="anio-c">6°</span><span class="anio-c">7°</span></div>
              <p>Diseño gráfico, producción audiovisual, fotografía, animación, diseño web y producción de contenidos digitales.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 2. ESTRUCTURA Y AUTORIDADES -->
  <div class="st-dark" id="autoridades">
    <div class="st-section">
      <div class="aut-header">
        <span class="tag-white">Conducción institucional</span>
        <h2>Autoridades y estructura organizacional</h2>
      </div>
      <div class="aut-grid">
        <div>
          <div class="aut-list">
            <div class="aut-card director-card">
              <div class="aut-avatar" style="font-size:1.5rem;">👤</div>
              <div><h4>Oscar Villarruel</h4><p>Director — Modalidad Técnica</p></div>
            </div>
            <div class="aut-card"><div class="aut-avatar">👤</div><div><h4>Nombre Vicedirector/a</h4><p>Vicedirección — Modalidad Técnica</p></div></div>
            <div class="aut-card"><div class="aut-avatar">👤</div><div><h4>Nombre Secretario/a</h4><p>Secretaría académica</p></div></div>
            <div class="aut-card"><div class="aut-avatar">👤</div><div><h4>Nombre Preceptor/a</h4><p>Preceptoría — Ciclo Básico</p></div></div>
            <div class="aut-card"><div class="aut-avatar">👤</div><div><h4>Nombre Preceptor/a</h4><p>Preceptoría — Ciclo Técnico</p></div></div>
            <div class="aut-card"><div class="aut-avatar">🔧</div><div><h4>Nombre Jefe/a de Taller</h4><p>Coordinación de Talleres</p></div></div>
          </div>
        </div>
        <div>
          <div class="especialidades-tabs">
            <button class="esp-tab active" data-esp="inf">💻 Informática</button>
            <button class="esp-tab" data-esp="ele">⚡ Electrónica</button>
            <button class="esp-tab" data-esp="mul">🎨 Multimedios</button>
          </div>
          <!-- Informática -->
          <div class="esp-panel active" id="esp-inf">
            <div class="esp-content">
              <h3>💻 Especialidad en Informática</h3>
              <p>Forma técnicos capaces de desarrollar, implementar y mantener sistemas informáticos. Los egresados pueden trabajar en desarrollo de software, administración de redes, soporte técnico y sistemas de información.</p>
              <div class="esp-anios-detail">
                <div class="esp-año"><div class="esp-año-num">4°</div><p><strong>Algoritmos y programación</strong> · Introducción a redes · Sistemas operativos I</p></div>
                <div class="esp-año"><div class="esp-año-num">5°</div><p><strong>Programación orientada a objetos</strong> · Redes y comunicaciones · Bases de datos I</p></div>
                <div class="esp-año"><div class="esp-año-num">6°</div><p><strong>Desarrollo web</strong> · Bases de datos II · Administración de sistemas · Práctica profesional I</p></div>
                <div class="esp-año"><div class="esp-año-num">7°</div><p><strong>Proyecto integrador</strong> · Práctica profesional II · Seguridad informática · Gestión de proyectos</p></div>
              </div>
              <span class="esp-tit">Título: Técnico en Informática Profesional y Personal</span>
            </div>
          </div>
          <!-- Electrónica -->
          <div class="esp-panel" id="esp-ele">
            <div class="esp-content">
              <h3>⚡ Especialidad en Electrónica</h3>
              <p>Forma técnicos en el diseño, instalación y mantenimiento de sistemas electrónicos. Los egresados pueden trabajar en automatización industrial, reparación de equipos, domótica y electrónica de consumo.</p>
              <div class="esp-anios-detail">
                <div class="esp-año"><div class="esp-año-num">4°</div><p><strong>Electrónica analógica I</strong> · Circuitos eléctricos · Electrotecnia básica</p></div>
                <div class="esp-año"><div class="esp-año-num">5°</div><p><strong>Electrónica digital</strong> · Microcontroladores I · Instrumentación y medición</p></div>
                <div class="esp-año"><div class="esp-año-num">6°</div><p><strong>Microcontroladores II</strong> · Automatización · Robótica básica · Práctica profesional I</p></div>
                <div class="esp-año"><div class="esp-año-num">7°</div><p><strong>Proyecto integrador</strong> · Domótica · Práctica profesional II · Gestión técnica</p></div>
              </div>
              <span class="esp-tit">Título: Técnico en Electrónica</span>
            </div>
          </div>
          <!-- Multimedios -->
          <div class="esp-panel" id="esp-mul">
            <div class="esp-content">
              <h3>🎨 Especialidad en Multimedios</h3>
              <p>Forma técnicos en producción audiovisual, diseño digital y comunicación multimedial. Los egresados pueden trabajar en diseño gráfico, producción de video, fotografía, animación y marketing digital.</p>
              <div class="esp-anios-detail">
                <div class="esp-año"><div class="esp-año-num">4°</div><p><strong>Diseño gráfico I</strong> · Fotografía digital · Narrativa audiovisual básica</p></div>
                <div class="esp-año"><div class="esp-año-num">5°</div><p><strong>Producción audiovisual</strong> · Diseño web · Animación digital I</p></div>
                <div class="esp-año"><div class="esp-año-num">6°</div><p><strong>Postproducción</strong> · Animación II · Identidad visual · Práctica profesional I</p></div>
                <div class="esp-año"><div class="esp-año-num">7°</div><p><strong>Proyecto integrador</strong> · Producción transmedia · Práctica profesional II</p></div>
              </div>
              <span class="esp-tit">Título: Técnico en Multimedios</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 3. DISEÑO CURRICULAR -->
  <section id="curricula" class="st-section">
    <div class="dc-header">
      <span class="tag-orange">Plan de estudios oficial</span>
      <h2>Diseño curricular por especialidad</h2>
    </div>
    <div class="dc-tabs">
      <button class="dc-tab active" data-dc="inf">💻 Informática</button>
      <button class="dc-tab" data-dc="ele">⚡ Electrónica</button>
      <button class="dc-tab" data-dc="mul">🎨 Multimedios</button>
    </div>

    <!-- Informática -->
    <div class="dc-panel active" id="dc-inf">
      <div class="dc-grid">
        <div class="dc-year-card"><div class="dc-year-head inf"><h4>4° año</h4><p>Inicio del ciclo técnico</p></div><div class="dc-materias"><div class="dc-materia">Algoritmos y Programación</div><div class="dc-materia">Sistemas Operativos I</div><div class="dc-materia">Redes I</div><div class="dc-materia">Matemática Aplicada</div><div class="dc-materia">Inglés Técnico</div><div class="dc-materia">Taller de Informática I</div></div></div>
        <div class="dc-year-card"><div class="dc-year-head inf"><h4>5° año</h4><p></p></div><div class="dc-materias"><div class="dc-materia">Prog. Orientada a Objetos</div><div class="dc-materia">Bases de Datos I</div><div class="dc-materia">Redes II</div><div class="dc-materia">Sistemas Operativos II</div><div class="dc-materia">Inglés Técnico II</div><div class="dc-materia">Taller de Informática II</div></div></div>
        <div class="dc-year-card"><div class="dc-year-head inf"><h4>6° año</h4><p></p></div><div class="dc-materias"><div class="dc-materia">Desarrollo Web</div><div class="dc-materia">Bases de Datos II</div><div class="dc-materia">Administración de Sistemas</div><div class="dc-materia">Seguridad Informática</div><div class="dc-materia">Práctica Profesional I</div><div class="dc-materia">Taller de Informática III</div></div></div>
        <div class="dc-year-card" style="grid-column:1/-1;max-width:380px;margin:0 auto;"><div class="dc-year-head inf"><h4>7° año</h4><p>Proyecto integrador y egreso</p></div><div class="dc-materias"><div class="dc-materia">Proyecto Integrador</div><div class="dc-materia">Gestión de Proyectos TI</div><div class="dc-materia">Práctica Profesional II</div><div class="dc-materia">Ética y Legislación Informática</div></div></div>
      </div>
      <div class="dc-nota"><span>📌</span><p>El diseño curricular se ajusta a la Resolución (completar número y organismo). Las materias del área general (Lengua, Matemática, Historia, etc.) se dictan en todos los años. Completar con el documento oficial del establecimiento.</p></div>
    </div>

    <!-- Electrónica -->
    <div class="dc-panel" id="dc-ele">
      <div class="dc-grid">
        <div class="dc-year-card"><div class="dc-year-head ele"><h4>4° año</h4><p>Inicio del ciclo técnico</p></div><div class="dc-materias"><div class="dc-materia">Electrónica Analógica I</div><div class="dc-materia">Circuitos Eléctricos</div><div class="dc-materia">Matemática Aplicada</div><div class="dc-materia">Inglés Técnico</div><div class="dc-materia">Taller de Electrónica I</div></div></div>
        <div class="dc-year-card"><div class="dc-year-head ele"><h4>5° año</h4><p></p></div><div class="dc-materias"><div class="dc-materia">Electrónica Digital</div><div class="dc-materia">Microcontroladores I</div><div class="dc-materia">Instrumentación</div><div class="dc-materia">Inglés Técnico II</div><div class="dc-materia">Taller de Electrónica II</div></div></div>
        <div class="dc-year-card"><div class="dc-year-head ele"><h4>6° año</h4><p></p></div><div class="dc-materias"><div class="dc-materia">Microcontroladores II</div><div class="dc-materia">Automatización</div><div class="dc-materia">Robótica Básica</div><div class="dc-materia">Práctica Profesional I</div><div class="dc-materia">Taller de Electrónica III</div></div></div>
        <div class="dc-year-card" style="grid-column:1/-1;max-width:380px;margin:0 auto;"><div class="dc-year-head ele"><h4>7° año</h4><p>Proyecto integrador y egreso</p></div><div class="dc-materias"><div class="dc-materia">Proyecto Integrador</div><div class="dc-materia">Domótica</div><div class="dc-materia">Práctica Profesional II</div><div class="dc-materia">Gestión y Legislación Técnica</div></div></div>
      </div>
      <div class="dc-nota"><span>📌</span><p>Diseño curricular sujeto a la resolución provincial vigente. Completar con el documento oficial del establecimiento.</p></div>
    </div>

    <!-- Multimedios -->
    <div class="dc-panel" id="dc-mul">
      <div class="dc-grid">
        <div class="dc-year-card"><div class="dc-year-head mul"><h4>4° año</h4><p>Inicio del ciclo técnico</p></div><div class="dc-materias"><div class="dc-materia">Diseño Gráfico I</div><div class="dc-materia">Fotografía Digital</div><div class="dc-materia">Narrativa Audiovisual</div><div class="dc-materia">Inglés Técnico</div><div class="dc-materia">Taller Multimedios I</div></div></div>
        <div class="dc-year-card"><div class="dc-year-head mul"><h4>5° año</h4><p></p></div><div class="dc-materias"><div class="dc-materia">Producción Audiovisual</div><div class="dc-materia">Diseño Web</div><div class="dc-materia">Animación Digital I</div><div class="dc-materia">Inglés Técnico II</div><div class="dc-materia">Taller Multimedios II</div></div></div>
        <div class="dc-year-card"><div class="dc-year-head mul"><h4>6° año</h4><p></p></div><div class="dc-materias"><div class="dc-materia">Postproducción de Video</div><div class="dc-materia">Animación Digital II</div><div class="dc-materia">Identidad Visual</div><div class="dc-materia">Práctica Profesional I</div><div class="dc-materia">Taller Multimedios III</div></div></div>
        <div class="dc-year-card" style="grid-column:1/-1;max-width:380px;margin:0 auto;"><div class="dc-year-head mul"><h4>7° año</h4><p>Proyecto integrador y egreso</p></div><div class="dc-materias"><div class="dc-materia">Proyecto Integrador</div><div class="dc-materia">Producción Transmedia</div><div class="dc-materia">Práctica Profesional II</div><div class="dc-materia">Gestión Cultural y Derechos</div></div></div>
      </div>
      <div class="dc-nota"><span>📌</span><p>Diseño curricular sujeto a la resolución provincial vigente. Completar con el documento oficial del establecimiento.</p></div>
    </div>
  </section>

  <!-- 4. TALLER -->
  <div class="st-mid-dark" id="taller">
    <div class="st-section">
      <div class="taller-header">
        <span class="tag-white">Formación práctica</span>
        <h2>El taller técnico</h2>
      </div>
      <div class="taller-grid">
        <div class="taller-info">
          <p>El taller es el corazón de la formación técnica. Es el espacio donde los alumnos aplican los conocimientos teóricos en proyectos concretos, trabajan con herramientas y equipamiento profesional, y desarrollan competencias que no pueden adquirirse solo en el aula.</p>
          <p>Cada especialidad cuenta con su propio espacio de taller equipado. Las horas de taller son obligatorias y forman parte de la carga horaria semanal desde 1° año.</p>
          <div class="taller-items">
            <div class="taller-item"><div class="taller-item-icon">🛠️</div><div><h4>Equipamiento actualizado</h4><p>Cada taller cuenta con herramientas y equipos actualizados acordes a los estándares actuales de cada especialidad.</p></div></div>
            <div class="taller-item"><div class="taller-item-icon">📐</div><div><h4>Proyectos reales</h4><p>Los alumnos de ciclo técnico desarrollan proyectos con aplicación real, muchos de los cuales son presentados en la Feria de Ciencias.</p></div></div>
            <div class="taller-item"><div class="taller-item-icon">🤝</div><div><h4>Trabajo en equipo</h4><p>Los proyectos de taller se desarrollan en grupos, fomentando la colaboración y la comunicación técnica efectiva.</p></div></div>
            <div class="taller-item"><div class="taller-item-icon">📋</div><div><h4>Documentación técnica</h4><p>Los alumnos aprenden a documentar sus proyectos con informes técnicos, manuales y memorias descriptivas.</p></div></div>
          </div>
        </div>
        <div>
          <div class="taller-horarios">
            <div class="taller-esp-card">
              <div class="taller-esp-head inf"><span>💻</span><h4>Taller de Informática</h4></div>
              <div class="taller-esp-body">
                <div class="taller-row"><span class="tr-label">Horario</span><span class="tr-val">Completar días y horas</span></div>
                <div class="taller-row"><span class="tr-label">Docente a cargo</span><span class="tr-val">Completar nombre</span></div>
                <div class="taller-row"><span class="tr-label">Equipamiento</span><span class="tr-val">PCs, servidores, switches, racks</span></div>
                <div class="taller-row"><span class="tr-label">Capacidad</span><span class="tr-val">Completar cantidad de alumnos</span></div>
              </div>
            </div>
            <div class="taller-esp-card">
              <div class="taller-esp-head ele"><span>⚡</span><h4>Taller de Electrónica</h4></div>
              <div class="taller-esp-body">
                <div class="taller-row"><span class="tr-label">Horario</span><span class="tr-val">Completar días y horas</span></div>
                <div class="taller-row"><span class="tr-label">Docente a cargo</span><span class="tr-val">Completar nombre</span></div>
                <div class="taller-row"><span class="tr-label">Equipamiento</span><span class="tr-val">Osciloscopios, protoboards, soldadores, Arduino</span></div>
                <div class="taller-row"><span class="tr-label">Capacidad</span><span class="tr-val">Completar cantidad de alumnos</span></div>
              </div>
            </div>
            <div class="taller-esp-card">
              <div class="taller-esp-head mul"><span>🎨</span><h4>Taller de Multimedios</h4></div>
              <div class="taller-esp-body">
                <div class="taller-row"><span class="tr-label">Horario</span><span class="tr-val">Completar días y horas</span></div>
                <div class="taller-row"><span class="tr-label">Docente a cargo</span><span class="tr-val">Completar nombre</span></div>
                <div class="taller-row"><span class="tr-label">Equipamiento</span><span class="tr-val">Cámaras, sets de iluminación, estación de edición</span></div>
                <div class="taller-row"><span class="tr-label">Capacidad</span><span class="tr-val">Completar cantidad de alumnos</span></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 5. PROYECTOS -->
  <section id="proyectos" class="st-section">
    <div class="proyectos-header">
      <span class="tag-orange">Lo que hacen nuestros alumnos</span>
      <h2>Proyectos destacados</h2>
      <p>Cada año los alumnos del ciclo técnico desarrollan proyectos integradores que muestran el nivel de formación alcanzado. Estos proyectos representan el trabajo y la creatividad de nuestra comunidad educativa.</p>
    </div>
    <div class="proyectos-grid">
      <div class="proyecto-card">
        <div class="proyecto-img inf"><span class="proyecto-tag">Informática</span><div class="proyecto-img-icon">💻</div></div>
        <div class="proyecto-body"><h4>Nombre del proyecto</h4><p>Descripción breve del proyecto realizado por los alumnos de Informática. Completar con el proyecto real más representativo del año.</p><div class="proyecto-meta"><span>7° año</span><span>Informática</span><span>Año lectivo: XXXX</span></div></div>
      </div>
      <div class="proyecto-card">
        <div class="proyecto-img inf"><span class="proyecto-tag">Informática</span><div class="proyecto-img-icon">🌐</div></div>
        <div class="proyecto-body"><h4>Nombre del proyecto</h4><p>Descripción del segundo proyecto de Informática destacado. Completar con el proyecto real.</p><div class="proyecto-meta"><span>6° año</span><span>Informática</span><span>Año lectivo: XXXX</span></div></div>
      </div>
      <div class="proyecto-card">
        <div class="proyecto-img ele"><span class="proyecto-tag">Electrónica</span><div class="proyecto-img-icon">⚡</div></div>
        <div class="proyecto-body"><h4>Nombre del proyecto</h4><p>Descripción del proyecto de Electrónica. Completar con el proyecto real más representativo.</p><div class="proyecto-meta"><span>7° año</span><span>Electrónica</span><span>Año lectivo: XXXX</span></div></div>
      </div>
      <div class="proyecto-card">
        <div class="proyecto-img ele"><span class="proyecto-tag">Electrónica</span><div class="proyecto-img-icon">🤖</div></div>
        <div class="proyecto-body"><h4>Nombre del proyecto</h4><p>Descripción del segundo proyecto de Electrónica. Completar con el proyecto real.</p><div class="proyecto-meta"><span>6° año</span><span>Electrónica</span><span>Año lectivo: XXXX</span></div></div>
      </div>
      <div class="proyecto-card">
        <div class="proyecto-img mul"><span class="proyecto-tag">Multimedios</span><div class="proyecto-img-icon">🎬</div></div>
        <div class="proyecto-body"><h4>Nombre del proyecto</h4><p>Descripción del proyecto de Multimedios. Completar con el proyecto real más representativo.</p><div class="proyecto-meta"><span>7° año</span><span>Multimedios</span><span>Año lectivo: XXXX</span></div></div>
      </div>
      <div class="proyecto-card">
        <div class="proyecto-img mul"><span class="proyecto-tag">Multimedios</span><div class="proyecto-img-icon">🎨</div></div>
        <div class="proyecto-body"><h4>Nombre del proyecto</h4><p>Descripción del segundo proyecto de Multimedios. Completar con el proyecto real.</p><div class="proyecto-meta"><span>6° año</span><span>Multimedios</span><span>Año lectivo: XXXX</span></div></div>
      </div>
    </div>
  </section>

  <!-- 6. AGENDA -->
  <div class="st-alt" id="agenda">
    <div class="st-section">
      <div class="ag-header">
        <span class="tag-orange">Organización del año</span>
        <h2>Agenda escolar — Modalidad Técnica</h2>
      </div>
      <div class="ag-layout">
        <div class="ag-meses">
          <h3>Seleccioná el mes</h3>
          <button class="ag-mes-btn active" data-ag="marzo">Marzo</button>
          <button class="ag-mes-btn" data-ag="abril">Abril</button>
          <button class="ag-mes-btn" data-ag="mayo">Mayo</button>
          <button class="ag-mes-btn" data-ag="junio">Junio</button>
          <button class="ag-mes-btn" data-ag="agosto">Agosto</button>
          <button class="ag-mes-btn" data-ag="septiembre">Septiembre</button>
          <button class="ag-mes-btn" data-ag="octubre">Octubre</button>
          <button class="ag-mes-btn" data-ag="noviembre">Noviembre</button>
          <button class="ag-mes-btn" data-ag="diciembre">Diciembre</button>
          <div class="ag-nota">Las fechas se confirman al inicio de cada ciclo lectivo. Ante consultas, comunicarse con secretaría técnica.</div>
        </div>
        <div>
          <div class="ag-eventos active" id="ag-marzo"><p class="ag-mes-title">Marzo</p>
            <div class="ag-item"><div class="ag-dia nar"><strong>DD</strong><span>Mar</span></div><div class="ag-info"><h4>Inicio del ciclo lectivo</h4><p>Primer día de clases para todos los años. Completar con fecha exacta.</p><span class="ag-tag inscr">Inicio de clases</span></div></div>
            <div class="ag-item"><div class="ag-dia"><strong>DD</strong><span>Mar</span></div><div class="ag-info"><h4>Reunión de padres de 1° año</h4><p>Presentación del proyecto educativo, reglamento de taller y autoridades. Completar con fecha y hora.</p><span class="ag-tag acto">Reunión</span></div></div>
            <div class="ag-item"><div class="ag-dia"><strong>DD</strong><span>Mar</span></div><div class="ag-info"><h4>Inicio de talleres — todos los cursos</h4><p>Comienzo formal de las actividades de taller por especialidad. Completar con fecha.</p><span class="ag-tag taller">Taller</span></div></div>
          </div>
          <div class="ag-eventos" id="ag-abril"><p class="ag-mes-title">Abril</p>
            <div class="ag-item"><div class="ag-dia nar"><strong>DD</strong><span>Abr</span></div><div class="ag-info"><h4>Acto por el Día del Veterano</h4><p>Acto institucional con participación de todos los cursos. Completar con detalles.</p><span class="ag-tag acto">Acto escolar</span></div></div>
            <div class="ag-item"><div class="ag-dia"><strong>DD</strong><span>Abr</span></div><div class="ag-info"><h4>Entrega de propuesta de proyecto integrador — 7° año</h4><p>Los alumnos de 7° año presentan la propuesta preliminar de su proyecto final. Completar con fecha.</p><span class="ag-tag taller">Proyecto</span></div></div>
          </div>
          <div class="ag-eventos" id="ag-mayo"><p class="ag-mes-title">Mayo</p>
            <div class="ag-item"><div class="ag-dia nar"><strong>25</strong><span>May</span></div><div class="ag-info"><h4>Acto por el 25 de Mayo</h4><p>Acto escolar conmemorativo de la Revolución de Mayo. Participan todos los cursos.</p><span class="ag-tag acto">Acto escolar</span></div></div>
            <div class="ag-item"><div class="ag-dia"><strong>DD</strong><span>May</span></div><div class="ag-info"><h4>Evaluaciones de taller — primer período</h4><p>Evaluaciones parciales de avance en los proyectos de taller de cada especialidad. Completar.</p><span class="ag-tag taller">Evaluación de taller</span></div></div>
          </div>
          <div class="ag-eventos" id="ag-junio"><p class="ag-mes-title">Junio</p>
            <div class="ag-item"><div class="ag-dia"><strong>DD</strong><span>Jun</span></div><div class="ag-info"><h4>Cierre del 1er. trimestre</h4><p>Período de cierre y acreditación del primer trimestre. Completar con fechas.</p><span class="ag-tag acto">Cierre trimestre</span></div></div>
          </div>
          <div class="ag-eventos" id="ag-agosto"><p class="ag-mes-title">Agosto</p>
            <div class="ag-item"><div class="ag-dia nar"><strong>DD</strong><span>Ago</span></div><div class="ag-info"><h4>Regreso de clases — 2do. semestre</h4><p>Inicio del segundo semestre. Completar con fecha exacta.</p><span class="ag-tag inscr">Inicio de clases</span></div></div>
            <div class="ag-item"><div class="ag-dia fer"><strong>DD</strong><span>Ago</span></div><div class="ag-info"><h4>Inscripción a la Feria de Ciencias</h4><p>Apertura del período de inscripción para proyectos que participarán en la Feria. Completar con fechas.</p><span class="ag-tag feria">Feria de Ciencias</span></div></div>
          </div>
          <div class="ag-eventos" id="ag-septiembre"><p class="ag-mes-title">Septiembre</p>
            <div class="ag-item"><div class="ag-dia nar"><strong>DD</strong><span>Sep</span></div><div class="ag-info"><h4>Día del estudiante — actividades especiales</h4><p>Festejo del Día del Estudiante con actividades organizadas por el Centro de Estudiantes. Completar.</p><span class="ag-tag acto">Acto escolar</span></div></div>
            <div class="ag-item"><div class="ag-dia fer"><strong>DD</strong><span>Sep</span></div><div class="ag-info"><h4>Feria de Ciencias Institucional</h4><p>Muestra de proyectos de taller de todas las especialidades, abierta a la comunidad. Completar con fecha y lugar.</p><span class="ag-tag feria">Feria de Ciencias</span></div></div>
          </div>
          <div class="ag-eventos" id="ag-octubre"><p class="ag-mes-title">Octubre</p>
            <div class="ag-item"><div class="ag-dia"><strong>DD</strong><span>Oct</span></div><div class="ag-info"><h4>Defensa preliminar de proyectos integradores — 7° año</h4><p>Los alumnos de 7° presentan el avance de su proyecto final ante el tribunal técnico. Completar.</p><span class="ag-tag taller">Proyecto integrador</span></div></div>
            <div class="ag-item"><div class="ag-dia"><strong>DD</strong><span>Oct</span></div><div class="ag-info"><h4>Inscripción a mesas de examen</h4><p>Período de inscripción a mesas para materias libres y previas. Completar con fechas.</p><span class="ag-tag inscr">Inscripción</span></div></div>
          </div>
          <div class="ag-eventos" id="ag-noviembre"><p class="ag-mes-title">Noviembre</p>
            <div class="ag-item"><div class="ag-dia nar"><strong>DD</strong><span>Nov</span></div><div class="ag-info"><h4>Colación de egresados — 7° año</h4><p>Ceremonia de egreso de los técnicos. Completar con fecha, hora y lugar.</p><span class="ag-tag acto">Colación</span></div></div>
            <div class="ag-item"><div class="ag-dia"><strong>DD</strong><span>Nov</span></div><div class="ag-info"><h4>Defensa final de proyectos integradores</h4><p>Defensa ante tribunal de los proyectos finales de 7° año. Completar con fecha.</p><span class="ag-tag taller">Proyecto integrador</span></div></div>
            <div class="ag-item"><div class="ag-dia"><strong>DD</strong><span>Nov</span></div><div class="ag-info"><h4>Mesas de examen — período regular</h4><p>Mesas de examen para materias libres y previas. Completar con días y horarios.</p><span class="ag-tag examen">Mesa de examen</span></div></div>
          </div>
          <div class="ag-eventos" id="ag-diciembre"><p class="ag-mes-title">Diciembre</p>
            <div class="ag-item"><div class="ag-dia"><strong>DD</strong><span>Dic</span></div><div class="ag-info"><h4>Cierre del ciclo lectivo</h4><p>Último día de clases. Completar con fecha según calendario oficial.</p><span class="ag-tag acto">Cierre</span></div></div>
            <div class="ag-item"><div class="ag-dia nar"><strong>DD</strong><span>Dic</span></div><div class="ag-info"><h4>Mesas de examen — período diciembre</h4><p>Mesas de fin de año. Completar con fechas y horarios.</p><span class="ag-tag examen">Mesa de examen</span></div></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 7. TABLÓN DE NOVEDADES -->
  <div class="st-dark" id="novedades">
    <div class="st-section">
      <div class="tablon-header">
        <span class="tag-white">Información actualizada</span>
        <h2>Tablón de novedades</h2>
      </div>
      <div class="tablon-grid">
        <div class="tablon-main">
          <div class="novedad-card">
            <div class="novedad-icon">📢</div>
            <div>
              <h4>Título de la novedad</h4>
              <p>Descripción de la novedad. Completar con información real de la institución. Las novedades se publican aquí para mantener informada a la comunidad educativa.</p>
              <div class="novedad-meta"><span class="novedad-fecha">DD/MM/AAAA</span><span class="nov-tag imp">Importante</span></div>
            </div>
          </div>
          <div class="novedad-card">
            <div class="novedad-icon">🗓️</div>
            <div>
              <h4>Título de la novedad</h4>
              <p>Información sobre próximas actividades, modificaciones de horario o anuncios institucionales. Completar con contenido real.</p>
              <div class="novedad-meta"><span class="novedad-fecha">DD/MM/AAAA</span><span class="nov-tag info">Información</span></div>
            </div>
          </div>
          <div class="novedad-card">
            <div class="novedad-icon">🏆</div>
            <div>
              <h4>Título de la novedad</h4>
              <p>Reconocimiento, logro o noticia positiva de la institución o de los alumnos. Completar con el contenido real.</p>
              <div class="novedad-meta"><span class="novedad-fecha">DD/MM/AAAA</span><span class="nov-tag act">Logro</span></div>
            </div>
          </div>
          <div class="novedad-card">
            <div class="novedad-icon">📋</div>
            <div>
              <h4>Título de la novedad</h4>
              <p>Anuncio sobre inscripciones, cambios de calendario o cualquier información relevante para los alumnos y familias. Completar con contenido real.</p>
              <div class="novedad-meta"><span class="novedad-fecha">DD/MM/AAAA</span><span class="nov-tag info">Información</span></div>
            </div>
          </div>
        </div>
        <div class="tablon-aside">
          <div class="aside-widget">
            <h4>📅 Próximas fechas clave</h4>
            <ul>
              <li><strong>DD/MM</strong> — Nombre del evento (completar)</li>
              <li><strong>DD/MM</strong> — Nombre del evento (completar)</li>
              <li><strong>DD/MM</strong> — Nombre del evento (completar)</li>
              <li><strong>DD/MM</strong> — Nombre del evento (completar)</li>
              <li><strong>DD/MM</strong> — Nombre del evento (completar)</li>
            </ul>
          </div>
          <div class="aside-widget">
            <h4>🔗 Accesos rápidos</h4>
            <ul>
              <li><a href="https://forms.google.com" target="_blank" rel="noopener" style="color:rgba(255,255,255,.75);">Inscripción a mesas de examen</a></li>
              <li><a href="https://forms.google.com" target="_blank" rel="noopener" style="color:rgba(255,255,255,.75);">Feria de Ciencias — inscribir proyecto</a></li>
              <li><a href="https://forms.google.com" target="_blank" rel="noopener" style="color:rgba(255,255,255,.75);">Trabajar en la institución</a></li>
              <li><a href="https://forms.google.com" target="_blank" rel="noopener" style="color:rgba(255,255,255,.75);">Consultas a secretaría</a></li>
            </ul>
          </div>
          <!-- Acceso autorizado para actualizar novedades -->
          <div class="aside-widget">
            <h4>🔒 Acceso institucional</h4>
            <button class="admin-toggle" id="adminToggle">
              Actualizar novedades
              <svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="admin-panel" id="adminPanel">
              <p>Acceso exclusivo para personal autorizado de la institución.</p>
              <input type="text" placeholder="Usuario institucional"/>
              <input type="password" placeholder="Contraseña"/>
              <button class="admin-btn">
                <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                Ingresar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 8. FERIA DE CIENCIAS + CENTRO DE ESTUDIANTES -->
  <section id="feria" class="st-section">
    <div class="feria-header">
      <span class="tag-orange">Comunidad e innovación</span>
      <h2>Feria de Ciencias y Centro de Estudiantes</h2>
    </div>
    <div class="feria-grid">
      <div class="feria-info">
        <h3>🔬 Feria de Ciencias</h3>
        <p>La Feria de Ciencias Institucional es el evento más importante de la modalidad técnica. Se realiza anualmente y convoca a alumnos de todas las especialidades para exhibir sus proyectos de taller ante la comunidad, el jurado y autoridades educativas.</p>
        <p>Los proyectos ganadores pueden participar de instancias distritales y regionales, representando al colegio en el circuito de ferias de ciencias de la provincia.</p>
        <div class="feria-pasos">
          <div class="feria-paso"><div class="fp-num"></div><div><h4>Presentación de propuesta</h4><p>Los equipos presentan su propuesta de proyecto en el formulario de inscripción. Máximo (completar) integrantes por proyecto.</p></div></div>
          <div class="feria-paso"><div class="fp-num"></div><div><h4>Desarrollo y seguimiento</h4><p>El equipo docente hace seguimiento del avance del proyecto a lo largo del año.</p></div></div>
          <div class="feria-paso"><div class="fp-num"></div><div><h4>Exposición pública</h4><p>El día de la Feria, cada equipo expone su proyecto ante el jurado y el público. Duración de la defensa: (completar) minutos.</p></div></div>
          <div class="feria-paso"><div class="fp-num"></div><div><h4>Evaluación y premiación</h4><p>El jurado evalúa innovación, viabilidad técnica y presentación. Los equipos ganadores reciben reconocimiento institucional.</p></div></div>
        </div>
      </div>
      <div class="ce-st">
        <div class="ce-st-card">
          <h4>🏛️ Centro de Estudiantes — Modalidad Técnica</h4>
          <p>El Centro de Estudiantes es el órgano de representación democrática de los alumnos de la Secundaria Técnica. Se elige anualmente por voto secreto y directo de todos los estudiantes del nivel.</p>
          <p>Representa los intereses del alumnado, organiza actividades estudiantiles, participa en reuniones con la dirección y promueve la convivencia y la participación activa en la vida institucional.</p>
        </div>
        <div class="ce-st-card" style="border-left-color:var(--t-inf);">
          <h4>🗳️ Elecciones del Centro</h4>
          <p>Las elecciones se realizan en (completar mes) de cada año. Pueden postularse alumnos de (completar) año en adelante. La comisión directiva está integrada por: Presidente/a, Vicepresidente/a, Secretario/a y Tesorero/a.</p>
        </div>
        <div class="ce-st-card" style="border-left-color:var(--t-mul);">
          <h4>📣 Actividades del Centro</h4>
          <p>El Centro organiza el festejo del Día del Estudiante, la colaboración con la Feria de Ciencias, campañas solidarias en articulación con Pastoral, y actúa como canal de comunicación entre el alumnado y la dirección técnica.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- 9. MESAS DE EXAMEN + INGRESO ESCALONADO -->
  <div class="st-dark" id="mesas">
    <div class="st-section">
      <div class="mesas-header">
        <span class="tag-white">Gestión académica</span>
        <h2>Mesas de examen e ingreso escalonado</h2>
      </div>
      <div class="mesas-grid">
        <div class="mesas-info">
          <h3>Mesas de examen</h3>
          <p>Los alumnos que adeudan materias o desean rendir en condición de libre deben inscribirse en los períodos habilitados. Existen tres períodos de examen por año.</p>
          <table class="mesa-table">
            <thead><tr><th>Período</th><th>Fechas</th><th>Inscripción hasta</th></tr></thead>
            <tbody>
              <tr><td><strong>Período de Mayo</strong></td><td>Completar fechas</td><td>Completar fecha</td></tr>
              <tr><td><strong>Período de Noviembre</strong></td><td>Completar fechas</td><td>Completar fecha</td></tr>
              <tr><td><strong>Período de Diciembre</strong></td><td>Completar fechas</td><td>Completar fecha</td></tr>
              <tr><td><strong>Período de Febrero</strong></td><td>Completar fechas</td><td>Completar fecha</td></tr>
            </tbody>
          </table>
          <div style="background:rgba(255,255,255,.05);border-radius:var(--radius);padding:.9rem 1.1rem;margin-top:1rem;font-size:.83rem;color:rgba(255,255,255,.65);">
            📌 La inscripción a mesas se realiza a través del formulario disponible en esta página o en secretaría técnica. Presentarse el día del examen con DNI y libreta escolar.
          </div>
        </div>
        <div class="ingreso-col">
          <h3>Ingreso escalonado</h3>
          <p>El ingreso escalonado es una modalidad de cursada que permite a los alumnos incorporarse a determinadas materias o etapas del ciclo técnico en momentos específicos del año. Esta información es gestionada por autorización de la dirección.</p>
          <p>Hacé clic en cada ítem para ver los detalles (acceso a información desplegable para usuarios autorizados):</p>

          <button class="ing-toggle" id="ing1">
            Criterios para el ingreso escalonado
            <svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <div class="ing-panel" id="panel-ing1">
            <p>Completar con los criterios institucionales que habilitan el ingreso escalonado: situación del alumno, materias que puede cursar, condiciones que debe cumplir, aval de la dirección, etc.</p>
          </div>

          <button class="ing-toggle" id="ing2">
            Procedimiento de solicitud
            <svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <div class="ing-panel" id="panel-ing2">
            <p>Completar con el procedimiento: formulario de solicitud, plazos, documentación requerida, instancias de aprobación (docente, dirección, preceptoría), y notificación al alumno y la familia.</p>
          </div>

          <button class="ing-toggle" id="ing3">
            Materias habilitadas para ingreso escalonado
            <svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <div class="ing-panel" id="panel-ing3">
            <p>Completar con el listado de materias del ciclo técnico que pueden cursarse bajo esta modalidad, y las que por su naturaleza práctica (taller) no admiten este formato.</p>
          </div>

          <button class="ing-toggle" id="ing4">
            Seguimiento y condición académica
            <svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <div class="ing-panel" id="panel-ing4">
            <p>Completar con cómo se hace el seguimiento del alumno bajo ingreso escalonado, cómo afecta a la condición regular o libre, y qué ocurre si no cumple con las condiciones establecidas.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- CONTACTO + TRABAJAR EN LA INSTITUCIÓN -->
  <div class="contact-strip">
    <h2>¿Querés ser parte de la Secundaria Técnica?</h2>
    <p>Para consultas sobre inscripciones, trabajar en la institución o visitar nuestros talleres, completá el formulario.</p>
    <div class="contact-btns">
      <a href="https://forms.google.com" target="_blank" rel="noopener" class="btn btn-white">Consultas e inscripciones</a>
      <a href="https://forms.google.com" target="_blank" rel="noopener" class="btn btn-outline">Trabajar en la institución</a>
      <a href="nivel-secundario.php" class="btn btn-dark">← Volver al selector</a>
    </div>
  </div>

  
<?php require __DIR__ . '/partials/footer.php';
