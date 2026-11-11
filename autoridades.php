<?php
// Página generada sobre partials/header.php + partials/footer.php.
// El menú se edita en partials/menu.php; los datos de contacto,
// desde el panel (Datos de contacto).
$page_title      = 'Autoridades';
$page_desc       = 'Equipo directivo y de gestión del Colegio Parroquial Juan XXIII: rectorado, dirección de niveles y coordinaciones.';
$nav_active      = 'institucional';
$nav_active_link = 'autoridades';
require __DIR__ . '/partials/header.php';
?>
  

  <?php page_hero(['Inicio' => './', 'Institucional' => null, 'Autoridades' => null], 'Institucional', 'Nuestro <em>equipo de gestión</em>', 'Un grupo humano comprometido con la educación, que coordina el día a día de cada nivel y acompaña a las familias.'); ?>

  <section class="content-section">
    <div class="section-header">
      <span class="section-tag">Representación legal</span>
      <h2 class="section-title">Representante legal</h2>
      <p class="section-sub">El colegio es propiedad del Obispado de San Justo.</p>
    </div>
    <div class="people-grid is-single">
        <div class="person-card">
          <div class="person-photo person-avatar" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
          <div class="person-body">
            <h3>Juan Carlos Quiroga</h3>
            <span class="person-role">Representante Legal</span>
          </div>
        </div>
    </div>
  </section>

  <section class="band">
    <div class="content-section">
      <div class="section-header">
        <span class="section-tag">Por nivel</span>
        <h2 class="section-title">Nivel Inicial y Nivel Primario</h2>
      </div>
      <div class="people-grid">
        <div class="person-card">
          <div class="person-photo person-avatar" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
          <div class="person-body">
            <h3>Natalia Cruz</h3>
            <span class="person-role">Directora · Nivel Inicial</span>
          </div>
        </div>
        <div class="person-card">
          <div class="person-photo person-avatar" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
          <div class="person-body">
            <h3>María Nazaret Rodríguez</h3>
            <span class="person-role">Directora · Nivel Primario</span>
          </div>
        </div>
        <div class="person-card">
          <div class="person-photo person-avatar" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
          <div class="person-body">
            <h3>Marcela García</h3>
            <span class="person-role">Coordinadora · Nivel Primario</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="content-section">
    <div class="section-header">
      <span class="section-tag">Por nivel</span>
      <h2 class="section-title">Nivel Secundario</h2>
    </div>
    <div class="people-grid cols-4">
        <div class="person-card">
          <div class="person-photo person-avatar" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
          <div class="person-body">
            <h3>Carlos Alcaro</h3>
            <span class="person-role">Director · Secundaria Orientada</span>
          </div>
        </div>
        <div class="person-card">
          <div class="person-photo person-avatar" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
          <div class="person-body">
            <h3>Oscar Villarruel</h3>
            <span class="person-role">Director · Secundaria Técnica</span>
          </div>
        </div>
        <div class="person-card">
          <div class="person-photo person-avatar" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
          <div class="person-body">
            <h3>Sebastián De Jesús</h3>
            <span class="person-role">Vicedirector · Secundaria Técnica</span>
          </div>
        </div>
        <div class="person-card">
          <div class="person-photo person-avatar" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
          <div class="person-body">
            <h3>Alejo Salzman</h3>
            <span class="person-role">Secretario · Secundaria Técnica</span>
          </div>
        </div>
    </div>
  </section>

  
<?php require __DIR__ . '/partials/footer.php';
