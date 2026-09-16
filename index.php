<?php
// Página generada sobre partials/header.php + partials/footer.php.
// El menú se edita en partials/menu.php; los datos de contacto,
// desde el panel (Datos de contacto).
$page_desc       = 'Colegio Parroquial Juan XXIII: Nivel Inicial, Primario y Secundario (Orientada y Técnica) en el oeste del Gran Buenos Aires. Educando con valores desde 1962.';
$nav_active      = 'inicio';
$nav_active_link = 'index.php';
require __DIR__ . '/partials/header.php';
?>
  <section class="hero">
    <div class="hero-overlay" aria-hidden="true"></div>
    <div class="hero-content">
      <p class="hero-eyebrow">Bienvenidos a nuestra comunidad</p>
      <h1 class="hero-title">Formando personas,<br/><em>construyendo futuro.</em></h1>
      <div class="hero-cta">
        <a href="novedades.php" class="btn btn-primary">Ver Novedades</a>
        <a href="#secundario" class="btn btn-outline">Conocé nuestros niveles</a>
      </div>
    </div>
  </section>

  <div class="stats-strip">
    <div class="stat"><strong>+70</strong><span>años de trayectoria</span></div>
    <div class="stat-divider"></div>
    <div class="stat"><strong>1200+</strong><span>alumnos</span></div>
    <div class="stat-divider"></div>
    <div class="stat"><strong>3</strong><span>niveles educativos</span></div>
    <div class="stat-divider"></div>
    <div class="stat"><strong>100+</strong><span>docentes</span></div>
  </div>

  <section class="section" id="inicial">
    <div class="section-header">
      <span class="section-tag">Nuestros Niveles</span>
      <h2 class="section-title">Una educación completa<br/>en cada etapa</h2>
    </div>
    <div class="niveles-grid">
      <div class="nivel-card" id="inicialCard">
        <div class="nivel-badge" style="--accent:#E63946;">Inicial</div>
        <div class="image-placeholder nivel-img"><span>Foto Nivel Inicial</span></div>
        <div class="nivel-body">
          <h3>Nivel Inicial</h3>
          <p>Un espacio de juego, descubrimiento y afecto donde los más pequeños dan sus primeros pasos en el aprendizaje.</p>
          <a href="nivel-inicial.php" class="link-arrow">Ver más &rarr;</a>
        </div>
      </div>
      <div class="nivel-card" id="primarioCard">
        <div class="nivel-badge" style="--accent:#1D3557;">Primario</div>
        <div class="image-placeholder nivel-img"><span>Foto Nivel Primario</span></div>
        <div class="nivel-body">
          <h3 id="primario">Nivel Primario</h3>
          <p>Consolidamos hábitos de estudio, valores y conocimientos con metodologías activas y docentes comprometidos.</p>
          <a href="nivel-primario.php" class="link-arrow">Ver más &rarr;</a>
        </div>
      </div>
      <div class="nivel-card" id="secundarioCard">
        <div class="nivel-badge" style="--accent:#E63946;">Secundario</div>
        <div class="image-placeholder nivel-img"><span>Foto Nivel Secundario</span></div>
        <div class="nivel-body">
          <h3 id="secundario">Nivel Secundario</h3>
          <p>Preparamos a nuestros jóvenes para el mundo universitario y laboral con orientación, excelencia y autonomía.</p>
          <a href="nivel-secundario.php" class="link-arrow">Ver más &rarr;</a>
        </div>
      </div>
    </div>
  </section>

  <section class="section" id="acceso-rapido" style="padding-top:0;">
    <div class="section-header">
      <span class="section-tag">Accesos rápidos</span>
      <h2 class="section-title">Explorá la institución</h2>
    </div>
    <div class="card-grid">
      <a href="propuesta-educativa.php" class="info-card blue"><div class="ic-icon"><svg viewBox="0 0 24 24" class=""><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg></div><h3>Propuesta Educativa</h3><p>Conocé nuestro proyecto pedagógico integral.</p></a>
      <a href="becas.php" class="info-card"><div class="ic-icon"><svg viewBox="0 0 24 24" class=""><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/><line x1="6" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="18" y2="12"/></svg></div><h3>Becas</h3><p>Sistema de ayudas económicas para las familias.</p></a>
      <a href="feria-ciencias.php" class="info-card blue"><div class="ic-icon"><svg viewBox="0 0 24 24" class=""><path d="M9 2v6L4 18a2 2 0 0 0 1.8 3h12.4A2 2 0 0 0 20 18L15 8V2"/><line x1="8" y1="2" x2="16" y2="2"/><line x1="7" y1="14" x2="17" y2="14"/></svg></div><h3>Feria de Ciencias</h3><p>Proyectos e innovación de nuestros alumnos.</p></a>
    </div>
  </section>

  
<?php require __DIR__ . '/partials/footer.php';
