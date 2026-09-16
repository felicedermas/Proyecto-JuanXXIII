<?php
// Página generada sobre partials/header.php + partials/footer.php.
// El menú se edita en partials/menu.php; los datos de contacto,
// desde el panel (Datos de contacto).
$page_title      = 'Centro de Estudiantes';
$page_desc       = 'Centro de Estudiantes del Colegio Parroquial Juan XXIII: participación, proyectos y voz estudiantil.';
$nav_active      = 'comunidad';
$nav_active_link = 'centro-estudiantes.php';
require __DIR__ . '/partials/header.php';
?>
  

  <?php page_hero(['Inicio' => 'index.php', 'Comunidad' => null, 'Centro de Estudiantes' => null], 'Comunidad', 'Centro de <em>Estudiantes</em>', 'La voz organizada de los alumnos: un espacio de participación, representación y proyectos que mejoran la vida escolar.'); ?>

  <section class="content-section">
    <div class="split">
      <div class="prose">
        <h2>¿Quiénes somos?</h2>
        <p>El Centro de Estudiantes está formado por alumnos de la secundaria elegidos democráticamente por sus compañeros. Su misión es representar los intereses del estudiantado y canalizar propuestas ante las autoridades.</p>
        <p>Cada año se renuevan las autoridades mediante elecciones, fomentando el compromiso cívico y el trabajo en equipo desde la escuela.</p>
      </div>
      <div class="split-media">
        <div class="image-placeholder split-img"><span>Foto del Centro de Estudiantes</span></div>
      </div>
    </div>
  </section>

  <section class="band">
    <div class="content-section narrow">
      <div class="section-header">
        <span class="section-tag">Nuestros valores</span>
        <h2 class="section-title">Lo que nos mueve</h2>
      </div>
      <div class="value-list">
        <div class="value-item"><span class="v-dot"></span><div><h4>Participación</h4><p>Damos voz a cada estudiante para construir entre todos.</p></div></div>
        <div class="value-item"><span class="v-dot"></span><div><h4>Solidaridad</h4><p>Organizamos campañas y colectas para quienes más lo necesitan.</p></div></div>
        <div class="value-item"><span class="v-dot"></span><div><h4>Compromiso</h4><p>Trabajamos por una escuela más justa e inclusiva.</p></div></div>
        <div class="value-item"><span class="v-dot"></span><div><h4>Creatividad</h4><p>Impulsamos eventos culturales, deportivos y recreativos.</p></div></div>
      </div>
    </div>
  </section>

  <section class="content-section">
    <div class="section-header">
      <span class="section-tag">Comisión actual</span>
      <h2 class="section-title">Autoridades estudiantiles</h2>
    </div>
    <div class="people-grid">
      <div class="person-card">
        <div class="image-placeholder person-photo"><span>Foto</span></div>
        <div class="person-body"><h3>Julián Ferreyra</h3><span class="person-role">Presidente</span><p>Estudiante de 6° año, orientación técnica.</p></div>
      </div>
      <div class="person-card">
        <div class="image-placeholder person-photo"><span>Foto</span></div>
        <div class="person-body"><h3>Camila Ríos</h3><span class="person-role">Vicepresidenta</span><p>Estudiante de 5° año, orientación economía.</p></div>
      </div>
      <div class="person-card">
        <div class="image-placeholder person-photo"><span>Foto</span></div>
        <div class="person-body"><h3>Tomás Aguirre</h3><span class="person-role">Secretario</span><p>Estudiante de 5° año, orientación sociales.</p></div>
      </div>
    </div>
  </section>

  
<?php require __DIR__ . '/partials/footer.php';
