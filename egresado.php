<?php
// ============================================================
//  Colegio Parroquial Juan XXIII — Detalle de Egresado
// ============================================================

require_once __DIR__ . '/conexion.php';
$pdo = db();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: egresados.php'); exit; }

$stmt = $pdo->prepare(
    "SELECT * FROM egresados WHERE id_egresado = :id AND publicado = 1"
);
$stmt->execute([':id' => $id]);
$egr = $stmt->fetch();
if (!$egr) { header('Location: egresados.php'); exit; }

// Galería de imágenes adicionales
$imgs = $pdo->prepare("SELECT url_imagen, alt_text FROM imagenes_egresados WHERE id_egresado = :id ORDER BY orden ASC");
$imgs->execute([':id' => $id]);
$galeria = $imgs->fetchAll();

// Egresados relacionados (los últimos 3 excepto el actual)
$rel = $pdo->prepare(
    "SELECT id_egresado, nombre, apellido, subtitulo, foto_perfil, anio_egreso, orientacion
     FROM egresados
     WHERE publicado = 1 AND id_egresado != :id
     ORDER BY created_at DESC
     LIMIT 3"
);
$rel->execute([':id' => $id]);
$relacionados = $rel->fetchAll();

function orientacion_color(string $o): string {
    return $o === 'Técnica' ? '#0d1b2a' : '#457B9D';
}
$iniciales = strtoupper(substr($egr['nombre'],0,1).substr($egr['apellido'],0,1));
$foto_principal = $egr['foto_perfil'] ?? ($galeria[0]['url_imagen'] ?? null);

