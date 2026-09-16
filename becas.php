<?php
// Página generada sobre partials/header.php + partials/footer.php.
// El menú se edita en partials/menu.php; los datos de contacto,
// desde el panel (Datos de contacto).
$page_title      = 'Becas';
$page_desc       = 'Sistema de becas y ayudas económicas del Colegio Parroquial Juan XXIII: requisitos, plazos y cómo solicitarlas.';
$nav_active      = 'institucional';
$nav_active_link = 'becas.php';
require __DIR__ . '/partials/header.php';
?>
  

  <?php page_hero(['Inicio' => 'index.php', 'Institucional' => null, 'Becas' => null], 'Institucional', 'Sistema de <em>becas y ayudas</em>', 'Acompañamos a las familias que atraviesan dificultades económicas para que ningún estudiante interrumpa su trayectoria escolar.'); ?>

  <section class="content-section narrow">
    <div class="prose">
      <h2>¿En qué consiste?</h2>
      <p>El colegio cuenta con un fondo solidario de becas que otorga reducciones parciales o totales del arancel mensual. Cada solicitud es evaluada por un comité que analiza la situación particular de la familia con total confidencialidad.</p>
    </div>
  </section>

  <section class="band">
    <div class="content-section">
      <div class="section-header">
        <span class="section-tag">Tipos de beca</span>
        <h2 class="section-title">Modalidades disponibles</h2>
      </div>
      <table class="simple-table">
        <thead>
          <tr><th>Tipo de beca</th><th>Cobertura</th><th>Dirigida a</th></tr>
        </thead>
        <tbody>
          <tr><td>Beca solidaria</td><td>25% a 50% del arancel</td><td>Familias con dificultades económicas comprobables</td></tr>
          <tr><td>Beca al mérito</td><td>30% del arancel</td><td>Estudiantes con desempeño académico destacado</td></tr>
          <tr><td>Beca hermanos</td><td>15% por hijo adicional</td><td>Familias con varios hijos en la institución</td></tr>
          <tr><td>Beca total</td><td>100% del arancel</td><td>Casos de vulnerabilidad evaluados por el comité</td></tr>
        </tbody>
      </table>
    </div>
  </section>

  <section class="content-section">
    <div class="section-header">
      <span class="section-tag">Cómo solicitarla</span>
      <h2 class="section-title">El proceso en 4 pasos</h2>
    </div>
    <div class="steps">
      <div class="step"><h3>Solicitud</h3><p>Completá el formulario de pedido de beca en la administración del colegio.</p></div>
      <div class="step"><h3>Documentación</h3><p>Presentá los comprobantes de ingresos y la documentación requerida.</p></div>
      <div class="step"><h3>Evaluación</h3><p>El comité analiza cada caso de manera confidencial y objetiva.</p></div>
      <div class="step"><h3>Resolución</h3><p>Recibís la respuesta y, de ser aprobada, se aplica la reducción.</p></div>
    </div>
  </section>

  <section class="cta-band">
    <h2>¿Necesitás más información?</h2>
    <p>Nuestro equipo administrativo está disponible para asesorarte sobre el sistema de becas.</p>
    <a href="contacto.php" class="btn btn-white">Consultar por becas</a>
  </section>

  
<?php require __DIR__ . '/partials/footer.php';
