<?php
// Página generada sobre partials/header.php + partials/footer.php.
// El menú se edita en partials/menu.php; los datos de contacto,
// desde el panel (Datos de contacto).
$page_title      = 'Becas';
$page_desc       = 'Sistema de becas y ayudas económicas del Colegio Parroquial Juan XXIII: requisitos, plazos y cómo solicitarlas.';
$nav_active      = 'institucional';
$nav_active_link = 'becas';

// Estado del formulario de beca (se habilita desde el panel → Inscripciones).
// Sin base de datos, la página se sigue viendo: el formulario queda cerrado.
require_once __DIR__ . '/partials/inscripciones.php';
$becas_estado = insc_estado(null);
$pdo_becas = db_opcional();
if ($pdo_becas !== null && insc_asegurar_tablas($pdo_becas)) {
    try {
        $becas_estado = insc_estado(insc_fila_formulario($pdo_becas, 'becas'));
    } catch (Throwable $ex) {
        // se queda con el estado cerrado
    }
}

require __DIR__ . '/partials/header.php';
?>
  

  <?php page_hero(['Inicio' => './', 'Institucional' => null, 'Becas' => null], 'Institucional', 'Sistema de <em>becas y ayudas</em>', 'Acompañamos a las familias que atraviesan dificultades económicas para que ningún estudiante interrumpa su trayectoria escolar.'); ?>

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
      <div class="step"><h3>Solicitud</h3><p>Completá el formulario de pedido de beca en línea, desde esta misma página.</p></div>
      <div class="step"><h3>Documentación</h3><p>Presentá los comprobantes de ingresos y la documentación requerida.</p></div>
      <div class="step"><h3>Evaluación</h3><p>El comité analiza cada caso de manera confidencial y objetiva.</p></div>
      <div class="step"><h3>Resolución</h3><p>Recibís la respuesta y, de ser aprobada, se aplica la reducción.</p></div>
    </div>
  </section>

  <section class="cta-band">
    <?php if ($becas_estado['abierto']): ?>
      <h2>Solicitá tu beca en línea</h2>
      <p>
        Completás la solicitud desde acá y el comité la evalúa con total confidencialidad.
        <?php if ($becas_estado['periodo'] !== ''): ?><br><b><?= e($becas_estado['periodo']) ?></b><?php endif; ?>
      </p>
      <a href="inscripcion-becas" class="btn btn-white">Completar la solicitud</a>
      <a href="contacto" class="btn btn-outline">Consultar por becas</a>
    <?php else: ?>
      <h2>¿Necesitás más información?</h2>
      <p>
        <?php if ($becas_estado['motivo'] === 'proximamente'): ?>
          La solicitud en línea se habilita el <?= e(insc_fecha_ar($becas_estado['desde'])) ?>.
        <?php elseif ($becas_estado['motivo'] === 'finalizado'): ?>
          El período de solicitudes finalizó el <?= e(insc_fecha_ar($becas_estado['hasta'])) ?>.
        <?php else: ?>
          La solicitud en línea no está habilitada en este momento.
        <?php endif; ?>
        Nuestro equipo administrativo está disponible para asesorarte sobre el sistema de becas.
      </p>
      <a href="contacto" class="btn btn-white">Consultar por becas</a>
    <?php endif; ?>
  </section>

  
<?php require __DIR__ . '/partials/footer.php';
