<?php
// Página generada sobre partials/header.php + partials/footer.php.
// El menú se edita en partials/menu.php; los datos de contacto,
// desde el panel (Datos de contacto).
$page_title      = 'Historia';
$page_desc       = 'Más de seis décadas formando comunidad: la historia del Colegio Parroquial Juan XXIII desde su fundación en 1962.';
$nav_active      = 'institucional';
$nav_active_link = 'historia.php';
require __DIR__ . '/partials/header.php';
?>
  

  <?php page_hero(['Inicio' => 'index.php', 'Institucional' => null, 'Historia' => null], 'Institucional', 'Más de seis décadas <em>formando comunidad</em>', 'Desde 1962 acompañamos a miles de familias del oeste del Gran Buenos Aires con una propuesta educativa centrada en la persona, los valores y la excelencia.'); ?>

  <section class="content-section">
    <div class="split">
      <div class="prose">
        <h2>Nuestros orígenes</h2>
        <p>El Colegio Parroquial Juan XXIII nació en 1962 por iniciativa de la parroquia local, que soñaba con un espacio donde la educación y la fe caminaran juntas. Comenzó con apenas dos aulas y un puñado de docentes comprometidos con el barrio.</p>
        <p>Con el correr de los años, la institución creció hasta consolidar los tres niveles educativos que hoy ofrece, manteniendo siempre el espíritu cercano y familiar de sus primeros días.</p>
      </div>
      <div class="split-media">
        <div class="image-placeholder split-img"><span>Foto histórica del colegio</span></div>
      </div>
    </div>
  </section>

  <section class="band">
    <div class="content-section">
      <div class="section-header">
        <span class="section-tag">Línea de tiempo</span>
        <h2 class="section-title">Hitos de nuestra historia</h2>
      </div>
      <div class="timeline">
        <div class="tl-item">
          <span class="tl-year">1962</span>
          <h3>Fundación</h3>
          <p>Abre sus puertas el Nivel Primario con las primeras dos secciones y el respaldo de la comunidad parroquial.</p>
        </div>
        <div class="tl-item">
          <span class="tl-year">1975</span>
          <h3>Apertura del Nivel Inicial</h3>
          <p>Se incorpora el Jardín de Infantes con salas de 4 y 5 años para acompañar a los más pequeños.</p>
        </div>
        <div class="tl-item">
          <span class="tl-year">1988</span>
          <h3>Nace el Nivel Secundario</h3>
          <p>Comienza el Bachillerato, completando la trayectoria educativa dentro de la misma institución.</p>
        </div>
        <div class="tl-item">
          <span class="tl-year">2004</span>
          <h3>Orientación Técnica</h3>
          <p>Se suma la modalidad técnica, ampliando las salidas laborales y académicas de los egresados.</p>
        </div>
        <div class="tl-item">
          <span class="tl-year">2022</span>
          <h3>Transformación digital</h3>
          <p>Se moderniza la infraestructura tecnológica y se incorpora la plataforma de gestión académica Xhendra.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="cta-band">
    <h2>Una historia que seguís escribiendo vos</h2>
    <p>Sumate a una comunidad educativa con más de 70 años de trayectoria y proyecto a futuro.</p>
    <a href="contacto.php" class="btn btn-white">Conocé el colegio</a>
  </section>

  
<?php require __DIR__ . '/partials/footer.php';
