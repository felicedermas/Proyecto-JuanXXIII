<?php
// Página generada sobre partials/header.php + partials/footer.php.
// El menú se edita en partials/menu.php; los datos de contacto,
// desde el panel (Datos de contacto).
$page_title      = 'Deportes';
$page_desc       = 'Deportes en el Colegio Parroquial Juan XXIII: disciplinas, equipos, torneos e instalaciones.';
$nav_active      = 'comunidad';
$nav_active_link = 'deportes.php';
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


  .dep-hero { position: relative; background: var(--blue-dark); padding: 3.5rem 2rem 3rem; text-align: center; overflow: hidden; border-bottom: 3px solid var(--red); }
  .dep-hero::before { content:''; position:absolute; inset:0; background:
      radial-gradient(circle at 20% 30%, rgba(230,57,70,.18), transparent 40%),
      radial-gradient(circle at 85% 70%, rgba(168,218,220,.12), transparent 45%); }
  .dep-hero-inner { position: relative; max-width: 760px; margin: 0 auto; }
  .dep-eyebrow { font-size:.72rem; font-weight:800; letter-spacing:.22em; text-transform:uppercase; color:var(--blue-light); margin-bottom:.6rem; }
  .dep-hero h1 { font-family:var(--font-display); font-size:clamp(2rem,5vw,3rem); color:#fff; line-height:1.1; }
  .dep-hero h1 em { font-style:italic; color:#ffb3b8; }
  .dep-hero p { color:rgba(255,255,255,.65); margin-top:.9rem; font-size:1rem; }

  .dep-section { max-width: 1180px; margin: 0 auto; padding: 3.5rem 2rem 4.5rem; }
  .dep-intro { max-width:680px; margin:0 auto 2.75rem; text-align:center; color:#555; font-size:1.02rem; line-height:1.8; }
  .dep-intro strong { color:var(--blue-dark); }

  .dep-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:1.5rem; }
  .dep-card { position:relative; background:#fff; border-radius:var(--radius); overflow:hidden; box-shadow:var(--shadow-sm); border-top:4px solid var(--accent,#1D3557); transition:transform .3s var(--transition), box-shadow .3s; display:flex; flex-direction:column; }
  .dep-card:hover { transform:translateY(-6px); box-shadow:var(--shadow-md); }
  .dep-icon-wrap { font-size:2.4rem; width:64px; height:64px; display:flex; align-items:center; justify-content:center; border-radius:16px; margin:1.5rem 1.5rem 0; background:color-mix(in srgb, var(--accent) 12%, #fff); }
  .dep-body { padding:1.1rem 1.5rem 1.6rem; flex:1; display:flex; flex-direction:column; }
  .dep-card h3 { font-family:var(--font-display); font-size:1.3rem; color:var(--blue-dark); margin-bottom:.5rem; }
  .dep-card p { font-size:.88rem; color:#666; line-height:1.65; flex:1; }
  .dep-tags { display:flex; flex-wrap:wrap; gap:.4rem; margin-top:1rem; }
  .dep-tag { font-size:.66rem; font-weight:700; letter-spacing:.04em; text-transform:uppercase; color:var(--accent); background:color-mix(in srgb, var(--accent) 10%, #fff); padding:.25rem .6rem; border-radius:50px; }

  .dep-cta { margin-top:3.5rem; background:var(--gray-100); border-radius:var(--radius-lg); padding:2.5rem 2rem; text-align:center; }
  .dep-cta h2 { font-family:var(--font-display); font-size:1.6rem; color:var(--blue-dark); margin-bottom:.6rem; }
  .dep-cta p { color:#666; max-width:540px; margin:0 auto 1.4rem; }
  .dep-cta .btn { display:inline-block; background:var(--red); color:#fff; font-weight:800; padding:.85rem 1.8rem; border-radius:50px; transition:background .2s, transform .2s; }
  .dep-cta .btn:hover { background:var(--red-dark); transform:translateY(-2px); }
CSS;
require __DIR__ . '/partials/header.php';
?>
<section class="dep-hero">
  <div class="dep-hero-inner">
    <p class="dep-eyebrow">Vida deportiva</p>
    <h1>El deporte como <em>escuela de valores</em></h1>
    <p>En el Juan XXIII el deporte forma parte de la educación integral: trabajo en equipo, esfuerzo, respeto y salud.</p>
  </div>
</section>

<section class="dep-section">
  <p class="dep-intro">Ofrecemos una amplia variedad de <strong>disciplinas deportivas</strong> a lo largo de todos los niveles, tanto dentro de la currícula como en talleres y equipos representativos que compiten en encuentros intercolegiales.</p>

  <div class="dep-grid">
      <article class="dep-card" style="--accent:#1D3557">
        <div class="dep-icon-wrap">⚽</div>
        <div class="dep-body">
          <h3>Fútbol</h3>
          <p>Categorías masculinas y femeninas desde Nivel Inicial hasta Secundaria. Entrenamientos semanales y participación en torneos intercolegiales.</p>
          <div class="dep-tags"><span class="dep-tag">Mixto</span><span class="dep-tag">Torneos</span><span class="dep-tag">Todas las edades</span></div>
        </div>
      </article>
      <article class="dep-card" style="--accent:#E63946">
        <div class="dep-icon-wrap">🏐</div>
        <div class="dep-body">
          <h3>Vóley</h3>
          <p>Una de las disciplinas más fuertes del colegio. Equipos competitivos que representan a Juan XXIII en ligas zonales.</p>
          <div class="dep-tags"><span class="dep-tag">Femenino</span><span class="dep-tag">Masculino</span><span class="dep-tag">Competitivo</span></div>
        </div>
      </article>
      <article class="dep-card" style="--accent:#457B9D">
        <div class="dep-icon-wrap">🏀</div>
        <div class="dep-body">
          <h3>Básquet</h3>
          <p>Desarrollo de fundamentos técnicos y juego en equipo. Canchas propias y profesores especializados.</p>
          <div class="dep-tags"><span class="dep-tag">Primaria</span><span class="dep-tag">Secundaria</span></div>
        </div>
      </article>
      <article class="dep-card" style="--accent:#E63946">
        <div class="dep-icon-wrap">🤾</div>
        <div class="dep-body">
          <h3>Handball</h3>
          <p>Disciplina en crecimiento con gran convocatoria. Foco en el trabajo colaborativo y la coordinación.</p>
          <div class="dep-tags"><span class="dep-tag">Mixto</span><span class="dep-tag">Intercolegial</span></div>
        </div>
      </article>
      <article class="dep-card" style="--accent:#457B9D">
        <div class="dep-icon-wrap">🏊</div>
        <div class="dep-body">
          <h3>Natación</h3>
          <p>Convenio con natatorio para clases curriculares y escuela de iniciación deportiva durante todo el año.</p>
          <div class="dep-tags"><span class="dep-tag">Iniciación</span><span class="dep-tag">Curricular</span></div>
        </div>
      </article>
      <article class="dep-card" style="--accent:#1D3557">
        <div class="dep-icon-wrap">🏃</div>
        <div class="dep-body">
          <h3>Atletismo</h3>
          <p>Pruebas de pista y campo. Participación anual en los encuentros deportivos de escuelas parroquiales.</p>
          <div class="dep-tags"><span class="dep-tag">Pista</span><span class="dep-tag">Campo</span></div>
        </div>
      </article>
      <article class="dep-card" style="--accent:#E63946">
        <div class="dep-icon-wrap">🤸</div>
        <div class="dep-body">
          <h3>Gimnasia / Educación Física</h3>
          <p>Materia troncal en todos los niveles, orientada al desarrollo motriz, los hábitos saludables y el juego.</p>
          <div class="dep-tags"><span class="dep-tag">Curricular</span><span class="dep-tag">Todos los niveles</span></div>
        </div>
      </article>
      <article class="dep-card" style="--accent:#457B9D">
        <div class="dep-icon-wrap">♟️</div>
        <div class="dep-body">
          <h3>Ajedrez</h3>
          <p>Taller extracurricular que fomenta la concentración, la estrategia y el pensamiento lógico.</p>
          <div class="dep-tags"><span class="dep-tag">Taller</span><span class="dep-tag">Mixto</span></div>
        </div>
      </article>
  </div>

  <div class="dep-cta">
    <h2>¿Querés sumarte a un equipo?</h2>
    <p>Las inscripciones a las actividades deportivas se realizan al comienzo de cada ciclo lectivo. Consultá fechas y horarios en la Agenda del colegio.</p>
    <a href="agenda.php" class="btn">Ver Agenda deportiva</a>
  </div>
</section>
<?php require __DIR__ . '/partials/footer.php';
