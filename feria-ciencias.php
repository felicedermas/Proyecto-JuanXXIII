<?php
// Página generada sobre partials/header.php + partials/footer.php.
// El menú se edita en partials/menu.php; los datos de contacto,
// desde el panel (Datos de contacto).
$page_title      = 'Feria de Ciencias';
$page_desc       = 'Feria Anual de Ciencias del Colegio Parroquial Juan XXIII: proyectos, innovación y trabajo de los alumnos.';
$nav_active      = 'comunidad';
$nav_active_link = 'feria-ciencias.php';
require __DIR__ . '/partials/header.php';
?>
  

  <?php page_hero(['Inicio' => 'index.php', 'Comunidad' => null, 'Feria de Ciencias' => null], 'Comunidad', 'Feria Anual de <em>Ciencias</em>', 'Cada año, nuestros estudiantes transforman la curiosidad en proyectos: experimentos, prototipos e ideas que cambian la mirada sobre el mundo.'); ?>

  <section class="content-section">
    <div class="prose narrow" style="max-width:820px;margin:0 auto;">
      <h2>Una tradición que despierta vocaciones</h2>
      <p>La Feria de Ciencias es uno de los eventos más esperados del calendario escolar. Durante toda una jornada, las aulas se convierten en laboratorios donde los alumnos de los tres niveles presentan sus investigaciones ante familias, docentes y visitantes.</p>
    </div>
  </section>

  <section class="band">
    <div class="content-section">
      <div class="section-header">
        <span class="section-tag">Edición 2025</span>
        <h2 class="section-title">Proyectos destacados</h2>
      </div>
      <div class="card-grid">
        <div class="info-card">
          <span class="project-tag">Nivel Secundario</span>
          <h3>Energía solar casera</h3>
          <p>Un panel solar construido con materiales reciclados capaz de cargar dispositivos pequeños.</p>
        </div>
        <div class="info-card blue">
          <span class="project-tag">Nivel Primario</span>
          <h3>El huerto inteligente</h3>
          <p>Sistema de riego automático con sensores de humedad para una huerta escolar sustentable.</p>
        </div>
        <div class="info-card">
          <span class="project-tag">Nivel Secundario</span>
          <h3>Bioplástico de papa</h3>
          <p>Elaboración de un plástico biodegradable a partir del almidón de la papa.</p>
        </div>
        <div class="info-card blue">
          <span class="project-tag">Nivel Inicial</span>
          <h3>¿Por qué flota?</h3>
          <p>Experimentos sencillos sobre flotación y densidad para los más pequeños.</p>
        </div>
        <div class="info-card">
          <span class="project-tag">Nivel Secundario</span>
          <h3>App de reciclaje</h3>
          <p>Aplicación que ayuda a clasificar residuos y ubicar puntos verdes del barrio.</p>
        </div>
        <div class="info-card blue">
          <span class="project-tag">Nivel Primario</span>
          <h3>Volcán químico</h3>
          <p>Demostración de reacciones ácido-base con una maqueta interactiva.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="cta-band">
    <h2>Próxima edición: octubre 2026</h2>
    <p>Te esperamos para descubrir el ingenio y la creatividad de nuestros estudiantes.</p>
    <a href="agenda.php" class="btn btn-white">Ver agenda</a>
  </section>

  
<?php require __DIR__ . '/partials/footer.php';