$page_title      = (string) ($eg['nombre'] . ' ' . $eg['apellido']);
$page_desc       = (string) (mb_substr(strip_tags((string)$eg['resumen']), 0, 180));
$nav_active      = 'comunidad';
$nav_active_link = 'egresado.php';
$header_compacto = true;
$page_style = <<<'CSS'
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    /* ── BREADCRUMB ── */
    .breadcrumb {
      max-width: 820px;
      margin: 0 auto;
      padding: 1.25rem 2rem .5rem;
      font-size: .8rem;
      color: var(--gray-500);
      display: flex;
      align-items: center;
      gap: .5rem;
    }
    .breadcrumb a { color: var(--blue-mid); font-weight: 600; }
    .breadcrumb a:hover { color: var(--red); }
    .breadcrumb-sep { color: var(--gray-500); }

    /* ── HERO DEL ARTÍCULO ── */
    .det-hero {
      max-width: 820px;
      margin: 0 auto;
      padding: 1.5rem 2rem 2.5rem;
      display: grid;
      grid-template-columns: auto 1fr;
      gap: 2.25rem;
      align-items: center;
    }
    .det-foto {
      width: 160px; height: 160px;
      border-radius: 50%;
      overflow: hidden;
      flex-shrink: 0;
      background: linear-gradient(135deg, #1D3557, #457B9D);
      display: flex; align-items: center; justify-content: center;
      box-shadow: var(--shadow-md);
      border: 4px solid var(--blue-light);
    }
    .det-foto img { width: 100%; height: 100%; object-fit: cover; }
    .det-foto-avatar {
      font-family: var(--font-display);
      font-size: 3.2rem;
      font-weight: 900;
      color: #fff;
    }
    .det-meta { display: flex; flex-direction: column; gap: .55rem; }
    .det-eyebrow {
      font-size: .72rem;
      font-weight: 800;
      letter-spacing: .16em;
      text-transform: uppercase;
      color: var(--red);
    }
    .det-title {
      font-family: var(--font-display);
      font-size: clamp(1.6rem, 4vw, 2.4rem);
      line-height: 1.1;
      color: var(--blue-dark);
    }
    .det-title em { font-style: italic; }
    .det-subtitle {
      font-size: .95rem;
      color: var(--blue-mid);
      font-weight: 600;
    }
    .det-pills {
      display: flex;
      align-items: center;
      gap: .65rem;
      flex-wrap: wrap;
      margin-top: .35rem;
    }
    .det-pill {
      display: inline-flex;
      align-items: center;
      gap: .3rem;
      font-size: .72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .07em;
      padding: .28rem .8rem;
      border-radius: 50px;
      border: 1.5px solid;
    }
    .det-pill-orientacion {
      background: transparent;
      border-color: currentColor;
    }
    .det-pill-anio {
      background: rgba(29,53,87,.08);
      border-color: transparent;
      color: var(--blue-dark);
    }

    /* ── LÍNEA DIVISORIA ── */
    .det-divider {
      max-width: 820px;
      margin: 0 auto;
      height: 1px;
      background: var(--gray-200);
      margin-bottom: 2.5rem;
    }

    /* ── CUERPO DEL ARTÍCULO ── */
    .det-body {
      max-width: 820px;
      margin: 0 auto;
      padding: 0 2rem 3rem;
    }
    .det-body p {
      font-size: 1.05rem;
      line-height: 1.85;
      color: #333;
      margin-bottom: 1.4rem;
    }
    .det-body p:first-child::first-letter {
      font-family: var(--font-display);
      font-size: 3.8rem;
      font-weight: 900;
      line-height: .85;
      float: left;
      margin: .1rem .18rem 0 0;
      color: var(--blue-dark);
    }
    .det-body h2, .det-body h3 {
      font-family: var(--font-display);
      color: var(--blue-dark);
      margin: 2rem 0 .75rem;
    }
    .det-body blockquote {
      border-left: 4px solid var(--red);
      padding: .75rem 1.25rem;
      margin: 1.75rem 0;
      background: rgba(230,57,70,.04);
      border-radius: 0 var(--radius) var(--radius) 0;
    }
    .det-body blockquote p {
      font-family: var(--font-display);
      font-style: italic;
      font-size: 1.15rem;
      color: var(--blue-dark);
      margin: 0;
    }
    .det-body em { color: var(--blue-dark); font-style: italic; }

    /* ── GALERÍA ── */
    .det-galeria {
      max-width: 820px;
      margin: 0 auto 3rem;
      padding: 0 2rem;
    }
    .det-galeria-label {
      font-size: .7rem;
      font-weight: 800;
      letter-spacing: .15em;
      text-transform: uppercase;
      color: var(--gray-500);
      margin-bottom: 1rem;
    }
    .det-galeria-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: .85rem;
    }
    .det-galeria-grid img {
      width: 100%; aspect-ratio: 4/3;
      object-fit: cover;
      border-radius: var(--radius);
      cursor: pointer;
      transition: var(--transition);
    }
    .det-galeria-grid img:hover { transform: scale(1.03); box-shadow: var(--shadow-md); }

    /* ── LIGHTBOX ── */
    .lightbox {
      display: none;
      position: fixed; inset: 0;
      background: rgba(0,0,0,.88);
      z-index: 9999;
      align-items: center; justify-content: center;
    }
    .lightbox.open { display: flex; }
    .lightbox img { max-width: 90vw; max-height: 90vh; border-radius: var(--radius); box-shadow: var(--shadow-lg); }
    .lightbox-close {
      position: absolute; top: 1.5rem; right: 1.5rem;
      background: rgba(255,255,255,.15); border: none; cursor: pointer;
      color: #fff; font-size: 1.5rem; width: 44px; height: 44px;
      border-radius: 50%; display: flex; align-items: center; justify-content: center;
      transition: background .2s;
    }
    .lightbox-close:hover { background: rgba(255,255,255,.28); }

    /* ── RELACIONADOS ── */
    .det-relacionados {
      background: var(--gray-100);
      padding: 3.5rem 2rem 4rem;
      margin-top: 2rem;
    }
    .det-rel-inner { max-width: 1100px; margin: 0 auto; }
    .det-rel-title {
      font-family: var(--font-display);
      font-size: 1.5rem;
      color: var(--blue-dark);
      margin-bottom: 1.75rem;
    }
    .det-rel-title em { font-style: italic; color: var(--red); }
    .det-rel-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.25rem;
    }
    .det-rel-card {
      background: #fff;
      border-radius: var(--radius-lg);
      overflow: hidden;
      box-shadow: var(--shadow-sm);
      text-decoration: none;
      color: inherit;
      display: flex;
      flex-direction: column;
      transition: var(--transition);
    }
    .det-rel-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
    .det-rel-img {
      aspect-ratio: 3/2;
      background: linear-gradient(135deg, #1D3557, #457B9D);
      display: flex; align-items: center; justify-content: center;
      overflow: hidden; position: relative;
    }
    .det-rel-img img { width:100%;height:100%;object-fit:cover;position:absolute;inset:0; }
    .det-rel-avatar {
      font-family: var(--font-display);
      font-size: 1.6rem; font-weight: 900; color: #fff;
      position: relative; z-index: 1;
    }
    .det-rel-body { padding: 1.1rem 1.1rem .9rem; }
    .det-rel-name {
      font-family: var(--font-display);
      font-size: 1rem; color: var(--blue-dark); margin-bottom: .25rem;
    }
    .det-rel-sub { font-size: .78rem; color: var(--blue-mid); font-weight: 600; margin-bottom: .5rem; }
    .det-rel-anio { font-size: .72rem; color: var(--gray-500); }

    /* ── VOLVER ── */
    .det-volver {
      max-width: 820px;
      margin: 0 auto;
      padding: 1.75rem 2rem 0;
    }
    .det-volver a {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      font-size: .85rem;
      font-weight: 700;
      color: var(--blue-mid);
      transition: color .2s, gap .2s;
    }
    .det-volver a:hover { color: var(--red); gap: .8rem; }

    /* ── RESPONSIVE ── */
    @media(max-width:768px) {
      .det-hero { grid-template-columns: 1fr; text-align: center; }
      .det-foto { margin: 0 auto; }
      .det-pills { justify-content: center; }
      .det-rel-grid { grid-template-columns: 1fr; }
      .det-body p:first-child::first-letter { font-size: 2.8rem; }
    }
CSS;
require __DIR__ . '/partials/header.php';
?>
<!-- ── BREADCRUMB ── -->
<div class="breadcrumb">
  <a href="egresados.php">Egresados</a>
  <span class="breadcrumb-sep">/</span>
  <span><?= htmlspecialchars($egr['nombre'].' '.$egr['apellido']) ?></span>
</div>

<!-- ── HERO ── -->
<div class="det-hero">
  <div class="det-foto">
    <?php if ($foto_principal): ?>
      <img src="<?= htmlspecialchars($foto_principal) ?>" alt="<?= htmlspecialchars($egr['nombre'].' '.$egr['apellido']) ?>"/>
    <?php else: ?>
      <span class="det-foto-avatar"><?= $iniciales ?></span>
    <?php endif; ?>
  </div>
  <div class="det-meta">
    <p class="det-eyebrow">✦ Conocemos a</p>
    <h1 class="det-title"><em><?= htmlspecialchars($egr['nombre'].' '.$egr['apellido']) ?></em></h1>
    <p class="det-subtitle"><?= htmlspecialchars($egr['subtitulo']) ?></p>
    <div class="det-pills">
      <span class="det-pill det-pill-orientacion" style="color:<?= orientacion_color($egr['orientacion']) ?>">
        <?= htmlspecialchars($egr['orientacion']) ?>
      </span>
      <span class="det-pill det-pill-anio">
        Egresado/a <?= htmlspecialchars($egr['anio_egreso']) ?>
      </span>
    </div>
  </div>
</div>

<div class="det-divider"></div>

<!-- ── CUERPO ── -->
<div class="det-body">
  <?= $egr['cuerpo'] /* El cuerpo puede contener HTML — sanitizar antes de guardar */ ?>
</div>

<!-- ── GALERÍA ── -->
<?php if (!empty($galeria)): ?>
<div class="det-galeria">
  <p class="det-galeria-label">Galería de fotos</p>
  <div class="det-galeria-grid">
    <?php foreach ($galeria as $img): ?>
      <img src="<?= htmlspecialchars($img['url_imagen']) ?>"
           alt="<?= htmlspecialchars($img['alt_text'] ?? '') ?>"
           onclick="abrirLightbox(this.src)"
           loading="lazy"/>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- ── VOLVER ── -->
<div class="det-volver">
  <a href="egresados.php">← Volver a Egresados</a>
</div>

<!-- ── RELACIONADOS ── -->
<?php if (!empty($relacionados)): ?>
<div class="det-relacionados">
  <div class="det-rel-inner">
    <h2 class="det-rel-title">Otras <em>historias</em></h2>
    <div class="det-rel-grid">
      <?php foreach ($relacionados as $r):
        $rfoto = $r['foto_perfil'] ?? null;
        $rini  = strtoupper(substr($r['nombre'],0,1).substr($r['apellido'],0,1));
      ?>
      <a href="egresado.php?id=<?= $r['id_egresado'] ?>" class="det-rel-card">
        <div class="det-rel-img">
          <?php if ($rfoto): ?>
            <img src="<?= htmlspecialchars($rfoto) ?>" alt="<?= htmlspecialchars($r['nombre'].' '.$r['apellido']) ?>"/>
          <?php else: ?>
            <span class="det-rel-avatar"><?= $rini ?></span>
          <?php endif; ?>
        </div>
        <div class="det-rel-body">
          <p class="det-rel-name">Conocemos a <?= htmlspecialchars($r['nombre'].' '.$r['apellido']) ?></p>
          <p class="det-rel-sub"><?= htmlspecialchars($r['subtitulo']) ?></p>
          <p class="det-rel-anio">Egresado/a <?= htmlspecialchars($r['anio_egreso']) ?></p>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ===== FOOTER ===== -->
<?php require __DIR__ . '/partials/footer.php';
