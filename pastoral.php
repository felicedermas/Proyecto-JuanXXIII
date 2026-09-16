<?php
// Página generada sobre partials/header.php + partials/footer.php.
// El menú se edita en partials/menu.php; los datos de contacto,
// desde el panel (Datos de contacto).
$page_title      = 'Pastoral';
$page_desc       = 'Pastoral del Colegio Parroquial Juan XXIII: vida espiritual, acción solidaria y misiones.';
$nav_active      = 'comunidad';
$nav_active_link = 'pastoral.php';
require __DIR__ . '/partials/header.php';
?>
  

  <?php page_hero(['Inicio' => 'index.php', 'Comunidad' => null, 'Pastoral' => null], 'Comunidad', 'Vida <em>Pastoral</em>', 'El corazón espiritual del colegio: un espacio de encuentro, fe y compromiso solidario que da identidad a nuestra comunidad.'); ?>

  <section class="content-section">
    <div class="split reverse">
      <div class="prose">
        <h2>Caminar en comunidad</h2>
        <p>Como colegio parroquial, la dimensión pastoral atraviesa toda la vida escolar. No se trata solo de una materia, sino de un modo de habitar la escuela: con cercanía, escucha y apertura al otro.</p>
        <p>A lo largo del año proponemos retiros, celebraciones litúrgicas, campañas solidarias y grupos de reflexión abiertos a estudiantes, familias y docentes.</p>
      </div>
      <div class="split-media">
        <div class="image-placeholder split-img"><span>Foto de actividad pastoral</span></div>
      </div>
    </div>
  </section>

  <section class="band">
    <div class="content-section">
      <div class="section-header">
        <span class="section-tag">Nuestras actividades</span>
        <h2 class="section-title">Cómo vivimos la pastoral</h2>
      </div>
      <div class="card-grid">
        <div class="info-card blue"><div class="ic-icon"><svg viewBox="0 0 24 24"><path d="M12 2v20M5 9h14"/></svg></div><h3>Celebraciones</h3><p>Misas y momentos de oración que marcan el calendario de la comunidad.</p></div>
        <div class="info-card"><div class="ic-icon"><svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78z"/></svg></div><h3>Acción solidaria</h3><p>Colectas, voluntariados y proyectos de servicio a quienes más lo necesitan.</p></div>
        <div class="info-card blue"><div class="ic-icon"><svg viewBox="0 0 24 24"><circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div><h3>Grupos de reflexión</h3><p>Espacios de encuentro para compartir la fe y crecer juntos.</p></div>
      </div>
    </div>
  </section>

  <section class="pastoral-section">
    <div class="pastoral-deco" aria-hidden="true"></div>
    <div class="pastoral-content">
      <span class="section-tag light">Nuestro lema</span>
      <h2 class="section-title light">Educar el corazón, no solo la mente</h2>
      <p>Inspirados en el espíritu del Papa Juan XXIII, buscamos formar personas capaces de transformar la realidad con bondad, justicia y esperanza.</p>
      <a href="contacto.php" class="btn btn-primary">Sumate a la pastoral</a>
    </div>
    <div class="pastoral-image-wrap">
      <div class="image-placeholder pastoral-img"><span>Foto de la comunidad</span></div>
      <div class="pastoral-quote">
        <blockquote>"Busquemos lo que une, no lo que separa." — San Juan XXIII</blockquote>
      </div>
    </div>
  </section>

  
<?php require __DIR__ . '/partials/footer.php';
