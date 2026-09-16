<?php
// Página generada sobre partials/header.php + partials/footer.php.
// El menú se edita en partials/menu.php; los datos de contacto,
// desde el panel (Datos de contacto).
$page_title      = 'Ubicación';
$page_desc       = 'Cómo llegar al Colegio Parroquial Juan XXIII: dirección, mapa y medios de transporte.';
$nav_active      = 'contacto';
$nav_active_link = 'ubicacion.php';
require __DIR__ . '/partials/header.php';
?>
  

  <?php page_hero(['Inicio' => 'index.php', 'Contacto' => 'contacto.php', 'Ubicación' => null], 'Cómo llegar', '<em>Ubicación</em> en el mapa', 'Nos encontramos en el corazón de Ramos Mejía, con fácil acceso en transporte público y privado.'); ?>

  <section class="content-section">
    <?php /* El mapa se configura en el panel → Datos de contacto → "Mapa incrustado".
             Si está vacío, cae en un mapa genérico de OpenStreetMap. */ ?>
    <iframe
      class="map-frame"
      src="<?= cfg_e('mapa_embed', 'https://www.openstreetmap.org/export/embed.html?bbox=-58.575%2C-34.645%2C-58.555%2C-34.635&layer=mapnik&marker=-34.64%2C-58.565') ?>"
      title="Mapa de ubicación del <?= cfg_e('nombre_colegio') ?>"
      loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    <?php if (cfg('mapa_link')): ?>
      <p style="text-align:center;margin-top:1.2rem;">
        <a href="<?= cfg_e('mapa_link') ?>" target="_blank" rel="noopener" class="btn btn-outline">Abrir en Google Maps</a>
      </p>
    <?php endif; ?>
  </section>

  <section class="band">
    <div class="content-section">
      <div class="card-grid">
        <div class="info-card blue"><div class="ic-icon"><svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></div><h3>Dirección</h3><p><?= cfg_e('direccion') ?><?= cfg('localidad') ? ', ' . cfg_e('localidad') : '' ?>.<?= cfg('codigo_postal') ? ' CP ' . cfg_e('codigo_postal') . '.' : '' ?></p></div>
        <div class="info-card"><div class="ic-icon"><svg viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="16" rx="2"/><path d="M4 11h16M8 3v4M16 3v4M7 19l-2 2M17 19l2 2"/></svg></div><h3>En tren</h3><p>A 8 cuadras de la estación Ramos Mejía (Línea Sarmiento).</p></div>
        <div class="info-card blue"><div class="ic-icon"><svg viewBox="0 0 24 24"><path d="M8 6v6M16 6v6M2 12h20M7 18h10M5 6h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z"/></svg></div><h3>En colectivo</h3><p>Líneas 88, 96, 136 y 180 con parada cercana al colegio.</p></div>
      </div>
    </div>
  </section>

  <section class="cta-band">
    <h2>¿Querés visitarnos?</h2>
    <p>Coordiná una visita guiada y conocé nuestras instalaciones en persona.</p>
    <a href="contacto.php" class="btn btn-white">Coordinar visita</a>
  </section>

  
<?php require __DIR__ . '/partials/footer.php';
