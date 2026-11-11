<?php
// Página generada sobre partials/header.php + partials/footer.php.
// El menú se edita en partials/menu.php; los datos de contacto,
// desde el panel (Datos de contacto).
$page_title      = 'Deportes';
$page_desc       = 'Deportes en el Colegio Parroquial Juan XXIII: patín.';
$nav_active      = 'comunidad';
$nav_active_link = 'deportes';
$page_style = <<<'CSS'
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

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

  .dep-grid--uno { max-width:420px; margin:0 auto; grid-template-columns:1fr !important; }
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
  <p class="dep-intro">Hoy la disciplina deportiva que ofrece el colegio es <strong>patín</strong>.</p>

  <div class="dep-grid dep-grid--uno">
      <article class="dep-card" style="--accent:#E63946">
        <div class="dep-icon-wrap">⛸️</div>
        <div class="dep-body">
          <h3>Patín</h3>
          <p>Días, horarios y edades: a confirmar. Consultá en Secretaría.</p>
        </div>
      </article>
  </div>

  <div class="dep-cta">
    <h2>¿Querés sumarte a patín?</h2>
    <p>Escribinos o llamanos y te pasamos la información de inscripción.</p>
    <a href="contacto" class="btn">Hacer una consulta</a>
  </div>
</section>
<?php require __DIR__ . '/partials/footer.php';
