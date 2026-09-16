<?php
// ============================================================
//  Colegio Parroquial Juan XXIII — Nivel Inicial (Jardín)
//  Página informativa + tablón de novedades filtrado a 'Inicial'
//  Requiere: XAMPP corriendo, base colegio_juan_xxiii
// ============================================================

require_once __DIR__ . '/conexion.php';
$pdo = db();

function ni_tiempo_relativo(string $fecha): string {
    $diff = time() - strtotime($fecha);
    if ($diff < 60)     return 'Recién';
    if ($diff < 3600)   return 'Hace ' . floor($diff/60) . ' min';
    if ($diff < 86400)  return 'Hace ' . floor($diff/3600) . ' h';
    if ($diff < 604800) return 'Hace ' . floor($diff/86400) . ' días';
    return date('d/m/Y', strtotime($fecha));
}

$page_title      = 'Nivel Inicial';
$page_desc       = 'Nivel Inicial del Colegio Parroquial Juan XXIII: salas de 3, 4 y 5 años. Propuesta lúdica y acompañamiento.';
$nav_active      = 'niveles';
$nav_active_link = 'nivel-inicial.php';
$page_style = <<<'CSS'
/* ============================================================
       NIVEL INICIAL — Estética infantil con manchas de pintura
       ============================================================ */
    :root{
      --ni-amarillo:#FFC93C;
      --ni-naranja:#FF8A5B;
      --ni-rosa:#FF6B9D;
      --ni-celeste:#4ECDC4;
      --ni-violeta:#9B5DE5;
      --ni-verde:#7DCE82;
      --ni-azul:#1D3557;
      --ni-rojo:#E63946;
      --ni-crema:#FFF8EE;
      --ni-fuente:'Baloo 2','Nunito',sans-serif;
    }

    body{ background:var(--ni-crema); }

    /* Fondo general con manchas de pintura suaves y lunares */
    .ni-paint-bg{
      position:fixed; inset:0; z-index:-2; overflow:hidden; pointer-events:none;
      background:
        radial-gradient(circle at 12% 18%, rgba(255,201,60,.18) 0 12%, transparent 12.5%),
        radial-gradient(circle at 88% 12%, rgba(78,205,196,.16) 0 10%, transparent 10.5%),
        radial-gradient(circle at 78% 72%, rgba(255,107,157,.14) 0 14%, transparent 14.5%),
        radial-gradient(circle at 8% 78%, rgba(155,93,229,.12) 0 11%, transparent 11.5%),
        radial-gradient(circle at 50% 45%, rgba(125,206,130,.10) 0 9%, transparent 9.5%);
    }
    /* Manchas de pintura tipo "blob" distribuidas */
    .ni-blob{ position:absolute; opacity:.5; filter:blur(.3px); }
    .ni-blob svg{ width:100%; height:100%; display:block; }

    /* ============================================================
       BOTÓN DE INSCRIPCIÓN FIJO — debajo del header, a la derecha
       ============================================================ */
    .ni-inscribite-fija{
      position:fixed;
      top:96px;            /* queda justo bajo el header de dos filas */
      right:24px;
      z-index:950;
      display:inline-flex; align-items:center; gap:.55rem;
      background:linear-gradient(135deg,var(--ni-rosa),var(--ni-naranja));
      color:#fff; font-family:var(--ni-fuente); font-weight:800; font-size:1rem;
      padding:.85rem 1.6rem; border-radius:50px;
      box-shadow:0 10px 26px rgba(255,107,157,.45);
      border:3px solid #fff;
      transition:transform .25s cubic-bezier(.34,1.56,.64,1), box-shadow .25s;
      animation:ni-bounce 2.4s ease-in-out infinite;
    }
    .ni-inscribite-fija:hover{
      transform:translateY(-3px) scale(1.05) rotate(-1deg);
      box-shadow:0 14px 32px rgba(255,107,157,.6);
    }
    .ni-inscribite-fija svg{ width:20px;height:20px;stroke:#fff;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round; }
    @keyframes ni-bounce{
      0%,100%{ transform:translateY(0); }
      50%{ transform:translateY(-6px); }
    }
    @media(max-width:768px){
      .ni-inscribite-fija{ top:auto; bottom:18px; right:16px; font-size:.92rem; padding:.75rem 1.3rem; }
    }

    /* ============================================================
       HERO INFANTIL
       ============================================================ */
    .ni-hero{
      position:relative; overflow:hidden;
      padding:4.5rem 2rem 6rem;
      text-align:center;
      background:
        radial-gradient(circle at 20% 30%, rgba(255,201,60,.25), transparent 40%),
        radial-gradient(circle at 80% 20%, rgba(78,205,196,.25), transparent 40%),
        radial-gradient(circle at 70% 85%, rgba(255,107,157,.22), transparent 45%),
        linear-gradient(180deg,#FFF8EE 0%, #FFEFD9 100%);
    }
    .ni-hero-inner{ position:relative; z-index:2; max-width:780px; margin:0 auto; }
    .ni-hero-pill{
      display:inline-flex; align-items:center; gap:.5rem;
      background:#fff; border:3px dashed var(--ni-celeste);
      color:var(--ni-azul); font-family:var(--ni-fuente); font-weight:700; font-size:.85rem;
      padding:.4rem 1.1rem; border-radius:50px; margin-bottom:1.4rem;
      box-shadow:var(--shadow-sm);
    }
    .ni-hero h1{
      font-family:var(--ni-fuente); font-weight:800;
      font-size:clamp(2.4rem,6vw,4.2rem); line-height:1.05; color:var(--ni-azul);
      margin-bottom:1rem; text-shadow:2px 2px 0 #fff;
    }
    .ni-hero h1 .crayon{ position:relative; white-space:nowrap; }
    .ni-hero h1 .c-rosa{ color:var(--ni-rosa); }
    .ni-hero h1 .c-celeste{ color:var(--ni-celeste); }
    .ni-hero h1 .c-amarillo{ color:var(--ni-amarillo); -webkit-text-stroke:1.5px #E0A800; }
    .ni-hero p{
      font-size:1.12rem; color:#5a5a6a; line-height:1.75; max-width:600px; margin:0 auto 2rem;
      font-weight:600;
    }
    .ni-hero-cta{ display:flex; gap:1rem; justify-content:center; flex-wrap:wrap; }
    .ni-btn-grande{
      display:inline-flex; align-items:center; gap:.6rem;
      font-family:var(--ni-fuente); font-weight:800; font-size:1.05rem;
      padding:.9rem 1.9rem; border-radius:50px; border:3px solid transparent;
      transition:transform .2s cubic-bezier(.34,1.56,.64,1), box-shadow .2s; cursor:pointer;
    }
    .ni-btn-rosa{ background:var(--ni-rosa); color:#fff; box-shadow:0 8px 20px rgba(255,107,157,.4); }
    .ni-btn-rosa:hover{ transform:translateY(-3px) scale(1.04); box-shadow:0 12px 26px rgba(255,107,157,.55); }
    .ni-btn-blanco{ background:#fff; color:var(--ni-azul); border-color:var(--ni-celeste); box-shadow:var(--shadow-sm); }
    .ni-btn-blanco:hover{ transform:translateY(-3px) scale(1.04); box-shadow:var(--shadow-md); }
    .ni-btn-grande svg{ width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round; }

    /* Globos / lápices flotando en el hero */
    .ni-hero-deco{ position:absolute; inset:0; z-index:1; pointer-events:none; }
    .ni-float{ position:absolute; animation:ni-floaty 6s ease-in-out infinite; }
    .ni-float:nth-child(2){ animation-delay:-1.5s; }
    .ni-float:nth-child(3){ animation-delay:-3s; }
    .ni-float:nth-child(4){ animation-delay:-4.5s; }
    @keyframes ni-floaty{ 0%,100%{ transform:translateY(0) rotate(-4deg);} 50%{ transform:translateY(-18px) rotate(4deg);} }

    /* Divisor ondulado entre secciones */
    .ni-wave{ display:block; width:100%; height:60px; }
    .ni-wave svg{ width:100%; height:100%; display:block; }

    /* ============================================================
       SECCIONES GENÉRICAS
       ============================================================ */
    .ni-sec{ position:relative; max-width:1180px; margin:0 auto; padding:4.5rem 2rem; }
    .ni-sec-titulo{ text-align:center; margin-bottom:3rem; }
    .ni-sec-titulo .ni-tag{
      display:inline-block; font-family:var(--ni-fuente); font-weight:700; font-size:.8rem;
      letter-spacing:.06em; text-transform:uppercase; padding:.35rem 1rem; border-radius:50px;
      background:#fff; box-shadow:var(--shadow-sm); margin-bottom:.9rem;
    }
    .ni-sec-titulo h2{
      font-family:var(--ni-fuente); font-weight:800; font-size:clamp(1.8rem,4vw,2.8rem);
      color:var(--ni-azul); line-height:1.15;
    }
    .ni-sec-titulo p{ color:#777; font-weight:600; max-width:560px; margin:.8rem auto 0; }

    /* ============================================================
       PROYECTO EDUCATIVO
       ============================================================ */
    .ni-proyecto{ display:grid; grid-template-columns:1.05fr 1fr; gap:3.5rem; align-items:center; }
    .ni-proyecto-text h2{
      font-family:var(--ni-fuente); font-weight:800; font-size:clamp(1.8rem,3.6vw,2.6rem);
      color:var(--ni-azul); line-height:1.15; margin-bottom:1.2rem;
    }
    .ni-proyecto-text p{ color:#5a5a6a; font-weight:600; line-height:1.8; margin-bottom:1rem; }
    .ni-pilares{ display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-top:1.8rem; }
    .ni-pilar{
      background:#fff; border-radius:20px; padding:1.3rem;
      box-shadow:var(--shadow-sm); transition:transform .25s, box-shadow .25s;
      border-bottom:5px solid var(--pc,var(--ni-rosa));
    }
    .ni-pilar:hover{ transform:translateY(-5px) rotate(-1deg); box-shadow:var(--shadow-md); }
    .ni-pilar .pi-emoji{ font-size:2rem; display:block; margin-bottom:.5rem; }
    .ni-pilar h4{ font-family:var(--ni-fuente); font-weight:700; color:var(--ni-azul); font-size:1.05rem; margin-bottom:.3rem; }
    .ni-pilar p{ font-size:.86rem; color:#777; font-weight:600; line-height:1.55; margin:0; }

    .ni-proyecto-visual{ position:relative; }
    .ni-proyecto-img{
      width:100%; aspect-ratio:4/5; border-radius:30px;
      background:linear-gradient(135deg,#FFE5B4,#FFD1DC 60%,#B5EAD7);
      display:flex; align-items:center; justify-content:center;
      font-size:5rem; box-shadow:var(--shadow-lg);
      border:6px solid #fff; transform:rotate(2deg);
    }
    .ni-proyecto-badge{
      position:absolute; bottom:-1.2rem; left:-1.2rem;
      background:var(--ni-amarillo); color:var(--ni-azul);
      font-family:var(--ni-fuente); font-weight:800;
      border-radius:20px; padding:1rem 1.3rem; text-align:center;
      box-shadow:var(--shadow-md); border:4px solid #fff; transform:rotate(-5deg);
    }
    .ni-proyecto-badge strong{ display:block; font-size:1.9rem; line-height:1; }
    .ni-proyecto-badge span{ font-size:.72rem; text-transform:uppercase; letter-spacing:.05em; }

    /* ============================================================
       BOTONES/TARJETAS ESPARCIDAS (accesos a secciones)
       ============================================================ */
    .ni-accesos{ display:grid; grid-template-columns:repeat(3,1fr); gap:1.5rem; }
    .ni-acceso{
      position:relative; display:flex; flex-direction:column; gap:.6rem;
      background:#fff; border-radius:26px; padding:2rem 1.6rem;
      box-shadow:var(--shadow-sm); text-decoration:none;
      transition:transform .25s cubic-bezier(.34,1.56,.64,1), box-shadow .25s;
      overflow:hidden; border:3px solid transparent;
    }
    .ni-acceso::before{
      content:''; position:absolute; top:-30px; right:-30px;
      width:110px; height:110px; border-radius:50%;
      background:var(--ac,var(--ni-rosa)); opacity:.16; transition:transform .3s;
    }
    .ni-acceso:hover{ transform:translateY(-6px); box-shadow:var(--shadow-lg); border-color:var(--ac,var(--ni-rosa)); }
    .ni-acceso:hover::before{ transform:scale(1.4); }
    .ni-acceso .ac-emoji{
      width:60px; height:60px; border-radius:18px;
      background:var(--ac,var(--ni-rosa)); color:#fff;
      display:flex; align-items:center; justify-content:center; font-size:1.9rem;
      box-shadow:0 8px 18px rgba(0,0,0,.12); position:relative; z-index:1;
    }
    .ni-acceso h3{ font-family:var(--ni-fuente); font-weight:800; color:var(--ni-azul); font-size:1.25rem; position:relative; z-index:1; }
    .ni-acceso p{ font-size:.9rem; color:#777; font-weight:600; line-height:1.55; position:relative; z-index:1; }
    .ni-acceso .ac-go{
      margin-top:auto; display:inline-flex; align-items:center; gap:.4rem;
      font-family:var(--ni-fuente); font-weight:800; font-size:.9rem; color:var(--ac,var(--ni-rosa));
      position:relative; z-index:1;
    }
    .ni-acceso .ac-go svg{ width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:3;stroke-linecap:round;stroke-linejoin:round;transition:transform .2s; }
    .ni-acceso:hover .ac-go svg{ transform:translateX(4px); }

    /* ============================================================
       ACTIVIDADES / TALLERES
       ============================================================ */
    .ni-talleres{ display:grid; grid-template-columns:repeat(4,1fr); gap:1.25rem; }
    .ni-taller{
      background:#fff; border-radius:24px; padding:1.6rem 1.25rem; text-align:center;
      box-shadow:var(--shadow-sm); transition:transform .25s, box-shadow .25s;
      border-top:6px solid var(--tc,var(--ni-celeste));
    }
    .ni-taller:hover{ transform:translateY(-6px) rotate(1deg); box-shadow:var(--shadow-md); }
    .ni-taller .t-emoji{ font-size:2.6rem; display:block; margin-bottom:.6rem; }
    .ni-taller h4{ font-family:var(--ni-fuente); font-weight:700; color:var(--ni-azul); font-size:1.05rem; margin-bottom:.3rem; }
    .ni-taller p{ font-size:.82rem; color:#777; font-weight:600; line-height:1.5; }
    .ni-taller .t-dia{
      display:inline-block; margin-top:.7rem; font-size:.72rem; font-weight:800;
      font-family:var(--ni-fuente); padding:.2rem .7rem; border-radius:50px;
      background:var(--tc,var(--ni-celeste)); color:#fff; opacity:.9;
    }

    /* Jornada extendida — panel destacado */
    .ni-jornada{
      margin-top:2.5rem;
      background:linear-gradient(120deg,var(--ni-violeta),var(--ni-rosa));
      border-radius:30px; padding:2.5rem; color:#fff;
      display:grid; grid-template-columns:1.2fr 1fr; gap:2rem; align-items:center;
      box-shadow:var(--shadow-lg); position:relative; overflow:hidden;
    }
    .ni-jornada::after{
      content:'🎨'; position:absolute; bottom:-20px; right:20px; font-size:8rem; opacity:.15;
    }
    .ni-jornada h3{ font-family:var(--ni-fuente); font-weight:800; font-size:1.7rem; margin-bottom:.7rem; }
    .ni-jornada p{ font-weight:600; line-height:1.7; opacity:.95; margin-bottom:1rem; }
    .ni-jornada-features{ display:flex; flex-direction:column; gap:.6rem; }
    .ni-jf{ display:flex; align-items:center; gap:.6rem; background:rgba(255,255,255,.18); border-radius:14px; padding:.7rem 1rem; font-weight:700; font-size:.92rem; }
    .ni-jf span{ font-size:1.2rem; }
    .ni-jornada-side{ background:rgba(255,255,255,.15); border-radius:22px; padding:1.6rem; text-align:center; border:2px dashed rgba(255,255,255,.4); }
    .ni-jornada-side .js-hora{ font-family:var(--ni-fuente); font-weight:800; font-size:2.2rem; line-height:1; }
    .ni-jornada-side .js-label{ font-size:.85rem; opacity:.9; font-weight:600; margin-top:.3rem; }

    /* ============================================================
       RECORRIDO VIRTUAL
       ============================================================ */
    .ni-recorrido{
      background:linear-gradient(135deg,#4ECDC4,#1D9D8F);
      border-radius:34px; padding:3rem; color:#fff;
      display:grid; grid-template-columns:1fr 1fr; gap:2.5rem; align-items:center;
      box-shadow:var(--shadow-lg); position:relative; overflow:hidden;
    }
    .ni-recorrido h2{ font-family:var(--ni-fuente); font-weight:800; font-size:2rem; margin-bottom:.8rem; }
    .ni-recorrido > div > p{ font-weight:600; line-height:1.7; opacity:.95; margin-bottom:1.4rem; }
    .ni-espacios{ display:grid; grid-template-columns:1fr 1fr; gap:.6rem; margin-bottom:1.6rem; }
    .ni-espacio{ display:flex; align-items:center; gap:.5rem; background:rgba(255,255,255,.18); border-radius:14px; padding:.6rem .9rem; font-weight:700; font-size:.86rem; }
    .ni-espacio span{ font-size:1.1rem; }
    .ni-tour-frame{
      aspect-ratio:4/3; border-radius:24px; background:rgba(0,0,0,.18);
      border:3px dashed rgba(255,255,255,.45);
      display:flex; flex-direction:column; align-items:center; justify-content:center;
      gap:.8rem; text-align:center; padding:2rem;
    }
    .ni-tour-frame .tf-icon{ width:74px;height:74px;border-radius:50%;background:rgba(255,255,255,.22);display:flex;align-items:center;justify-content:center;font-size:2.2rem; }
    .ni-tour-frame h4{ font-family:var(--ni-fuente); font-weight:700; font-size:1.15rem; }
    .ni-tour-frame p{ font-size:.82rem; opacity:.85; font-weight:600; line-height:1.5; margin:0; }
    .ni-tour-badge{ display:inline-block; background:var(--ni-amarillo); color:var(--ni-azul); font-family:var(--ni-fuente); font-weight:800; font-size:.72rem; text-transform:uppercase; letter-spacing:.08em; padding:.25rem .8rem; border-radius:50px; }

    /* ============================================================
       HORARIOS
       ============================================================ */
    .ni-horarios{ display:grid; grid-template-columns:1fr 1fr; gap:1.75rem; }
    .ni-horario{
      background:#fff; border-radius:26px; overflow:hidden; box-shadow:var(--shadow-sm); }
    .ni-horario-head{ padding:1.4rem 1.6rem; display:flex; align-items:center; gap:1rem; color:#fff; }
    .ni-horario-head.simple{ background:linear-gradient(120deg,var(--ni-celeste),#1D9D8F); }
    .ni-horario-head.extendida{ background:linear-gradient(120deg,var(--ni-violeta),var(--ni-rosa)); }
    .ni-horario-head .hh-emoji{ width:48px;height:48px;border-radius:14px;background:rgba(255,255,255,.22);display:flex;align-items:center;justify-content:center;font-size:1.6rem;flex-shrink:0; }
    .ni-horario-head h3{ font-family:var(--ni-fuente); font-weight:800; font-size:1.2rem; }
    .ni-horario-head p{ font-size:.82rem; opacity:.92; font-weight:600; margin:0; }
    .ni-horario-body{ padding:1.3rem 1.6rem; }
    .ni-horario-row{ display:flex; align-items:center; justify-content:space-between; padding:.65rem 0; border-bottom:1px dashed var(--gray-200); }
    .ni-horario-row:last-child{ border-bottom:none; }
    .ni-horario-row .hr-dia{ font-family:var(--ni-fuente); font-weight:700; color:var(--ni-azul); font-size:.92rem; }
    .ni-horario-row .hr-hora{ color:#666; font-weight:600; font-size:.86rem; }
    .ni-horario-row .hr-tag{ font-size:.68rem; font-weight:800; text-transform:uppercase; padding:.15rem .6rem; border-radius:50px; margin-left:.6rem; }
    .tag-ok{ background:rgba(125,206,130,.2); color:#3a8c3f; }
    .tag-opt{ background:rgba(255,138,91,.2); color:#c2410c; }

    .ni-horarios-extra{ display:grid; grid-template-columns:repeat(3,1fr); gap:1.25rem; margin-top:1.75rem; }
    .ni-hx{ background:#fff; border-radius:20px; padding:1.4rem; text-align:center; box-shadow:var(--shadow-sm); border-bottom:5px solid var(--hxc,var(--ni-amarillo)); }
    .ni-hx .hx-emoji{ font-size:1.9rem; display:block; margin-bottom:.4rem; }
    .ni-hx h4{ font-family:var(--ni-fuente); font-weight:700; color:var(--ni-azul); font-size:1rem; margin-bottom:.3rem; }
    .ni-hx p{ font-size:.83rem; color:#777; font-weight:600; line-height:1.5; }

    /* ============================================================
       TABLÓN DE NOVEDADES (filtrado a Inicial)
       ============================================================ */
    .ni-novedades-band{ background:linear-gradient(180deg,#FFEFD9,#FFF8EE); }
    .ni-nov-grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:1.5rem; }
    .ni-nov-card{
      background:#fff; border-radius:24px; overflow:hidden; box-shadow:var(--shadow-sm);
      transition:transform .25s, box-shadow .25s; text-decoration:none; display:flex; flex-direction:column;
      border-bottom:5px solid var(--ni-rojo);
    }
    .ni-nov-card:hover{ transform:translateY(-6px); box-shadow:var(--shadow-lg); }
    .ni-nov-card.destacada{ grid-column:1 / -1; flex-direction:row; }
    .ni-nov-img{ position:relative; aspect-ratio:16/10; background:linear-gradient(135deg,#FFE5B4,#FFD1DC); display:flex; align-items:center; justify-content:center; font-size:3rem; }
    .ni-nov-card.destacada .ni-nov-img{ flex:1; aspect-ratio:auto; min-height:240px; }
    .ni-nov-img img{ width:100%; height:100%; object-fit:cover; display:block; }
    .ni-nov-badge{ position:absolute; top:.8rem; left:.8rem; background:var(--ni-rojo); color:#fff; font-family:var(--ni-fuente); font-weight:800; font-size:.72rem; text-transform:uppercase; letter-spacing:.05em; padding:.25rem .8rem; border-radius:50px; }
    .ni-nov-body{ padding:1.4rem; flex:1; display:flex; flex-direction:column; }
    .ni-nov-card.destacada .ni-nov-body{ flex:1; justify-content:center; }
    .ni-nov-feat-label{ font-family:var(--ni-fuente); font-weight:800; font-size:.72rem; text-transform:uppercase; letter-spacing:.08em; color:var(--ni-rosa); margin-bottom:.4rem; }
    .ni-nov-body h3{ font-family:var(--ni-fuente); font-weight:700; color:var(--ni-azul); font-size:1.15rem; line-height:1.25; margin-bottom:.5rem; }
    .ni-nov-card.destacada .ni-nov-body h3{ font-size:1.6rem; }
    .ni-nov-body p{ font-size:.88rem; color:#777; font-weight:600; line-height:1.6; flex:1;
      display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; }
    .ni-nov-card.destacada .ni-nov-body p{ -webkit-line-clamp:4; }
    .ni-nov-foot{ display:flex; align-items:center; justify-content:space-between; margin-top:1rem; padding-top:.9rem; border-top:1px dashed var(--gray-200); }
    .ni-nov-fecha{ font-size:.78rem; color:var(--gray-500); font-weight:700; }
    .ni-nov-link{ font-family:var(--ni-fuente); font-weight:800; font-size:.82rem; color:var(--ni-rojo); display:inline-flex; align-items:center; gap:.3rem; }
    .ni-nov-link svg{ width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:3;stroke-linecap:round;stroke-linejoin:round; }

    .ni-nov-vacio{ text-align:center; background:#fff; border-radius:24px; padding:3rem; box-shadow:var(--shadow-sm); }
    .ni-nov-vacio .v-emoji{ font-size:3rem; margin-bottom:.5rem; }
    .ni-nov-vacio h3{ font-family:var(--ni-fuente); font-weight:700; color:var(--ni-azul); margin-bottom:.4rem; }
    .ni-nov-vacio p{ color:#888; font-weight:600; }
    .ni-nov-cta{ text-align:center; margin-top:2rem; }

    /* Instagram embebido */
    .ni-instagram{ margin-top:2.5rem; }
    .ni-ig-head{ display:flex; align-items:center; justify-content:center; gap:.6rem; margin-bottom:1.5rem; }
    .ni-ig-head h3{ font-family:var(--ni-fuente); font-weight:800; color:var(--ni-azul); font-size:1.3rem; }
    .ni-ig-head .ig-logo{ width:34px;height:34px;border-radius:10px;background:linear-gradient(45deg,#F58529,#DD2A7B,#8134AF);display:flex;align-items:center;justify-content:center; }
    .ni-ig-head .ig-logo svg{ width:20px;height:20px;stroke:#fff;fill:none;stroke-width:2; }
    .ni-ig-grid{ display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; }
    .ni-ig-post{ aspect-ratio:1; border-radius:18px; overflow:hidden; position:relative; background:linear-gradient(135deg,#FFD1DC,#B5EAD7); display:flex; align-items:center; justify-content:center; font-size:2.4rem; box-shadow:var(--shadow-sm); transition:transform .2s; cursor:pointer; }
    .ni-ig-post:hover{ transform:scale(1.04); }
    .ni-ig-post .ig-ov{ position:absolute; inset:0; background:rgba(29,53,87,.0); display:flex; align-items:center; justify-content:center; transition:background .2s; }
    .ni-ig-post:hover .ig-ov{ background:rgba(29,53,87,.35); }
    .ni-ig-note{ text-align:center; font-size:.82rem; color:#999; font-weight:600; margin-top:1.2rem; }

    /* ============================================================
       CIERRE / CTA FINAL
       ============================================================ */
    .ni-cierre{
      text-align:center; padding:4.5rem 2rem;
      background:linear-gradient(135deg,var(--ni-amarillo),var(--ni-naranja));
      position:relative; overflow:hidden;
    }
    .ni-cierre h2{ font-family:var(--ni-fuente); font-weight:800; font-size:clamp(1.8rem,4vw,2.6rem); color:var(--ni-azul); margin-bottom:.8rem; text-shadow:2px 2px 0 rgba(255,255,255,.4); }
    .ni-cierre p{ color:#6a4a1a; font-weight:700; max-width:540px; margin:0 auto 1.8rem; font-size:1.05rem; }
    .ni-cierre-btns{ display:flex; gap:1rem; justify-content:center; flex-wrap:wrap; }

    /* ============================================================
       RESPONSIVE
       ============================================================ */
    @media(max-width:1024px){
      .ni-proyecto{ grid-template-columns:1fr; gap:2.5rem; }
      .ni-accesos{ grid-template-columns:1fr 1fr; }
      .ni-talleres{ grid-template-columns:1fr 1fr; }
      .ni-jornada{ grid-template-columns:1fr; }
      .ni-recorrido{ grid-template-columns:1fr; }
      .ni-horarios{ grid-template-columns:1fr; }
      .ni-ig-grid{ grid-template-columns:repeat(2,1fr); }
      .ni-nov-card.destacada{ flex-direction:column; }
      .ni-nov-card.destacada .ni-nov-img{ min-height:200px; }
    }
    @media(max-width:768px){
      .ni-accesos{ grid-template-columns:1fr; }
      .ni-talleres{ grid-template-columns:1fr 1fr; }
      .ni-nov-grid{ grid-template-columns:1fr; }
      .ni-horarios-extra{ grid-template-columns:1fr; }
      .ni-pilares{ grid-template-columns:1fr; }
      .ni-sec{ padding:3rem 1.25rem; }
    }
  
  /* ===== HEADER DE DOS FILAS + DROPDOWN (igual al index, sticky resuelto) ===== */
  .nav-item.has-dropdown { position: relative; }
  .nav-item.has-dropdown::after { content:''; position:absolute; left:0; right:0; top:100%; height:.6rem; }
  .nav-dropdown-btn{ display:flex; align-items:center; gap:.45rem; padding:.55rem 1rem; border-radius:8px; color:rgba(255,255,255,.85); font-weight:600; font-size:.88rem; font-family:var(--font-body); background:none; border:none; cursor:pointer; transition:var(--transition); white-space:nowrap; }
  .nav-dropdown-btn:hover,.nav-item.has-dropdown.open .nav-dropdown-btn{ background:rgba(255,255,255,.12); color:#fff; }
  .nav-dropdown-btn.active-section{ background:rgba(255,255,255,.15); color:#fff; }
  .dropdown-chevron{ width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round;transition:transform .3s cubic-bezier(.4,0,.2,1);flex-shrink:0; }
  .nav-item.has-dropdown.open .dropdown-chevron{ transform:rotate(180deg); }
  .nav-dropdown{ position:absolute; top:calc(100% + .5rem); left:50%; transform:translateX(-50%) translateY(-6px); background:#1d3557; border:1px solid rgba(255,255,255,.12); border-radius:12px; padding:.5rem; min-width:250px; box-shadow:0 12px 40px rgba(0,0,0,.4); opacity:0; pointer-events:none; transition:opacity .22s ease, transform .22s cubic-bezier(.4,0,.2,1); z-index:200; }
  .nav-item.has-dropdown.open .nav-dropdown{ opacity:1; pointer-events:auto; transform:translateX(-50%) translateY(0); }
  @media (hover:hover) and (min-width:769px){
    .nav-item.has-dropdown:hover .nav-dropdown{ opacity:1; pointer-events:auto; transform:translateX(-50%) translateY(0); }
    .nav-item.has-dropdown:hover .nav-dropdown-btn{ background:rgba(255,255,255,.12); color:#fff; }
    .nav-item.has-dropdown:hover .dropdown-chevron{ transform:rotate(180deg); }
  }
  .nav-dropdown a{ display:flex; align-items:center; gap:.55rem; padding:.6rem .9rem; border-radius:8px; color:rgba(255,255,255,.8); font-size:.88rem; font-weight:600; font-family:var(--font-body); transition:background .18s,color .18s; white-space:nowrap; }
  .nav-dropdown a:hover{ background:rgba(255,255,255,.12); color:#fff; }
  .nav-dropdown a.active-link{ background:rgba(255,255,255,.18); color:#fff; }
  .nav-dropdown .dropdown-divider{ height:1px; background:rgba(255,255,255,.1); margin:.35rem .4rem; }
  .nav-dropdown a .dd-sub{ font-size:.72rem; font-weight:400; color:rgba(255,255,255,.45); display:block; margin-top:.05rem; }
  @media(max-width:768px){
    .nav-dropdown{ position:static; transform:none; opacity:1; pointer-events:auto; box-shadow:none; border:none; border-radius:0; background:rgba(0,0,0,.15); padding:0 0 0 1rem; max-height:0; overflow:hidden; transition:max-height .3s ease; }
    .nav-item.has-dropdown.open .nav-dropdown{ max-height:720px; }
  }
  /* Top-bar + dos filas */
  .topbar{ background:linear-gradient(90deg,var(--red) 0%,var(--red-dark) 35%,var(--blue-dark) 100%); color:rgba(255,255,255,.85); font-size:.8rem; }
  .topbar-inner{ max-width:1300px; margin:0 auto; padding:.45rem 2rem; display:flex; align-items:center; justify-content:flex-end; gap:1rem; }
  .topbar-quick{ display:flex; gap:.4rem; align-items:center; }
  .topbar-quick a{ display:inline-flex; align-items:center; gap:.35rem; color:rgba(255,255,255,.9); font-weight:600; padding:.25rem .7rem; border-radius:6px; transition:background .2s,color .2s; white-space:nowrap; }
  .topbar-quick a:hover{ background:rgba(255,255,255,.18); color:#fff; }
  .topbar-quick .tq-plataforma{ background:rgba(255,255,255,.16); color:#fff; border:1px solid rgba(255,255,255,.25); }
  .nav-item.mobile-only{ display:none; }
  .header-inner.two-row{ flex-direction:column; align-items:stretch; gap:0; padding:0 2rem; }
  .header-top-row{ display:flex; align-items:center; justify-content:flex-start; padding:.7rem 0; gap:1rem; }
  .two-row .brand{ gap:.85rem; }
  .two-row .logo{ width:72px; height:75px; }
  .two-row .school-name{ font-size:1.7rem; line-height:1.1; letter-spacing:-.01em; }
  .two-row .school-motto{ font-size:.78rem; }
  .main-nav.nav-row{ border-top:1px solid rgba(255,255,255,.1); }
  .nav-row .nav-list{ justify-content:center; gap:.15rem; flex-wrap:wrap; padding:.15rem 0; }
  .nav-row .nav-link,.nav-row .nav-dropdown-btn{ padding:.6rem .85rem; font-size:.87rem; }
  .two-row .header-top-row .hamburger{ margin-left:auto; }
  @media(max-width:900px){ .two-row .school-name{ font-size:1.45rem; } }
  @media(max-width:768px){
    .topbar{ display:none; }
    .header-inner.two-row{ padding:0; }
    .header-top-row{ padding:.6rem 1.2rem; }
    .two-row .school-name{ font-size:1.25rem; }
    .two-row .logo{ width:58px; height:60px; }
    .hamburger{ display:flex; }
    .main-nav.nav-row{ position:fixed; top:80px; left:0; right:0; background:var(--blue-dark); max-height:0; overflow:hidden; border-top:none; transition:max-height .4s cubic-bezier(.4,0,.2,1); box-shadow:var(--shadow-md); z-index:999; }
    .main-nav.nav-row.open{ max-height:80vh; overflow-y:auto; }
    .nav-row .nav-list{ flex-direction:column; align-items:stretch; padding:1rem; gap:.25rem; flex-wrap:nowrap; }
    .nav-item.mobile-only{ display:block; }
  }
  .site-header{ position:sticky; top:0; z-index:1000; background:var(--blue-dark); box-shadow:var(--shadow-md); }
  .site-header::after{ content:''; position:absolute; left:0; right:0; bottom:0; height:4px; background:linear-gradient(90deg,var(--red) 0%,var(--red-dark) 35%,var(--blue-dark) 100%); z-index:1001; }
CSS;
require __DIR__ . '/partials/header.php';
?>
  <!-- ===== BOTÓN DE INSCRIPCIÓN FIJO (debajo del header, derecha) ===== -->
  <a href="inscripcion-jardin.php" class="ni-inscribite-fija">
    <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/></svg>
    ¡Inscribite al Jardín!
  </a>

  <!-- ===== HERO INFANTIL ===== -->
  <section class="ni-hero">
    <div class="ni-hero-deco" aria-hidden="true">
      <div class="ni-float" style="top:18%;left:10%;font-size:2.6rem;">🎈</div>
      <div class="ni-float" style="top:30%;right:12%;font-size:2.4rem;">✏️</div>
      <div class="ni-float" style="bottom:22%;left:16%;font-size:2.2rem;">⭐</div>
      <div class="ni-float" style="bottom:30%;right:18%;font-size:2.6rem;">🧩</div>
    </div>
    <div class="ni-hero-inner">
      <span class="ni-hero-pill">🌈 Sala de 3, 4 y 5 años</span>
      <h1>Donde aprender es <span class="crayon c-rosa">jugar</span>,<br/>crear y <span class="crayon c-celeste">soñar</span> <span class="crayon c-amarillo">juntos</span></h1>
      <p>Un espacio cálido, seguro y lleno de color donde los más pequeños dan sus primeros pasos en el mundo del aprendizaje, acompañados con amor y dedicación.</p>
      <div class="ni-hero-cta">
        <a href="inscripcion-jardin.php" class="ni-btn-grande ni-btn-rosa">
          <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          Inscribite ahora
        </a>
        <a href="#proyecto" class="ni-btn-grande ni-btn-blanco">
          <svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          Nuestro proyecto
        </a>
      </div>
    </div>
  </section>

  <!-- onda divisoria -->
  <div class="ni-wave" style="margin-top:-1px;">
    <svg viewBox="0 0 1200 60" preserveAspectRatio="none"><path d="M0,30 C150,60 350,0 600,30 C850,60 1050,0 1200,30 L1200,60 L0,60 Z" fill="#FFF8EE"/></svg>
  </div>

  <!-- ===== PROYECTO EDUCATIVO ===== -->
  <section id="proyecto" class="ni-sec">
    <div class="ni-proyecto">
      <div class="ni-proyecto-text">
        <span class="ni-sec-titulo ni-tag" style="color:var(--ni-rosa);">Nuestro proyecto educativo</span>
        <h2>Cada niño es único, y así lo acompañamos</h2>
        <p>En el Nivel Inicial del Colegio Juan XXIII creemos que los primeros años son los cimientos de toda la vida. Por eso trabajamos con una <strong>pedagogía activa</strong> donde el juego es el motor del aprendizaje, integrando los valores del Evangelio en un ambiente de afecto y respeto.</p>
        <p>Nuestra propuesta integra las áreas artística, motriz, lingüística, científica y socio-emocional en proyectos significativos para la etapa de 3 a 5 años, acompañando a cada niño de forma personalizada según su ritmo.</p>
        <div class="ni-pilares">
          <div class="ni-pilar" style="--pc:var(--ni-rosa);">
            <span class="pi-emoji">🎨</span>
            <h4>Creatividad</h4>
            <p>El arte y la expresión como lenguajes propios de la infancia.</p>
          </div>
          <div class="ni-pilar" style="--pc:var(--ni-celeste);">
            <span class="pi-emoji">🤝</span>
            <h4>Vínculos</h4>
            <p>Aprendizaje en comunidad y desarrollo socio-emocional.</p>
          </div>
          <div class="ni-pilar" style="--pc:var(--ni-amarillo);">
            <span class="pi-emoji">🔍</span>
            <h4>Curiosidad</h4>
            <p>Exploración, preguntas y descubrimiento del entorno.</p>
          </div>
          <div class="ni-pilar" style="--pc:var(--ni-violeta);">
            <span class="pi-emoji">💛</span>
            <h4>Valores</h4>
            <p>Respeto, solidaridad y amor en cada momento del día.</p>
          </div>
        </div>
      </div>
      <div class="ni-proyecto-visual">
        <div class="ni-proyecto-img">🧸</div>
        <div class="ni-proyecto-badge">
          <strong>3 a 5</strong>
          <span>años de edad</span>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== ACCESOS ESPARCIDOS ===== -->
  <div style="background:linear-gradient(180deg,#FFF8EE,#FFEFD9);">
    <section class="ni-sec">
      <div class="ni-sec-titulo">
        <span class="ni-tag" style="color:var(--ni-celeste);">Explorá el jardín</span>
        <h2>Todo lo que querés saber 🧭</h2>
        <p>Tocá cada tarjeta para descubrir nuestras actividades, espacios, horarios y novedades.</p>
      </div>
      <div class="ni-accesos">
        <a href="#actividades" class="ni-acceso" style="--ac:var(--ni-rosa);">
          <div class="ac-emoji">🎭</div>
          <h3>Actividades y Talleres</h3>
          <p>Música, arte, inglés, expresión corporal y mucho más para cada sala.</p>
          <span class="ac-go">Ver actividades <svg viewBox="0 0 24 24"><polyline points="9 6 15 12 9 18"/></svg></span>
        </a>
        <a href="#jornada" class="ni-acceso" style="--ac:var(--ni-violeta);">
          <div class="ac-emoji">🕐</div>
          <h3>Jornada Extendida</h3>
          <p>Opción de turno completo con almuerzo, descanso y talleres especiales.</p>
          <span class="ac-go">Conocer más <svg viewBox="0 0 24 24"><polyline points="9 6 15 12 9 18"/></svg></span>
        </a>
        <a href="#recorrido" class="ni-acceso" style="--ac:var(--ni-celeste);">
          <div class="ac-emoji">🏫</div>
          <h3>Recorrido Virtual</h3>
          <p>Conocé nuestras aulas, el SUM y el patio de juegos desde casa.</p>
          <span class="ac-go">Hacer el tour <svg viewBox="0 0 24 24"><polyline points="9 6 15 12 9 18"/></svg></span>
        </a>
        <a href="#horarios" class="ni-acceso" style="--ac:var(--ni-amarillo);">
          <div class="ac-emoji">📅</div>
          <h3>Horarios</h3>
          <p>Jornada simple y extendida, ingreso, salida, comedor y transporte.</p>
          <span class="ac-go">Ver horarios <svg viewBox="0 0 24 24"><polyline points="9 6 15 12 9 18"/></svg></span>
        </a>
        <a href="#novedades" class="ni-acceso" style="--ac:var(--ni-rojo);">
          <div class="ac-emoji">📣</div>
          <h3>Novedades del Jardín</h3>
          <p>Enterate de los últimos eventos, actos y comunicados del Nivel Inicial.</p>
          <span class="ac-go">Ver novedades <svg viewBox="0 0 24 24"><polyline points="9 6 15 12 9 18"/></svg></span>
        </a>
        <a href="inscripcion-jardin.php" class="ni-acceso" style="--ac:var(--ni-verde);">
          <div class="ac-emoji">✏️</div>
          <h3>Inscripciones</h3>
          <p>¿Listos para empezar? Inscribí a tu hijo o hija en pocos pasos.</p>
          <span class="ac-go">Inscribirse <svg viewBox="0 0 24 24"><polyline points="9 6 15 12 9 18"/></svg></span>
        </a>
      </div>
    </section>
  </div>

  <!-- ===== ACTIVIDADES Y TALLERES ===== -->
  <section id="actividades" class="ni-sec">
    <div class="ni-sec-titulo">
      <span class="ni-tag" style="color:var(--ni-rosa);">Actividades extracurriculares</span>
      <h2>Talleres para crecer jugando 🎨</h2>
      <p>Cada semana, nuestros chicos descubren nuevas formas de expresarse y aprender. Estos talleres están incluidos en la propuesta del jardín.</p>
    </div>
    <div class="ni-talleres">
      <div class="ni-taller" style="--tc:var(--ni-rosa);">
        <span class="t-emoji">🎵</span>
        <h4>Música y Ritmo</h4>
        <p>Canciones, instrumentos y movimiento para desarrollar el oído y la expresión.</p>
        <span class="t-dia">Lunes</span>
      </div>
      <div class="ni-taller" style="--tc:var(--ni-celeste);">
        <span class="t-emoji">🌍</span>
        <h4>Inglés Lúdico</h4>
        <p>Primer acercamiento al idioma a través de juegos, cuentos y canciones.</p>
        <span class="t-dia">Martes</span>
      </div>
      <div class="ni-taller" style="--tc:var(--ni-amarillo);">
        <span class="t-emoji">🤸</span>
        <h4>Educación Física</h4>
        <p>Juegos motrices que desarrollan coordinación, equilibrio y trabajo en equipo.</p>
        <span class="t-dia">Miércoles</span>
      </div>
      <div class="ni-taller" style="--tc:var(--ni-violeta);">
        <span class="t-emoji">🎭</span>
        <h4>Expresión y Teatro</h4>
        <p>Dramatización y juego de roles para la creatividad y la autoestima.</p>
        <span class="t-dia">Jueves</span>
      </div>
      <div class="ni-taller" style="--tc:var(--ni-verde);">
        <span class="t-emoji">🌱</span>
        <h4>Huerta Escolar</h4>
        <p>Sembrar, cuidar y cosechar para aprender sobre la naturaleza y el cuidado.</p>
        <span class="t-dia">Viernes</span>
      </div>
      <div class="ni-taller" style="--tc:var(--ni-naranja);">
        <span class="t-emoji">🖌️</span>
        <h4>Taller de Plástica</h4>
        <p>Pintura, modelado y collage para explorar texturas, colores y formas.</p>
        <span class="t-dia">Semanal</span>
      </div>
      <div class="ni-taller" style="--tc:var(--ni-celeste);">
        <span class="t-emoji">📖</span>
        <h4>Hora del Cuento</h4>
        <p>Narración de cuentos para despertar el amor por la lectura desde pequeños.</p>
        <span class="t-dia">Diaria</span>
      </div>
      <div class="ni-taller" style="--tc:var(--ni-rosa);">
        <span class="t-emoji">🧩</span>
        <h4>Juego y Lógica</h4>
        <p>Rompecabezas y juegos de mesa que estimulan el pensamiento y la atención.</p>
        <span class="t-dia">Semanal</span>
      </div>
    </div>

    <!-- Jornada extendida -->
    <div id="jornada" class="ni-jornada">
      <div>
        <h3>🌟 Jornada Extendida Optativa</h3>
        <p>Para las familias que necesitan un acompañamiento de día completo, ofrecemos la jornada extendida: una propuesta opcional con almuerzo, descanso y talleres especiales por la tarde.</p>
        <div class="ni-jornada-features">
          <div class="ni-jf"><span>🍽️</span> Almuerzo con menú elaborado por nutricionista</div>
          <div class="ni-jf"><span>😴</span> Espacio de descanso y siesta (sala de 3)</div>
          <div class="ni-jf"><span>🎨</span> Talleres especiales de arte, cocina y juego</div>
        </div>
      </div>
      <div class="ni-jornada-side">
        <div class="js-hora">8 a 16:30</div>
        <div class="js-label">hs · Lunes a Viernes</div>
        <div style="margin-top:1.2rem;font-size:.85rem;font-weight:700;background:rgba(255,255,255,.2);border-radius:12px;padding:.6rem;">Cupos limitados — consultá en Secretaría</div>
      </div>
    </div>
  </section>

  <!-- onda divisoria -->
  <div class="ni-wave">
    <svg viewBox="0 0 1200 60" preserveAspectRatio="none"><path d="M0,30 C150,0 350,60 600,30 C850,0 1050,60 1200,30 L1200,60 L0,60 Z" fill="#fff"/></svg>
  </div>

  <!-- ===== RECORRIDO VIRTUAL ===== -->
  <section id="recorrido" class="ni-sec">
    <div class="ni-recorrido">
      <div>
        <span class="ni-tour-badge">Conocé nuestros espacios</span>
        <h2 style="margin-top:.8rem;">Recorré el jardín sin salir de casa 🏫</h2>
        <p>Visitá nuestras aulas, el SUM, la biblioteca infantil y el patio de juegos, todos los espacios pensados y diseñados especialmente para los más pequeños.</p>
        <div class="ni-espacios">
          <div class="ni-espacio"><span>🏫</span> Aulas de sala 3, 4 y 5</div>
          <div class="ni-espacio"><span>📚</span> Biblioteca infantil</div>
          <div class="ni-espacio"><span>🤸</span> Patio de juegos</div>
          <div class="ni-espacio"><span>🎨</span> Taller de plástica</div>
          <div class="ni-espacio"><span>🎵</span> Sala de música</div>
          <div class="ni-espacio"><span>🌱</span> Huerta escolar</div>
        </div>
        <a href="recorrido-360.php" class="ni-btn-grande ni-btn-blanco" style="border-color:#fff;">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
          Iniciar recorrido 360°
        </a>
      </div>
      <div class="ni-tour-frame">
        <div class="tf-icon">🎥</div>
        <span class="ni-tour-badge">Próximamente</span>
        <h4>Tour Virtual 360°</h4>
        <p>Acá irá el recorrido interactivo del jardín.<br/>Reemplazá este bloque con el embed de tu plataforma 360° (Matterport, Google Street View, etc.).</p>
      </div>
    </div>
  </section>

  <!-- ===== HORARIOS ===== -->
  <div style="background:linear-gradient(180deg,#fff,#FFEFD9);">
    <section id="horarios" class="ni-sec">
      <div class="ni-sec-titulo">
        <span class="ni-tag" style="color:var(--ni-violeta);">Organización del día</span>
        <h2>Nuestros horarios 🕐</h2>
        <p>Dos jornadas para adaptarnos a cada familia. Ambas incluyen recreos, talleres y mucho juego.</p>
      </div>
      <div class="ni-horarios">
        <div class="ni-horario">
          <div class="ni-horario-head simple">
            <div class="hh-emoji">☀️</div>
            <div><h3>Jornada Simple</h3><p>Turno mañana</p></div>
          </div>
          <div class="ni-horario-body">
            <div class="ni-horario-row"><span class="hr-dia">Lunes a Viernes</span><span class="hr-hora">8:00 – 12:30 hs</span><span class="hr-tag tag-ok">Regular</span></div>
            <div class="ni-horario-row"><span class="hr-dia">Ingreso</span><span class="hr-hora">7:45 – 8:10 hs</span></div>
            <div class="ni-horario-row"><span class="hr-dia">Recreo / Merienda</span><span class="hr-hora">10:00 – 10:30 hs</span></div>
            <div class="ni-horario-row"><span class="hr-dia">Salida</span><span class="hr-hora">12:15 – 12:30 hs</span></div>
            <div class="ni-horario-row"><span class="hr-dia">Talleres</span><span class="hr-hora">Incluidos</span><span class="hr-tag tag-ok">Incluido</span></div>
          </div>
        </div>
        <div class="ni-horario">
          <div class="ni-horario-head extendida">
            <div class="hh-emoji">🌙</div>
            <div><h3>Jornada Extendida</h3><p>Turno completo con almuerzo</p></div>
          </div>
          <div class="ni-horario-body">
            <div class="ni-horario-row"><span class="hr-dia">Lunes a Viernes</span><span class="hr-hora">8:00 – 16:30 hs</span><span class="hr-tag tag-opt">Optativa</span></div>
            <div class="ni-horario-row"><span class="hr-dia">Almuerzo</span><span class="hr-hora">12:30 – 13:30 hs</span></div>
            <div class="ni-horario-row"><span class="hr-dia">Descanso / Siesta</span><span class="hr-hora">13:30 – 14:30 hs</span></div>
            <div class="ni-horario-row"><span class="hr-dia">Talleres de tarde</span><span class="hr-hora">14:30 – 16:00 hs</span></div>
            <div class="ni-horario-row"><span class="hr-dia">Salida</span><span class="hr-hora">16:15 – 16:30 hs</span></div>
          </div>
        </div>
      </div>
      <div class="ni-horarios-extra">
        <div class="ni-hx" style="--hxc:var(--ni-amarillo);">
          <span class="hx-emoji">📅</span>
          <h4>Actos escolares</h4>
          <p>Los actos y eventos se informan con anticipación por el cuaderno de comunicaciones.</p>
        </div>
        <div class="ni-hx" style="--hxc:var(--ni-rosa);">
          <span class="hx-emoji">🍽️</span>
          <h4>Servicio de comedor</h4>
          <p>Menú equilibrado elaborado por nutricionista, adaptable a alergias o restricciones.</p>
        </div>
        <div class="ni-hx" style="--hxc:var(--ni-celeste);">
          <span class="hx-emoji">🚌</span>
          <h4>Transporte escolar</h4>
          <p>Listado de transportistas habilitados de la zona disponible en Secretaría.</p>
        </div>
      </div>
    </section>
  </div>

  <!-- onda divisoria -->
  <div class="ni-wave">
    <svg viewBox="0 0 1200 60" preserveAspectRatio="none"><path d="M0,30 C150,60 350,0 600,30 C850,60 1050,0 1200,30 L1200,60 L0,60 Z" fill="#FFEFD9"/></svg>
  </div>

  <!-- ===== TABLÓN DE NOVEDADES (solo Inicial) ===== -->
  <div id="novedades" class="ni-novedades-band">
    <section class="ni-sec">
      <div class="ni-sec-titulo">
        <span class="ni-tag" style="color:var(--ni-rojo);">Tablón del Jardín</span>
        <h2>Novedades del Nivel Inicial 📣</h2>
        <p>Eventos, actos y comunicados de nuestra salita. Estas publicaciones se cargan desde el panel del colegio.</p>
      </div>

      <?php if (!$db_ok): ?>
        <div class="ni-nov-vacio">
          <div class="v-emoji">🔌</div>
          <h3>Tablón no disponible</h3>
          <p>No se pudo conectar con la base de datos. Verificá que XAMPP/MySQL esté corriendo y la base <strong>colegio_juan_xxiii</strong> exista.</p>
        </div>
      <?php elseif (empty($novedades)): ?>
        <div class="ni-nov-vacio">
          <div class="v-emoji">📭</div>
          <h3>Todavía no hay novedades del jardín</h3>
          <p>Cuando se publiquen comunicados del Nivel Inicial, aparecerán acá automáticamente.</p>
        </div>
      <?php else:
        $destacada = $novedades[0];
        $resto     = array_slice($novedades, 1);
        $d_imgs    = $imagenes_por_novedad[$destacada['id_novedad']] ?? [];
        $d_primera = $d_imgs[0] ?? null;
      ?>
        <div class="ni-nov-grid">
          <!-- Destacada -->
          <a href="novedad.php?id=<?= $destacada['id_novedad'] ?>" class="ni-nov-card destacada">
            <div class="ni-nov-img">
              <span class="ni-nov-badge">Inicial</span>
              <?php if ($d_primera): ?>
                <img src="<?= htmlspecialchars($d_primera) ?>" alt="<?= htmlspecialchars($destacada['titulo']) ?>" loading="lazy"/>
              <?php else: ?>🎨<?php endif; ?>
            </div>
            <div class="ni-nov-body">
              <span class="ni-nov-feat-label">★ Última publicación</span>
              <h3><?= htmlspecialchars($destacada['titulo']) ?></h3>
              <p><?= htmlspecialchars($destacada['descripcion']) ?></p>
              <div class="ni-nov-foot">
                <span class="ni-nov-fecha"><?= ni_tiempo_relativo($destacada['created_at']) ?></span>
                <span class="ni-nov-link">Leer más <svg viewBox="0 0 24 24"><polyline points="9 6 15 12 9 18"/></svg></span>
              </div>
            </div>
          </a>

          <!-- Resto -->
          <?php foreach ($resto as $nov):
            $imgs    = $imagenes_por_novedad[$nov['id_novedad']] ?? [];
            $primera = $imgs[0] ?? null;
          ?>
          <a href="novedad.php?id=<?= $nov['id_novedad'] ?>" class="ni-nov-card">
            <div class="ni-nov-img">
              <span class="ni-nov-badge">Inicial</span>
              <?php if ($primera): ?>
                <img src="<?= htmlspecialchars($primera) ?>" alt="<?= htmlspecialchars($nov['titulo']) ?>" loading="lazy"/>
              <?php else: ?>🖼️<?php endif; ?>
            </div>
            <div class="ni-nov-body">
              <h3><?= htmlspecialchars($nov['titulo']) ?></h3>
              <p><?= htmlspecialchars($nov['descripcion']) ?></p>
              <div class="ni-nov-foot">
                <span class="ni-nov-fecha"><?= ni_tiempo_relativo($nov['created_at']) ?></span>
                <span class="ni-nov-link">Leer <svg viewBox="0 0 24 24"><polyline points="9 6 15 12 9 18"/></svg></span>
              </div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="ni-nov-cta">
        <a href="novedades.php?etiqueta=Inicial" class="ni-btn-grande ni-btn-rosa">
          Ver todas las novedades del jardín
          <svg viewBox="0 0 24 24"><polyline points="9 6 15 12 9 18"/></svg>
        </a>
      </div>

      <!-- ===== INSTAGRAM ===== -->
      <div class="ni-instagram">
        <div class="ni-ig-head">
          <div class="ig-logo"><svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><line x1="17.5" y1="6.5" x2="17.5" y2="6.5"/></svg></div>
          <h3>Seguinos en Instagram</h3>
        </div>
        <div class="ni-ig-grid" id="igGrid">
          <!-- Placeholders: se reemplazan dinámicamente vía Instagram Graph API -->
          <div class="ni-ig-post">🎨<div class="ig-ov"></div></div>
          <div class="ni-ig-post">🧩<div class="ig-ov"></div></div>
          <div class="ni-ig-post">🎈<div class="ig-ov"></div></div>
          <div class="ni-ig-post">🌟<div class="ig-ov"></div></div>
        </div>
        <p class="ni-ig-note">📸 Galería conectada a <strong>@juanxxiii.inicial</strong> — las publicaciones se sincronizan automáticamente desde la API de Instagram.</p>
      </div>
    </section>
  </div>

  <!-- ===== CIERRE / CTA FINAL ===== -->
  <section class="ni-cierre">
    <h2>¿Listos para esta aventura? 🚀</h2>
    <p>Te invitamos a formar parte de nuestra familia. Inscribí a tu hijo o hija y empecemos juntos este hermoso camino.</p>
    <div class="ni-cierre-btns">
      <a href="inscripcion-jardin.php" class="ni-btn-grande ni-btn-rosa">
        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        Inscribirse al Jardín
      </a>
      <a href="contacto.php" class="ni-btn-grande ni-btn-blanco">
        <svg viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
        Hacé tu consulta
      </a>
    </div>
  </section>

  <!-- ===== FOOTER ===== -->
  
<?php require __DIR__ . '/partials/footer.php';
