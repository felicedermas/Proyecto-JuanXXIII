<?php
// Página generada sobre partials/header.php + partials/footer.php.
// El menú se edita en partials/menu.php; los datos de contacto,
// desde el panel (Datos de contacto).
$page_title      = 'Equipo de Psicopedagogía';
$page_desc       = 'Equipo de Psicopedagogía del Colegio Parroquial Juan XXIII: acompañamiento, orientación y apoyo a las familias.';
$nav_active      = 'comunidad';
$nav_active_link = 'psicopedagogia.php';
require __DIR__ . '/partials/header.php';
?>
  


  

  <?php page_hero(['Inicio' => 'index.php', 'Institucional' => null, 'Becas' => null], 'Comunidad', 'Equipo de <em>Psicopedagogía</em>', 'Contamos con profesionales para acompañar a nuestros estudiantes y familias en su trayectoria escolar, disponibles los días lunes y miércoles en el establecimiento.'); ?>

  <section class="content-section narrow">
    <div class="prose">
      <h2>Nuestro acompañamiento</h2>
      <p>El equipo de psicopedagogía del colegio trabaja de manera articulada con docentes, directivos y familias para favorecer los procesos de aprendizaje de cada estudiante. Brindamos orientación frente a dificultades escolares, estrategias de estudio y apoyo en la integración de alumnos con necesidades particulares, siempre desde un enfoque respetuoso y confidencial.</p>
    </div>
  </section>

  <section class="band">
    <div class="content-section">
      <div class="section-header">
        <span class="section-tag">Disponibilidad</span>
        <h2 class="section-title">Días y horarios de atención</h2>
      </div>
      <table class="simple-table">
        <thead>
          <tr><th>Día</th><th>Horario</th><th>Modalidad</th></tr>
        </thead>
        <tbody>
          <tr><td>Lunes</td><td>8:00 a 12:00 hs</td><td>Atención a estudiantes y docentes</td></tr>
          <tr><td>Miércoles</td><td>13:00 a 17:00 hs</td><td>Entrevistas con familias (con turno previo)</td></tr>
        </tbody>
      </table>
    </div>
  </section>

  <section class="content-section">
    <div class="section-header">
      <span class="section-tag">Áreas de trabajo</span>
      <h2 class="section-title">¿En qué te podemos ayudar?</h2>
    </div>
    <div class="card-grid">
      <div class="info-card blue">
        <div class="ic-icon"><svg viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div>
        <h3>Procesos de aprendizaje</h3>
        <p>Orientación frente a dificultades en la lectura, escritura, matemática y organización del estudio.</p>
      </div>
      <div class="info-card">
        <div class="ic-icon"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
        <h3>Acompañamiento familiar</h3>
        <p>Espacios de entrevista y asesoramiento para que las familias acompañen el recorrido escolar.</p>
      </div>
      <div class="info-card blue">
        <div class="ic-icon"><svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
        <h3>Integración escolar</h3>
        <p>Apoyo y seguimiento de estudiantes con necesidades educativas particulares.</p>
      </div>
    </div>
  </section>

  <section class="cta-band">
    <h2>¿Querés solicitar una entrevista?</h2>
    <p>Nuestro equipo está a disposición para acompañarte. Escribinos y coordinamos un encuentro.</p>
    <a href="contacto.php" class="btn btn-white">Contactar al equipo</a>
  </section>

  
<?php require __DIR__ . '/partials/footer.php';
