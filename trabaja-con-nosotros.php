<?php
// Página generada sobre partials/header.php + partials/footer.php.
// El menú se edita en partials/menu.php; los datos de contacto,
// desde el panel (Datos de contacto).
$page_title      = 'Trabajá con Nosotros';
$page_desc       = 'Trabajá con nosotros: búsquedas abiertas y postulación docente en el Colegio Parroquial Juan XXIII.';
$nav_active      = 'comunidad';
$nav_active_link = 'trabaja-con-nosotros.php';
require __DIR__ . '/partials/header.php';
?>
  

  <?php page_hero(['Inicio' => 'index.php', 'Comunidad' => null, 'Trabajá con Nosotros' => null], 'Comunidad', 'Trabajá <em>con nosotros</em>', '¿Compartís nuestra vocación educativa? Sumate a un equipo humano comprometido con la formación de las nuevas generaciones.'); ?>

  <section class="content-section">
    <div class="split">
      <div class="prose">
        <h2>Por qué elegirnos</h2>
        <p>En el Colegio Juan XXIII valoramos a nuestros docentes y no docentes como protagonistas del proyecto educativo. Ofrecemos un ambiente de trabajo cálido, oportunidades de formación continua y un fuerte sentido de comunidad.</p>
        <ul class="bullets">
          <li>Capacitación y desarrollo profesional permanente.</li>
          <li>Clima institucional basado en el respeto y la colaboración.</li>
          <li>Estabilidad y acompañamiento en la tarea docente.</li>
        </ul>
      </div>
      <div class="split-media">
        <div class="image-placeholder split-img"><span>Foto del equipo docente</span></div>
      </div>
    </div>
  </section>

  <section class="band">
    <div class="content-section narrow">
      <div class="section-header">
        <span class="section-tag">Postulación</span>
        <h2 class="section-title">Dejanos tus datos</h2>
      </div>
      <div class="form-wrap">
        <div class="form-grid">
          <div class="form-field">
            <label for="t-nombre">Nombre y apellido</label>
            <input id="t-nombre" type="text" placeholder="Tu nombre completo">
          </div>
          <div class="form-field">
            <label for="t-email">Correo electrónico</label>
            <input id="t-email" type="email" placeholder="tucorreo@ejemplo.com">
          </div>
          <div class="form-field">
            <label for="t-tel">Teléfono</label>
            <input id="t-tel" type="tel" placeholder="(011) 1234-5678">
          </div>
          <div class="form-field">
            <label for="t-area">Área de interés</label>
            <select id="t-area">
              <option>Docente — Nivel Inicial</option>
              <option>Docente — Nivel Primario</option>
              <option>Docente — Nivel Secundario</option>
              <option>Personal administrativo</option>
              <option>Maestranza y servicios</option>
            </select>
          </div>
          <div class="form-field full">
            <label for="t-msg">Contanos sobre vos</label>
            <textarea id="t-msg" placeholder="Experiencia, formación y motivación..."></textarea>
          </div>
          <div class="form-field full">
            <label for="t-cv">Adjuntar CV</label>
            <input id="t-cv" type="file">
            <span class="form-note">Formato PDF o Word, hasta 5 MB.</span>
          </div>
          <div class="form-field full">
            <button type="button" class="btn btn-primary">Enviar postulación</button>
          </div>
        </div>
      </div>
    </div>
  </section>

  
<?php require __DIR__ . '/partials/footer.php';
