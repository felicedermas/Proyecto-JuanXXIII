<?php
// ============================================================
//  Colegio Parroquial Juan XXIII — Detalle de Novedad
// ============================================================

require_once __DIR__ . '/conexion.php';
$pdo = db();

// Validar ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    header('Location: novedades.php');
    exit;
}

// Traer la novedad
$stmt = $pdo->prepare("
    SELECT n.id_novedad, n.titulo, n.descripcion, n.etiqueta, n.created_at,
           CONCAT(u.nombre, ' ', u.apellido) AS autor, u.rol AS rol_autor
    FROM novedades n
    JOIN usuarios u ON u.id_usuario = n.id_usuario
    WHERE n.id_novedad = ?
");
$stmt->execute([$id]);
$nov = $stmt->fetch();

if (!$nov) {
    header('Location: novedades.php');
    exit;
}

// Traer imágenes ordenadas
$img_stmt = $pdo->prepare("
    SELECT url_imagen, orden
    FROM imagenes_novedades
    WHERE id_novedad = ?
    ORDER BY orden ASC
");
$img_stmt->execute([$id]);
$imagenes = $img_stmt->fetchAll();

// Novedades relacionadas (misma etiqueta, excluyendo la actual)
$rel_stmt = $pdo->prepare("
    SELECT n.id_novedad, n.titulo, n.etiqueta, n.created_at,
           (SELECT url_imagen FROM imagenes_novedades WHERE id_novedad = n.id_novedad ORDER BY orden ASC LIMIT 1) AS portada
    FROM novedades n
    WHERE n.etiqueta = ? AND n.id_novedad != ?
    ORDER BY n.created_at DESC
    LIMIT 3
");
$rel_stmt->execute([$nov['etiqueta'], $id]);
$relacionadas = $rel_stmt->fetchAll();

function etiqueta_color(string $e): string {
    return match($e) {
        'Inicial'   => '#E63946',
        'Primario'  => '#1D3557',
        'Secundario'=> '#2a9d8f',
        'Técnica'   => '#0d1b2a',
        'Orientada' => '#457B9D',
        'Global'    => '#6d6875',
        default     => '#888',
    };
}
function fecha_larga(string $fecha): string {
    $meses = ['', 'enero','febrero','marzo','abril','mayo','junio',
              'julio','agosto','septiembre','octubre','noviembre','diciembre'];
    $t = strtotime($fecha);
    return date('j', $t) . ' de ' . $meses[(int)date('n', $t)] . ' de ' . date('Y', $t);
}

$color = etiqueta_color($nov['etiqueta']);
$portada = $imagenes[0]['url_imagen'] ?? null;

$page_title      = (string) ($nov['titulo']);
$page_desc       = (string) (mb_substr(strip_tags((string)$nov['descripcion']), 0, 180));
$nav_active_link = 'novedad.php';
$header_compacto = true;
$page_style = <<<'CSS'
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    /* ── BREADCRUMB ── */
    .breadcrumb {
      background: var(--blue-dark);
      padding: .7rem 2rem;
      display: flex;
      align-items: center;
      gap: .5rem;
      font-size: .78rem;
      border-bottom: 1px solid rgba(255,255,255,.1);
    }
    .breadcrumb a {
      color: rgba(255,255,255,.6);
      text-decoration: none;
      transition: color .18s;
    }
    .breadcrumb a:hover { color: #fff; }
    .breadcrumb-sep {
      color: rgba(255,255,255,.3);
      font-size: .7rem;
    }
    .breadcrumb-current {
      color: rgba(255,255,255,.9);
      font-weight: 600;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 340px;
    }

    /* ── LAYOUT ── */
    .nov-detail-wrap {
      max-width: 860px;
      margin: 0 auto;
      padding: 2.5rem 2rem 5rem;
    }

    /* ── CABECERA DE LA NOVEDAD ── */
    .nov-detail-header {
      margin-bottom: 2rem;
    }
    .nov-detail-badge {
      display: inline-block;
      color: #fff;
      font-size: .7rem;
      font-weight: 800;
      letter-spacing: .1em;
      text-transform: uppercase;
      padding: .3rem .85rem;
      margin-bottom: 1rem;
    }
    .nov-detail-title {
      font-family: var(--font-display);
      font-size: clamp(1.8rem, 4vw, 2.6rem);
      line-height: 1.15;
      color: var(--blue-dark);
      margin-bottom: .75rem;
    }
    .nov-detail-meta {
      font-size: .8rem;
      color: var(--gray-500);
      display: flex;
      align-items: center;
      gap: .75rem;
      flex-wrap: wrap;
    }
    .nov-detail-meta .meta-sep { opacity: .4; }

    /* ── IMAGEN PORTADA ── */
    .nov-detail-portada {
      width: 100%;
      aspect-ratio: 16/8;
      object-fit: cover;
      display: block;
      margin-bottom: 2rem;
    }
    .nov-detail-portada-placeholder {
      width: 100%;
      aspect-ratio: 16/8;
      background: linear-gradient(135deg, var(--gray-200), #dde3ec);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 3rem;
      opacity: .3;
      margin-bottom: 2rem;
    }

    /* ── DESCRIPCIÓN ── */
    .nov-detail-desc {
      font-size: 1.05rem;
      line-height: 1.85;
      color: #333;
      margin-bottom: 2.5rem;
      white-space: pre-line;  /* respeta saltos de línea de la BD */
    }

    /* ── GALERÍA ── */
    .nov-gallery-title {
      font-family: var(--font-display);
      font-size: 1.1rem;
      color: var(--blue-dark);
      margin-bottom: 1rem;
      padding-bottom: .5rem;
      border-bottom: 2px solid var(--gray-200);
    }
    .nov-gallery {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: .75rem;
      margin-bottom: 3rem;
    }
    .nov-gallery-item {
      position: relative;
      aspect-ratio: 4/3;
      overflow: hidden;
      cursor: pointer;
      background: var(--gray-200);
    }
    .nov-gallery-item img {
      width: 100%; height: 100%;
      object-fit: cover;
      display: block;
      transition: transform .4s ease;
    }
    .nov-gallery-item:hover img { transform: scale(1.05); }
    .nov-gallery-item:hover .gallery-overlay { opacity: 1; }
    .gallery-overlay {
      position: absolute; inset: 0;
      background: rgba(29,53,87,.45);
      display: flex; align-items: center; justify-content: center;
      opacity: 0; transition: opacity .3s;
      color: #fff; font-size: 1.5rem;
    }

    /* ── LIGHTBOX ── */
    .lightbox {
      display: none;
      position: fixed; inset: 0; z-index: 9999;
      background: rgba(0,0,0,.92);
      align-items: center; justify-content: center;
      flex-direction: column;
    }
    .lightbox.open { display: flex; }
    .lightbox-img {
      max-width: 90vw;
      max-height: 82vh;
      object-fit: contain;
      display: block;
    }
    .lightbox-controls {
      display: flex; align-items: center; gap: 1.5rem;
      margin-top: 1rem;
    }
    .lb-btn {
      background: rgba(255,255,255,.12);
      border: 1px solid rgba(255,255,255,.25);
      color: #fff; font-size: 1.3rem;
      width: 44px; height: 44px;
      display: flex; align-items: center; justify-content: center;
      cursor: pointer; transition: background .2s;
    }
    .lb-btn:hover { background: rgba(255,255,255,.25); }
    .lb-counter {
      font-size: .85rem; color: rgba(255,255,255,.6);
      min-width: 60px; text-align: center;
    }
    .lb-close {
      position: absolute; top: 1.25rem; right: 1.5rem;
      background: none; border: none; color: rgba(255,255,255,.7);
      font-size: 1.75rem; cursor: pointer; line-height: 1;
      transition: color .2s;
    }
    .lb-close:hover { color: #fff; }

    /* ── RELACIONADAS ── */
    .nov-relacionadas {
      border-top: 2px solid var(--gray-200);
      padding-top: 2rem;
    }
    .nov-relacionadas h3 {
      font-family: var(--font-display);
      font-size: 1.1rem;
      color: var(--blue-dark);
      margin-bottom: 1.25rem;
    }
    .relacionadas-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1rem;
    }
    .rel-card {
      text-decoration: none;
      display: block;
      overflow: hidden;
      border: 1px solid var(--gray-200);
      transition: box-shadow .25s, transform .25s;
    }
    .rel-card:hover {
      box-shadow: 0 6px 24px rgba(29,53,87,.12);
      transform: translateY(-3px);
    }
    .rel-img {
      aspect-ratio: 16/9;
      object-fit: cover;
      width: 100%; display: block;
      background: var(--gray-200);
    }
    .rel-img-placeholder {
      aspect-ratio: 16/9;
      background: linear-gradient(135deg, var(--gray-200), #dde3ec);
      display: flex; align-items: center; justify-content: center;
      font-size: 1.5rem; opacity: .3;
    }
    .rel-body { padding: .7rem .85rem .85rem; }
    .rel-badge {
      font-size: .62rem; font-weight: 800; letter-spacing: .08em;
      text-transform: uppercase; color: #fff;
      padding: .18rem .55rem; display: inline-block;
      margin-bottom: .4rem;
    }
    .rel-title {
      font-family: var(--font-display);
      font-size: .88rem; line-height: 1.3;
      color: var(--blue-dark);
      display: -webkit-box; -webkit-line-clamp: 2;
      -webkit-box-orient: vertical; overflow: hidden;
    }

    /* ── VOLVER ── */
    .btn-volver {
      display: inline-flex; align-items: center; gap: .5rem;
      color: var(--blue-dark); font-weight: 700; font-size: .85rem;
      text-decoration: none;
      margin-bottom: 1.5rem;
      transition: gap .2s;
    }
    .btn-volver:hover { gap: .25rem; }
    .btn-volver svg { width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round; }

    @media (max-width: 640px) {
      .nov-detail-wrap { padding: 1.5rem 1rem 3rem; }
      .relacionadas-grid { grid-template-columns: 1fr; }
      .nov-gallery { grid-template-columns: repeat(2, 1fr); }
    }
CSS;
require __DIR__ . '/partials/header.php';
?>
<!-- ══ BREADCRUMB ══════════════════════════════════════════ -->
<div class="breadcrumb">
  <a href="index.php">Inicio</a>
  <span class="breadcrumb-sep">›</span>
  <a href="novedades.php">Novedades</a>
  <span class="breadcrumb-sep">›</span>
  <span class="breadcrumb-current"><?= htmlspecialchars($nov['titulo']) ?></span>
</div>

<!-- ══ CONTENIDO ═══════════════════════════════════════════ -->
<div class="nov-detail-wrap">

  <a href="novedades.php" class="btn-volver">
    <svg viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
    Volver a novedades
  </a>

  <!-- Cabecera -->
  <div class="nov-detail-header">
    <span class="nov-detail-badge" style="background:<?= $color ?>"><?= htmlspecialchars($nov['etiqueta']) ?></span>
    <h1 class="nov-detail-title"><?= htmlspecialchars($nov['titulo']) ?></h1>
    <div class="nov-detail-meta">
      <span><?= fecha_larga($nov['created_at']) ?></span>
      <?php if (!empty($imagenes)): ?>
        <span class="meta-sep">·</span>
        <span><?= count($imagenes) ?> <?= count($imagenes)===1?'imagen':'imágenes' ?></span>
      <?php endif; ?>
    </div>
  </div>

  <!-- Imagen portada -->
  <?php if ($portada): ?>
    <img src="<?= htmlspecialchars($portada) ?>" alt="<?= htmlspecialchars($nov['titulo']) ?>" class="nov-detail-portada"/>
  <?php else: ?>
    <div class="nov-detail-portada-placeholder">🖼</div>
  <?php endif; ?>

  <!-- Descripción completa -->
  <p class="nov-detail-desc"><?= htmlspecialchars($nov['descripcion']) ?></p>

  <!-- Galería (si hay más de 1 imagen) -->
  <?php if (count($imagenes) > 1): ?>
  <h2 class="nov-gallery-title">Galería de imágenes</h2>
  <div class="nov-gallery">
    <?php foreach ($imagenes as $i => $img): ?>
    <div class="nov-gallery-item" onclick="openLightbox(<?= $i ?>)">
      <img src="<?= htmlspecialchars($img['url_imagen']) ?>" alt="Imagen <?= $i+1 ?>" loading="lazy"/>
      <div class="gallery-overlay">⊕</div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Novedades relacionadas -->
  <?php if (!empty($relacionadas)): ?>
  <div class="nov-relacionadas">
    <h3>Más novedades de <?= htmlspecialchars($nov['etiqueta']) ?></h3>
    <div class="relacionadas-grid">
      <?php foreach ($relacionadas as $rel):
        $rel_color = etiqueta_color($rel['etiqueta']);
      ?>
      <a href="novedad.php?id=<?= $rel['id_novedad'] ?>" class="rel-card">
        <?php if ($rel['portada']): ?>
          <img src="<?= htmlspecialchars($rel['portada']) ?>" alt="<?= htmlspecialchars($rel['titulo']) ?>" class="rel-img" loading="lazy"/>
        <?php else: ?>
          <div class="rel-img-placeholder">🖼</div>
        <?php endif; ?>
        <div class="rel-body">
          <span class="rel-badge" style="background:<?= $rel_color ?>"><?= htmlspecialchars($rel['etiqueta']) ?></span>
          <p class="rel-title"><?= htmlspecialchars($rel['titulo']) ?></p>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</div>

<!-- ══ LIGHTBOX ════════════════════════════════════════════ -->
<div class="lightbox" id="lightbox">
  <button class="lb-close" onclick="closeLightbox()">✕</button>
  <img class="lightbox-img" id="lbImg" src="" alt=""/>
  <div class="lightbox-controls">
    <button class="lb-btn" onclick="lbPrev()">&#8592;</button>
    <span class="lb-counter" id="lbCounter"></span>
    <button class="lb-btn" onclick="lbNext()">&#8594;</button>
  </div>
</div>

<!-- ══ FOOTER ══════════════════════════════════════════════ -->
<?php require __DIR__ . '/partials/footer.php';
