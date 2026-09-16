<?php
http_response_code(404);
$page_title = 'Página no encontrada';
$page_desc  = 'La página que buscabas no existe o cambió de dirección.';
$header_compacto = true;
require __DIR__ . '/partials/header.php';
?>

<?php page_hero(
  ['Inicio' => 'index.php', 'Página no encontrada' => null],
  'Error 404',
  'Esta página <em>no existe</em>',
  'Puede que el enlace esté viejo o que la dirección tenga un error de tipeo.'
); ?>

<section class="content-section" style="text-align:center;">
  <p style="margin-bottom:2rem;color:var(--gray-500);">
    Probá desde alguno de estos accesos:
  </p>
  <div class="card-grid">
    <a href="index.php" class="info-card blue">
      <h3>Inicio</h3><p>Volver a la portada del sitio.</p>
    </a>
    <a href="novedades.php" class="info-card">
      <h3>Novedades</h3><p>Últimas comunicaciones del colegio.</p>
    </a>
    <a href="contacto.php" class="info-card blue">
      <h3>Contacto</h3><p>Escribinos o llamanos.</p>
    </a>
  </div>
</section>

<?php require __DIR__ . '/partials/footer.php';
