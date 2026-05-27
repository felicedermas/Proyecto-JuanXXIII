<?php
// ============================================================
//  Colegio Parroquial Juan XXIII — Detalle de Novedad
// ============================================================

$host    = 'localhost';
$db      = 'colegio_juan_xxiii';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (\PDOException $e) {
    die('<p style="font-family:sans-serif;padding:2rem;color:red;">Error de conexión: ' . htmlspecialchars($e->getMessage()) . '</p>');
}

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
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($nov['titulo']) ?> — Colegio Parroquial Juan XXIII</title>
  <link rel="stylesheet" href="styles.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    /* ── DROPDOWN ── */
    .nav-item.has-dropdown { position: relative; }
    .nav-dropdown-btn {
      display:flex;align-items:center;gap:.45rem;padding:.55rem 1rem;border-radius:8px;
      color:rgba(255,255,255,.85);font-weight:600;font-size:.88rem;font-family:var(--font-body);
      background:none;border:none;cursor:pointer;transition:var(--transition);white-space:nowrap;
    }
    .nav-dropdown-btn:hover,.nav-item.has-dropdown.open .nav-dropdown-btn{background:rgba(255,255,255,.12);color:#fff;}
    .dropdown-chevron{width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round;transition:transform .3s cubic-bezier(.4,0,.2,1);flex-shrink:0;}
    .nav-item.has-dropdown.open .dropdown-chevron{transform:rotate(180deg);}
    .nav-dropdown{position:absolute;top:calc(100% + .5rem);left:50%;transform:translateX(-50%) translateY(-6px);background:#1d3557;border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:.5rem;min-width:220px;box-shadow:0 12px 40px rgba(0,0,0,.4);opacity:0;pointer-events:none;transition:opacity .22s ease,transform .22s cubic-bezier(.4,0,.2,1);z-index:200;}
    .nav-item.has-dropdown.open .nav-dropdown{opacity:1;pointer-events:auto;transform:translateX(-50%) translateY(0);}
    .nav-dropdown a{display:flex;align-items:center;gap:.55rem;padding:.6rem .9rem;border-radius:8px;color:rgba(255,255,255,.8);font-size:.88rem;font-weight:600;font-family:var(--font-body);transition:background .18s,color .18s;white-space:nowrap;}
    .nav-dropdown a:hover{background:rgba(255,255,255,.12);color:#fff;}
    .nav-dropdown .dropdown-divider{height:1px;background:rgba(255,255,255,.1);margin:.35rem .4rem;}
    .nav-dropdown a .dd-sub{font-size:.72rem;font-weight:400;color:rgba(255,255,255,.45);display:block;margin-top:.05rem;}
    @media(max-width:768px){
      .hamburger{display:flex;}
      .nav-dropdown{position:static;transform:none;opacity:1;pointer-events:auto;box-shadow:none;border:none;border-radius:0;background:rgba(0,0,0,.15);padding:0 0 0 1rem;max-height:0;overflow:hidden;transition:max-height .3s ease;}
      .nav-item.has-dropdown.open .nav-dropdown{max-height:300px;}
    }

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
  </style>
</head>
<body>

<!-- ══ HEADER ══════════════════════════════════════════════ -->
<header class="site-header">
  <div class="header-inner">
    <div class="brand">
      <img src="logo.png" alt="Logo del Colegio" class="logo"/>
      <div class="brand-text">
        <span class="school-name">Colegio Parroquial Juan XXIII</span>
        <span class="school-motto">Educando desde xxxx</span>
      </div>
    </div>
    <button class="hamburger" id="menuToggle" aria-label="Abrir menú">
      <span></span><span></span><span></span>
    </button>
    <nav class="main-nav" id="mainNav">
      <ul class="nav-list">
        <li class="nav-item">
          <a href="index.html" class="nav-link">
            <svg viewBox="0 0 24 24" class="nav-icon"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>Inicio
          </a>
        </li>
        <li class="nav-item has-dropdown">
          <button class="nav-dropdown-btn">
            <svg viewBox="0 0 24 24" class="nav-icon"><path d="M22 10v6M2 10l10-5 10 5-10 5-10-5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>Niveles
            <svg class="dropdown-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <div class="nav-dropdown">
            <a href="nivel-inicial.html"><svg viewBox="0 0 24 24" class="nav-icon"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>Nivel Inicial<span class="dd-sub">Sala de 3, 4 y 5</span></a>
            <a href="nivel-primario.html"><svg viewBox="0 0 24 24" class="nav-icon"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>Nivel Primario<span class="dd-sub">1° a 6° grado</span></a>
            <div class="dropdown-divider"></div>
            <a href="nivel-secundario.html"><svg viewBox="0 0 24 24" class="nav-icon"><path d="M22 10v6M2 10l10-5 10 5-10 5-10-5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>Sec. Orientada / Técnica<span class="dd-sub">Bachillerato · 6 o 7 años</span></a>
          </div>
        </li>
        <li class="nav-item">
          <a href="index.html#pastoral" class="nav-link">
            <svg viewBox="0 0 24 24" class="nav-icon"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>Pastoral
          </a>
        </li>
        <li class="nav-item">
          <a href="novedades.php" class="nav-link" style="background:rgba(255,255,255,.15);color:#fff;">
            <svg viewBox="0 0 24 24" class="nav-icon"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>Novedades
          </a>
        </li>
      </ul>
    </nav>
  </div>
</header>

<!-- ══ BREADCRUMB ══════════════════════════════════════════ -->
<div class="breadcrumb">
  <a href="index.html">Inicio</a>
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
<footer class="site-footer">
  <div class="footer-inner">
    <div class="footer-brand">
      <img src="logo.png" alt="Logo" class="footer-logo"/>
      <p>Formando profesionales desde xxxx.</p>
    </div>
    <div class="footer-links">
      <h4>Niveles</h4>
      <ul>
        <li><a href="nivel-inicial.html">Nivel Inicial</a></li>
        <li><a href="nivel-primario.html">Nivel Primario</a></li>
        <li><a href="nivel-secundario.html">Nivel Secundario</a></li>
      </ul>
    </div>
    <div class="footer-links">
      <h4>Comunidad</h4>
      <ul>
        <li><a href="index.html#pastoral">Pastoral</a></li>
        <li><a href="novedades.php">Novedades</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <p>© 2026 Colegio Parroquial Juan XXIII. Todos los derechos reservados.</p>
  </div>
</footer>

<script>
  // ── Nav ──
  const toggle = document.getElementById('menuToggle');
  const nav    = document.getElementById('mainNav');
  if (toggle && nav) {
    toggle.addEventListener('click', () => { nav.classList.toggle('open'); toggle.classList.toggle('active'); });
  }
  document.querySelectorAll('.nav-item.has-dropdown').forEach(item => {
    item.querySelector('.nav-dropdown-btn').addEventListener('click', e => {
      e.stopPropagation();
      const isOpen = item.classList.contains('open');
      document.querySelectorAll('.nav-item.has-dropdown.open').forEach(o => o.classList.remove('open'));
      if (!isOpen) item.classList.add('open');
    });
  });
  document.addEventListener('click', () => {
    document.querySelectorAll('.nav-item.has-dropdown.open').forEach(o => o.classList.remove('open'));
  });

  // ── Lightbox ──
  const imagenes = <?= json_encode(array_column($imagenes, 'url_imagen')) ?>;
  let lbIndex = 0;

  function openLightbox(i) {
    lbIndex = i;
    updateLightbox();
    document.getElementById('lightbox').classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function closeLightbox() {
    document.getElementById('lightbox').classList.remove('open');
    document.body.style.overflow = '';
  }
  function updateLightbox() {
    document.getElementById('lbImg').src = imagenes[lbIndex];
    document.getElementById('lbCounter').textContent = (lbIndex + 1) + ' / ' + imagenes.length;
  }
  function lbPrev() { lbIndex = (lbIndex - 1 + imagenes.length) % imagenes.length; updateLightbox(); }
  function lbNext() { lbIndex = (lbIndex + 1) % imagenes.length; updateLightbox(); }

  document.addEventListener('keydown', e => {
    if (!document.getElementById('lightbox').classList.contains('open')) return;
    if (e.key === 'ArrowLeft')  lbPrev();
    if (e.key === 'ArrowRight') lbNext();
    if (e.key === 'Escape')     closeLightbox();
  });
  document.getElementById('lightbox').addEventListener('click', e => {
    if (e.target === document.getElementById('lightbox')) closeLightbox();
  });
</script>
</body>
</html>
