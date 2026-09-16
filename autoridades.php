<?php
// Página generada sobre partials/header.php + partials/footer.php.
// El menú se edita en partials/menu.php; los datos de contacto,
// desde el panel (Datos de contacto).
$page_title      = 'Autoridades';
$page_desc       = 'Equipo directivo y de gestión del Colegio Parroquial Juan XXIII: rectorado, dirección de niveles y coordinaciones.';
$nav_active      = 'institucional';
$nav_active_link = 'autoridades.php';
require __DIR__ . '/partials/header.php';
?>
  

  <?php page_hero(['Inicio' => 'index.php', 'Institucional' => null, 'Autoridades' => null], 'Institucional', 'Nuestro <em>equipo de gestión</em>', 'Un grupo humano comprometido con la educación, que coordina el día a día de cada nivel y acompaña a las familias.'); ?>

  <section class="content-section">
    <div class="section-header">
      <span class="section-tag">Equipo Directivo</span>
      <h2 class="section-title">Dirección general</h2>
    </div>
    <div class="people-grid">
      <div class="person-card">
        <div class="image-placeholder person-photo"><span>Foto</span></div>
        <div class="person-body">
          <h3>Pbro. Ricardo Domínguez</h3>
          <span class="person-role">Representante Legal</span>
          <p>Referente espiritual y legal de la institución, vínculo con la parroquia.</p>
        </div>
      </div>
      <div class="person-card">
        <div class="image-placeholder person-photo"><span>Foto</span></div>
        <div class="person-body">
          <h3>Lic. Marta Sánchez</h3>
          <span class="person-role">Directora General</span>
          <p>Coordina el proyecto educativo integral de los tres niveles.</p>
        </div>
      </div>
      <div class="person-card">
        <div class="image-placeholder person-photo"><span>Foto</span></div>
        <div class="person-body">
          <h3>Prof. Daniel Quiroga</h3>
          <span class="person-role">Vicedirector</span>
          <p>Acompaña la gestión administrativa y la articulación entre niveles.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="band">
    <div class="content-section">
      <div class="section-header">
        <span class="section-tag">Por nivel</span>
        <h2 class="section-title">Equipos directivos de cada nivel</h2>
      </div>
      <div class="people-grid">
        <div class="person-card">
          <div class="image-placeholder person-photo"><span>Foto</span></div>
          <div class="person-body">
            <h3>Prof. Laura Giménez</h3>
            <span class="person-role">Directora · Nivel Inicial</span>
            <p>Responsable del Jardín de Infantes y las salas de 3, 4 y 5 años.</p>
          </div>
        </div>
        <div class="person-card">
          <div class="image-placeholder person-photo"><span>Foto</span></div>
          <div class="person-body">
            <h3>Prof. Sergio Medina</h3>
            <span class="person-role">Director · Nivel Primario</span>
            <p>Conduce la propuesta de 1° a 6° grado y el equipo docente.</p>
          </div>
        </div>
        <div class="person-card">
          <div class="image-placeholder person-photo"><span>Foto</span></div>
          <div class="person-body">
            <h3>Lic. Verónica Paredes</h3>
            <span class="person-role">Directora · Nivel Secundario</span>
            <p>A cargo de la secundaria orientada y la modalidad técnica.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  
<?php require __DIR__ . '/partials/footer.php';
