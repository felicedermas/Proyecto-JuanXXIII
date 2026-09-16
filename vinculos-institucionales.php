<?php
// Página generada sobre partials/header.php + partials/footer.php.
// El menú se edita en partials/menu.php; los datos de contacto,
// desde el panel (Datos de contacto).
$page_title      = 'Vínculos Institucionales';
$page_desc       = 'Vínculos institucionales del Colegio Parroquial Juan XXIII: universidades, empresas y organizaciones aliadas.';
$nav_active      = 'comunidad';
$nav_active_link = 'vinculos-institucionales.php';
require __DIR__ . '/partials/header.php';
?>
  


  

  <?php page_hero(['Inicio' => 'index.php', 'Institucional' => null, 'Becas' => null], 'Comunidad', 'Vínculos <em>Institucionales</em>', 'Trabajamos junto a instituciones, colegios y empresas que acompañan y enriquecen nuestra propuesta educativa. Estos son algunos de los aliados con los que construimos comunidad.'); ?>

  <section class="content-section narrow">
    <div class="prose">
      <h2>Construyendo redes</h2>
      <p>El Colegio Parroquial Juan XXIII sostiene vínculos de cooperación con organizaciones educativas, culturales y empresariales que complementan la formación de nuestros estudiantes. Estas alianzas se traducen en pasantías, proyectos conjuntos, capacitaciones y oportunidades de crecimiento para toda la comunidad.</p>
    </div>
  </section>

  <section class="band">
    <div class="content-section">
      <div class="section-header">
        <span class="section-tag">Nuestros aliados</span>
        <h2 class="section-title">Instituciones y empresas con las que trabajamos</h2>
      </div>
      <!-- Para agregar un logo real: reemplazá el <span class="logo-placeholder"> por
           <img src="img/vinculos/nombre-logo.png" alt="Nombre de la institución" /> -->
      <div class="logo-grid">
        <div class="logo-item"><span class="logo-placeholder">Logo institución 1</span></div>
        <div class="logo-item"><span class="logo-placeholder">Logo institución 2</span></div>
        <div class="logo-item"><span class="logo-placeholder">Logo institución 3</span></div>
        <div class="logo-item"><span class="logo-placeholder">Logo institución 4</span></div>
        <div class="logo-item"><span class="logo-placeholder">Logo institución 5</span></div>
        <div class="logo-item"><span class="logo-placeholder">Logo institución 6</span></div>
        <div class="logo-item"><span class="logo-placeholder">Logo institución 7</span></div>
        <div class="logo-item"><span class="logo-placeholder">Logo institución 8</span></div>
      </div>
    </div>
  </section>

  <section class="content-section">
    <div class="section-header">
      <span class="section-tag">Tipos de vínculo</span>
      <h2 class="section-title">Cómo colaboramos</h2>
    </div>
    <div class="card-grid">
      <div class="info-card blue">
        <div class="ic-icon"><svg viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg></div>
        <h3>Colegios e instituciones educativas</h3>
        <p>Articulación entre niveles, proyectos pedagógicos compartidos e intercambio de experiencias.</p>
      </div>
      <div class="info-card">
        <div class="ic-icon"><svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></div>
        <h3>Empresas y organizaciones</h3>
        <p>Pasantías, charlas y experiencias que acercan el mundo laboral a nuestros estudiantes.</p>
      </div>
      <div class="info-card blue">
        <div class="ic-icon"><svg viewBox="0 0 24 24"><path d="M12 2l2.4 7.4H22l-6 4.6 2.3 7.4-6.3-4.6L5.7 21.4 8 14 2 9.4h7.6z"/></svg></div>
        <h3>Entidades culturales y comunitarias</h3>
        <p>Actividades solidarias, culturales y deportivas junto a organizaciones del barrio.</p>
      </div>
    </div>
  </section>

  <section class="cta-band">
    <h2>¿Querés ser parte de nuestra red?</h2>
    <p>Si tu institución o empresa desea establecer un vínculo con el colegio, nos encantará conocerte.</p>
    <a href="contacto.php" class="btn btn-white">Escribinos</a>
  </section>

  
<?php require __DIR__ . '/partials/footer.php';
