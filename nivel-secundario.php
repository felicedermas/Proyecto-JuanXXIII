<?php
// Selector de modalidad de Secundaria.
// Antes era una pantalla completa sin header (no se podía navegar desde acá).
// Ahora usa el header común del sitio y las dos opciones son tarjetas
// comparables: mismo alto, misma jerarquía, la diferencia está en el color.
$page_title      = 'Nivel Secundario';
$page_desc       = 'Secundaria del Colegio Parroquial Juan XXIII: Bachillerato Orientado (6 años) y Educación Técnica (7 años). Conocé las dos modalidades.';
$nav_active      = 'niveles';
$nav_active_link = 'nivel-secundario.php';
$page_style = <<<'CSS'
  /* ── Selector de modalidad ── */
  .sel-wrap { max-width: 1180px; margin: 0 auto; padding: 3.5rem 2rem 4.5rem; }

  .sel-opciones {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.6rem;
    align-items: stretch;
  }

  .sel-card {
    position: relative;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border-radius: 20px;
    background: #fff;
    border: 1px solid rgba(29,53,87,.1);
    box-shadow: 0 2px 14px rgba(29,53,87,.07);
    text-decoration: none;
    color: inherit;
    transition: transform .28s cubic-bezier(.4,0,.2,1),
                box-shadow .28s cubic-bezier(.4,0,.2,1),
                border-color .28s;
  }
  .sel-card:hover, .sel-card:focus-visible {
    transform: translateY(-6px);
    box-shadow: 0 22px 48px rgba(29,53,87,.2);
    border-color: transparent;
  }
  .sel-card:focus-visible { outline: 3px solid var(--red); outline-offset: 3px; }

  /* Franja superior con el color de la modalidad */
  .sel-top {
    position: relative;
    padding: 2.1rem 2rem 1.6rem;
    color: #fff;
    overflow: hidden;
  }
  .sel-orientada .sel-top { background: linear-gradient(135deg, #1D3557 0%, #457B9D 100%); }
  .sel-tecnica   .sel-top { background: linear-gradient(135deg, #0d1b2a 0%, #1D3557 55%, #6d2233 100%); }
  /* Textura sutil, para que no sea un degradado plano */
  .sel-top::after {
    content: '';
    position: absolute; inset: 0;
    background:
      radial-gradient(circle at 88% 12%, rgba(255,255,255,.16) 0%, transparent 46%),
      radial-gradient(circle at 12% 96%, rgba(255,255,255,.08) 0%, transparent 42%);
    pointer-events: none;
  }
  .sel-top > * { position: relative; z-index: 1; }

  .sel-encabezado {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 1rem; margin-bottom: 1.4rem;
  }
  .sel-tag {
    display: inline-block;
    font-size: .66rem; font-weight: 800; letter-spacing: .14em;
    text-transform: uppercase;
    background: rgba(255,255,255,.18);
    border: 1px solid rgba(255,255,255,.28);
    padding: .3rem .8rem; border-radius: 999px;
  }
  .sel-anios {
    display: flex; flex-direction: column; align-items: center;
    line-height: 1; flex-shrink: 0;
  }
  .sel-anios b {
    font-family: var(--font-display);
    font-size: 2.6rem; font-weight: 900; letter-spacing: -.03em;
  }
  .sel-anios span {
    font-size: .62rem; text-transform: uppercase;
    letter-spacing: .16em; opacity: .75; margin-top: .15rem;
  }

  .sel-titulo {
    font-family: var(--font-display);
    font-size: 2.05rem; font-weight: 700; line-height: 1.1;
    letter-spacing: -.02em;
  }
  .sel-titulo em { font-style: italic; display: block; font-weight: 900; }

  /* Cuerpo blanco */
  .sel-cuerpo {
    display: flex; flex-direction: column; flex: 1;
    padding: 1.6rem 2rem 1.9rem;
  }
  .sel-detalle {
    color: var(--gray-600, #555);
    font-size: .93rem; line-height: 1.65;
    margin-bottom: 1.2rem;
  }
  .sel-lista {
    display: flex; flex-direction: column; gap: .55rem;
    margin-bottom: 1.6rem;
  }
  .sel-lista li {
    display: flex; align-items: center; gap: .6rem;
    font-size: .89rem; color: var(--blue-dark); font-weight: 600;
  }
  .sel-lista svg {
    width: 17px; height: 17px; flex-shrink: 0;
    fill: none; stroke-width: 2.6; stroke-linecap: round; stroke-linejoin: round;
  }
  .sel-orientada .sel-lista svg { stroke: #457B9D; }
  .sel-tecnica   .sel-lista svg { stroke: var(--red); }

  .sel-cta {
    margin-top: auto;
    display: inline-flex; align-items: center; justify-content: center;
    gap: .55rem;
    padding: .9rem 1.4rem;
    border-radius: 12px;
    font-weight: 800; font-size: .93rem;
    color: #fff;
    transition: background .22s, gap .22s;
  }
  .sel-orientada .sel-cta { background: #457B9D; }
  .sel-orientada:hover .sel-cta { background: #1D3557; }
  .sel-tecnica   .sel-cta { background: var(--red); }
  .sel-tecnica:hover .sel-cta { background: var(--red-dark, #a01b27); }
  .sel-card:hover .sel-cta { gap: .9rem; }
  .sel-cta svg {
    width: 17px; height: 17px; fill: none; stroke: currentColor;
    stroke-width: 2.6; stroke-linecap: round; stroke-linejoin: round;
  }

  .sel-pie {
    margin-top: 2.6rem; text-align: center;
    font-size: .9rem; color: var(--gray-500);
  }
  .sel-pie a { color: var(--blue-mid); font-weight: 700; }

  @media (max-width: 820px) {
    .sel-opciones { grid-template-columns: 1fr; gap: 1.2rem; }
    .sel-wrap { padding: 2.5rem 1.2rem 3.5rem; }
    .sel-titulo { font-size: 1.75rem; }
    .sel-anios b { font-size: 2.1rem; }
  }
CSS;
require __DIR__ . '/partials/header.php';
?>

<?php page_hero(
  ['Inicio' => 'index.php', 'Niveles' => null, 'Secundario' => null],
  'Nivel Secundario',
  'Dos caminos, <em>una misma formación</em>',
  'Ambas modalidades comparten el ciclo básico y el proyecto educativo del colegio. Elegí la que quieras conocer en detalle.'
); ?>

<section class="sel-wrap">
  <div class="sel-opciones">

    <!-- ORIENTADA -->
    <a href="sec-orientada.php" class="sel-card sel-orientada">
      <div class="sel-top">
        <div class="sel-encabezado">
          <span class="sel-tag">Bachillerato</span>
          <span class="sel-anios"><b>6</b><span>años</span></span>
        </div>
        <h2 class="sel-titulo">Secundaria <em>Orientada</em></h2>
      </div>
      <div class="sel-cuerpo">
        <p class="sel-detalle">
          Ciclo básico (1° a 3°) y ciclo orientado (4° a 6°), con salida en
          Economía y Administración o en Ciencias Naturales.
        </p>
        <ul class="sel-lista">
          <li><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Economía y Administración</li>
          <li><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Ciencias Naturales</li>
          <li><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Laboratorio certificado</li>
        </ul>
        <span class="sel-cta">
          Ver la propuesta
          <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </span>
      </div>
    </a>

    <!-- TÉCNICA -->
    <a href="sec-tecnica.php" class="sel-card sel-tecnica">
      <div class="sel-top">
        <div class="sel-encabezado">
          <span class="sel-tag">Educación Técnica</span>
          <span class="sel-anios"><b>7</b><span>años</span></span>
        </div>
        <h2 class="sel-titulo">Secundaria <em>Técnica</em></h2>
      </div>
      <div class="sel-cuerpo">
        <p class="sel-detalle">
          Ciclo básico (1° a 3°) y ciclo técnico (4° a 7°), con título de
          Técnico y prácticas profesionalizantes en el último año.
        </p>
        <ul class="sel-lista">
          <li><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Informática</li>
          <li><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Electrónica</li>
          <li><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Multimedios</li>
        </ul>
        <span class="sel-cta">
          Ver la propuesta
          <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </span>
      </div>
    </a>

  </div>

  <p class="sel-pie">
    ¿Todavía no sabés cuál elegir? Escribinos a
    <a href="mailto:<?= cfg_e('email_admisiones') ?>"><?= cfg_e('email_admisiones') ?></a>
    o mirá las <a href="inscripcion-sec.php">condiciones de inscripción</a>.
  </p>
</section>

<?php require __DIR__ . '/partials/footer.php';
