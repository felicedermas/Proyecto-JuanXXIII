<?php
// ============================================================
//  Colegio Parroquial Juan XXIII — Página de Novedades
//  Requiere: XAMPP corriendo, base de datos colegio_juan_xxiii
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

$etiquetas_validas = ['Inicial', 'Primario', 'Técnica', 'Orientada', 'Global'];
$filtro = isset($_GET['etiqueta']) && in_array($_GET['etiqueta'], $etiquetas_validas)
    ? $_GET['etiqueta'] : 'todas';

$sql = "SELECT n.id_novedad, n.titulo, n.descripcion, n.etiqueta, n.created_at,
               CONCAT(u.nombre, ' ', u.apellido) AS autor, u.rol AS rol_autor
        FROM novedades n JOIN usuarios u ON u.id_usuario = n.id_usuario";
if ($filtro !== 'todas') $sql .= " WHERE n.etiqueta = :etiqueta";
$sql .= " ORDER BY n.created_at DESC";

$stmt = $pdo->prepare($sql);
if ($filtro !== 'todas') $stmt->bindParam(':etiqueta', $filtro);
$stmt->execute();
$novedades = $stmt->fetchAll();

$ids = array_column($novedades, 'id_novedad');
$imagenes_por_novedad = [];
if (!empty($ids)) {
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $img_stmt = $pdo->prepare("SELECT id_novedad, url_imagen FROM imagenes_novedades WHERE id_novedad IN ($ph) ORDER BY id_novedad, orden ASC");
    $img_stmt->execute($ids);
    foreach ($img_stmt->fetchAll() as $img)
        $imagenes_por_novedad[$img['id_novedad']][] = $img['url_imagen'];
}

// Conteos por etiqueta para el sidebar
$conteos = [];
$total_todas = 0;
foreach ($pdo->query("SELECT etiqueta, COUNT(*) as total FROM novedades GROUP BY etiqueta")->fetchAll() as $r) {
    $conteos[$r['etiqueta']] = $r['total'];
    $total_todas += $r['total'];
}

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
function tiempo_relativo(string $fecha): string {
    $diff = time() - strtotime($fecha);
    if ($diff < 60)     return 'Hace un momento';
    if ($diff < 3600)   return 'Hace ' . floor($diff/60) . ' min';
    if ($diff < 86400)  return 'Hace ' . floor($diff/3600) . ' h';
    if ($diff < 604800) return 'Hace ' . floor($diff/86400) . ' días';
    return date('d/m/Y', strtotime($fecha));
}
function iniciales(string $n): string {
    $p = explode(' ', trim($n));
    return strtoupper(substr($p[0],0,1).(isset($p[1])?substr($p[1],0,1):''));
}
function avatar_color(string $rol): string {
    return match($rol) {
        'admin'      => '#E63946',
        'directivo'  => '#1D3557',
        'secretaria' => '#457B9D',
        default      => '#6d6875',
    };
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Novedades — Colegio Parroquial Juan XXIII</title>
  <link rel="stylesheet" href="styles.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    /* ── DROPDOWN (igual al resto del sitio) ── */
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

    /* ── HERO COMPACTO ── */
    .nov-hero {
      background: var(--blue-dark);
      padding: 2rem 2rem 1.75rem;
      display: flex;
      align-items: center;
      gap: 1.5rem;
      border-bottom: 3px solid var(--red);
    }
    .nov-hero-icon {
      width: 48px; height: 48px; flex-shrink: 0;
      background: rgba(255,255,255,.1);
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.4rem;
    }
    .nov-hero-text h1 {
      font-family: var(--font-display);
      font-size: 1.5rem;
      color: #fff;
      line-height: 1.2;
    }
    .nov-hero-text h1 em { font-style: italic; color: #ffb3b8; }
    .nov-hero-text p {
      font-size: .82rem;
      color: rgba(255,255,255,.55);
      margin-top: .2rem;
    }

    /* ── LAYOUT: sidebar + contenido ── */
    .nov-layout {
      display: grid;
      grid-template-columns: 180px 1fr;
      max-width: 1200px;
      margin: 0 auto;
      min-height: calc(100vh - 78px - 88px);
      align-items: start;
    }

    /* ── SIDEBAR FILTROS ── */
    .nov-sidebar {
      position: sticky;
      top: 78px;
      padding: 2rem 1rem 2rem 1.5rem;
      border-right: 1px solid var(--gray-200);
    }
    .sidebar-label {
      font-size: .65rem;
      font-weight: 800;
      letter-spacing: .16em;
      text-transform: uppercase;
      color: var(--gray-500);
      margin-bottom: .75rem;
      padding-left: .4rem;
    }
    .filtro-list {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: .2rem;
    }
    .filtro-link {
      display: flex;
      align-items: center;
      gap: .55rem;
      padding: .45rem .7rem;
      font-size: .82rem;
      font-weight: 600;
      color: #555;
      text-decoration: none;
      border-radius: 6px;
      transition: background .18s, color .18s;
      position: relative;
    }
    .filtro-link:hover { background: var(--gray-200); color: var(--blue-dark); }
    .filtro-link.active {
      background: var(--blue-dark);
      color: #fff;
    }
    .filtro-link .f-dot {
      width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0;
    }
    .filtro-link .f-count {
      margin-left: auto;
      font-size: .68rem;
      font-weight: 700;
      background: rgba(0,0,0,.07);
      padding: .1rem .45rem;
      border-radius: 50px;
      color: inherit;
      opacity: .7;
    }
    .filtro-link.active .f-count { background: rgba(255,255,255,.2); opacity: 1; }
    .sidebar-divider {
      height: 1px;
      background: var(--gray-200);
      margin: .6rem .4rem;
    }

    /* ── ÁREA PRINCIPAL ── */
    .nov-main {
      padding: 2rem 2rem 4rem;
      background: var(--gray-100);
      min-height: 100%;
    }
    .nov-count-bar {
      font-size: .78rem;
      color: var(--gray-500);
      margin-bottom: 1.5rem;
    }
    .nov-count-bar strong { color: var(--blue-dark); }

    /* ── CARD BASE: SIN border-radius ── */
    .nov-card {
      background: #fff;
      border-radius: 0;
      overflow: hidden;
      box-shadow: 0 1px 6px rgba(29,53,87,.07);
      display: flex;
      flex-direction: column;
      transition: box-shadow .3s ease, transform .3s ease;
    }
    .nov-card:hover {
      box-shadow: 0 8px 32px rgba(29,53,87,.14);
      transform: translateY(-3px);
    }

    /* ── CARD GRANDE (primera / destacada) ── */
    .nov-card-featured {
      grid-column: 1 / -1;
      display: grid;
      grid-template-columns: 1.6fr 1fr;
    }
    .nov-card-featured .nov-img-wrap {
      aspect-ratio: 16/9;
    }
    .nov-card-featured .nov-body {
      padding: 2rem 1.75rem 1.5rem;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .nov-card-featured .nov-title {
      font-size: 1.55rem;
      line-height: 1.25;
      margin-bottom: .75rem;
    }
    .nov-card-featured .nov-desc {
      -webkit-line-clamp: 5;
      font-size: .92rem;
    }
    .featured-label {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      font-size: .68rem;
      font-weight: 800;
      letter-spacing: .12em;
      text-transform: uppercase;
      color: var(--red);
      margin-bottom: .75rem;
    }
    .featured-label::before {
      content: '';
      display: inline-block;
      width: 6px; height: 6px;
      background: var(--red);
      border-radius: 50%;
      animation: pulse-dot 2s infinite;
    }
    @keyframes pulse-dot {
      0%,100% { transform: scale(1); opacity: 1; }
      50%      { transform: scale(1.5); opacity: .6; }
    }

    /* ── GRILLA PEQUEÑAS ── */
    .nov-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.25rem;
    }

    /* ── IMAGEN ── */
    .nov-img-wrap {
      position: relative;
      aspect-ratio: 16/9;
      overflow: hidden;
      background: linear-gradient(135deg, var(--gray-200), #dde3ec);
      flex-shrink: 0;
    }
    .nov-img-wrap img {
      width: 100%; height: 100%;
      object-fit: cover;
      transition: transform .5s ease;
      display: block;
    }
    .nov-card:hover .nov-img-wrap img { transform: scale(1.04); }
    .nov-img-placeholder {
      width: 100%; height: 100%;
      display: flex; align-items: center; justify-content: center;
      font-size: 2rem; opacity: .25;
    }
    .nov-img-count {
      position: absolute; bottom: .5rem; right: .5rem;
      background: rgba(0,0,0,.6); color: #fff;
      font-size: .65rem; font-weight: 700;
      padding: .15rem .5rem; border-radius: 0;
      backdrop-filter: blur(4px);
    }
    .nov-badge {
      position: absolute; top: 0; left: 0;
      color: #fff; font-size: .65rem; font-weight: 800;
      letter-spacing: .08em; text-transform: uppercase;
      padding: .28rem .7rem;
    }

    /* ── CUERPO ── */
    .nov-body {
      padding: 1rem 1.1rem .9rem;
      flex: 1;
      display: flex; flex-direction: column; gap: .45rem;
    }
    .nov-title {
      font-family: var(--font-display);
      font-size: .98rem; line-height: 1.3;
      color: var(--blue-dark);
    }
    .nov-desc {
      font-size: .82rem; color: #666; line-height: 1.65;
      flex: 1;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    /* ── PIE ── */
    .nov-footer {
      padding: .65rem 1.1rem .8rem;
      border-top: 1px solid var(--gray-200);
      display: flex; align-items: center;
      justify-content: space-between; gap: .5rem;
    }
    .nov-autor {
      display: flex; align-items: center; gap: .5rem;
    }
    .nov-avatar {
      width: 26px; height: 26px; border-radius: 50%;
      color: #fff; font-size: .62rem; font-weight: 800;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
    }
    .nov-autor-name { font-size: .75rem; font-weight: 700; color: var(--blue-dark); }
    .nov-fecha { font-size: .72rem; color: var(--gray-500); white-space: nowrap; }

    /* ── SIN RESULTADOS ── */
    .sin-resultados {
      padding: 4rem 2rem; text-align: center; color: var(--gray-500);
      background: #fff;
    }
    .sin-resultados h3 {
      font-family: var(--font-display); font-size: 1.3rem;
      color: var(--blue-dark); margin: .75rem 0 .4rem;
    }

    /* ── RESPONSIVE ── */
    @media (max-width: 900px) {
      .nov-layout { grid-template-columns: 1fr; }
      .nov-sidebar { position: static; border-right: none; border-bottom: 1px solid var(--gray-200); padding: 1rem 1.5rem; }
      .filtro-list { flex-direction: row; flex-wrap: wrap; gap: .4rem; }
      .filtro-link { border-radius: 50px; padding: .3rem .85rem; font-size: .78rem; }
      .sidebar-divider { display: none; }
      .nov-card-featured { grid-template-columns: 1fr; }
      .nov-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 560px) {
      .nov-grid { grid-template-columns: 1fr; }
      .nov-main { padding: 1.25rem 1rem 3rem; }
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

<!-- ══ HERO COMPACTO ════════════════════════════════════════ -->
<div class="nov-hero">
  <div class="nov-hero-icon">📰</div>
  <div class="nov-hero-text">
    <h1>Novedades del <em>Colegio</em></h1>
    <p>Actividades, eventos y comunicados de nuestra comunidad.</p>
  </div>
</div>

<!-- ══ LAYOUT: SIDEBAR + CONTENIDO ════════════════════════ -->
<div class="nov-layout">

  <!-- SIDEBAR FILTROS -->
  <aside class="nov-sidebar">
    <p class="sidebar-label">Categorías</p>
    <ul class="filtro-list">
      <li>
        <a href="novedades.php" class="filtro-link <?= $filtro==='todas'?'active':'' ?>">
          <span class="f-dot" style="background:var(--blue-dark)"></span>
          Todas
          <span class="f-count"><?= $total_todas ?></span>
        </a>
      </li>
      <li><div class="sidebar-divider"></div></li>
      <?php
      $etiq_config = [
        'Inicial'   => '#E63946',
        'Primario'  => '#1D3557',
        'Técnica'   => '#0d1b2a',
        'Orientada' => '#457B9D',
        'Global'    => '#6d6875',
      ];
      foreach ($etiq_config as $etiq => $color): ?>
      <li>
        <a href="novedades.php?etiqueta=<?= urlencode($etiq) ?>"
           class="filtro-link <?= $filtro===$etiq?'active':'' ?>"
           <?= $filtro===$etiq ? "style=\"background:{$color};\"" : '' ?>>
          <span class="f-dot" style="background:<?= $color ?>"></span>
          <?= htmlspecialchars($etiq) ?>
          <span class="f-count"><?= $conteos[$etiq] ?? 0 ?></span>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
  </aside>

  <!-- CONTENIDO PRINCIPAL -->
  <main class="nov-main">
    <p class="nov-count-bar">
      <strong><?= count($novedades) ?></strong> <?= count($novedades)===1?'novedad':'novedades' ?>
      <?= $filtro!=='todas' ? ' · <strong>'.htmlspecialchars($filtro).'</strong>' : '' ?>
    </p>

    <?php if (empty($novedades)): ?>
      <div class="sin-resultados">
        <div style="font-size:2.5rem;opacity:.3">📭</div>
        <h3>Sin novedades</h3>
        <p>No hay publicaciones en esta categoría todavía.</p>
      </div>
    <?php else:
      $destacada = $novedades[0];
      $resto     = array_slice($novedades, 1);
      $d_color   = etiqueta_color($destacada['etiqueta']);
      $d_imgs    = $imagenes_por_novedad[$destacada['id_novedad']] ?? [];
      $d_primera = $d_imgs[0] ?? null;
    ?>

    <!-- ── CARD DESTACADA ── -->
    <div style="margin-bottom:1.25rem;">
      <a href="novedad.php?id=<?= $destacada['id_novedad'] ?>" class="nov-card nov-card-featured" style="text-decoration:none;">
        <div class="nov-img-wrap">
          <span class="nov-badge" style="background:<?= $d_color ?>"><?= htmlspecialchars($destacada['etiqueta']) ?></span>
          <?php if ($d_primera): ?>
            <img src="<?= htmlspecialchars($d_primera) ?>" alt="<?= htmlspecialchars($destacada['titulo']) ?>" loading="lazy"/>
            <?php if (count($d_imgs)>1): ?>
              <span class="nov-img-count">+<?= count($d_imgs)-1 ?> foto<?= count($d_imgs)>2?'s':'' ?></span>
            <?php endif; ?>
          <?php else: ?>
            <div class="nov-img-placeholder">🖼</div>
          <?php endif; ?>
        </div>
        <div class="nov-body">
          <div>
            <div class="featured-label">Última publicación</div>
            <h2 class="nov-title nov-card-featured .nov-title"><?= htmlspecialchars($destacada['titulo']) ?></h2>
            <p class="nov-desc" style="-webkit-line-clamp:5;margin-top:.5rem;"><?= htmlspecialchars($destacada['descripcion']) ?></p>
          </div>
          <div class="nov-footer" style="border-top:1px solid var(--gray-200);margin-top:1.25rem;padding:1rem 0 0;">
            <span class="nov-fecha"><?= tiempo_relativo($destacada['created_at']) ?></span>
          </div>
        </div>
      </a>
    </div>

    <!-- ── GRILLA RESTO ── -->
    <?php if (!empty($resto)): ?>
    <div class="nov-grid">
      <?php foreach ($resto as $nov):
        $color   = etiqueta_color($nov['etiqueta']);
        $imgs    = $imagenes_por_novedad[$nov['id_novedad']] ?? [];
        $primera = $imgs[0] ?? null;
      ?>
      <a href="novedad.php?id=<?= $nov['id_novedad'] ?>" class="nov-card" style="text-decoration:none;">
        <div class="nov-img-wrap">
          <span class="nov-badge" style="background:<?= $color ?>"><?= htmlspecialchars($nov['etiqueta']) ?></span>
          <?php if ($primera): ?>
            <img src="<?= htmlspecialchars($primera) ?>" alt="<?= htmlspecialchars($nov['titulo']) ?>" loading="lazy"/>
            <?php if (count($imgs)>1): ?>
              <span class="nov-img-count">+<?= count($imgs)-1 ?></span>
            <?php endif; ?>
          <?php else: ?>
            <div class="nov-img-placeholder">🖼</div>
          <?php endif; ?>
        </div>
        <div class="nov-body">
          <h2 class="nov-title"><?= htmlspecialchars($nov['titulo']) ?></h2>
          <p class="nov-desc"><?= htmlspecialchars($nov['descripcion']) ?></p>
        </div>
        <div class="nov-footer">
          <span class="nov-fecha"><?= tiempo_relativo($nov['created_at']) ?></span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php endif; ?>
  </main>

</div><!-- /.nov-layout -->

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
</script>
</body>
</html>
