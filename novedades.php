<?php
// ============================================================
//  Colegio Parroquial Juan XXIII — Página de Novedades
//  Requiere: XAMPP corriendo, base de datos colegio_juan_xxiii
// ============================================================

require_once __DIR__ . '/conexion.php';
$pdo = db();

$etiquetas_validas = ['Inicial', 'Primario', 'Secundario', 'Técnica', 'Orientada', 'Global'];
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
        'Secundario'=> '#2a9d8f',
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

$page_title      = 'Novedades';
$page_desc       = 'Novedades del Colegio Parroquial Juan XXIII: comunicados, actividades y noticias de los tres niveles.';
$nav_active_link = 'novedades.php';
$header_compacto = true;
$page_style = <<<'CSS'
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    /* ── TÍTULO DE PÁGINA (integrado en el contenido) ── */
    .nov-page-title {
      margin-bottom: 1.75rem;
    }
    .nov-page-title h1 {
      font-family: var(--font-display);
      font-size: 2.6rem;
      line-height: 1.1;
      color: var(--blue-dark);
    }
    .nov-page-title h1 em { font-style: italic; color: var(--red); }
    .nov-page-title p {
      font-size: 1rem;
      color: var(--gray-500);
      margin-top: .5rem;
    }

    /* ── LAYOUT: sidebar + contenido (a todo el ancho) ── */
    .nov-layout {
      display: grid;
      grid-template-columns: 200px 1fr;
      width: 100%;
      min-height: calc(100vh - 110px);
      align-items: stretch;
    }

    /* ── SIDEBAR FILTROS (pegado al margen izquierdo) ── */
    .nov-sidebar {
      position: sticky;
      top: 78px;
      padding: 2.5rem 1.25rem 2rem 1.5rem;
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
      padding: 2.5rem 2.25rem 2rem;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .nov-card-featured .nov-title {
      font-size: 2.1rem;
      line-height: 1.2;
      margin-bottom: .9rem;
    }
    .nov-card-featured .nov-desc {
      -webkit-line-clamp: 6;
      font-size: 1.05rem;
    }
    .featured-label {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      font-size: .74rem;
      font-weight: 800;
      letter-spacing: .12em;
      text-transform: uppercase;
      color: var(--red);
      margin-bottom: .9rem;
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
      grid-template-columns: repeat(2, 1fr);
      gap: 1.75rem;
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
      color: #fff; font-size: .72rem; font-weight: 800;
      letter-spacing: .08em; text-transform: uppercase;
      padding: .35rem .85rem;
    }

    /* ── CUERPO ── */
    .nov-body {
      padding: 1.4rem 1.5rem 1.1rem;
      flex: 1;
      display: flex; flex-direction: column; gap: .55rem;
    }
    .nov-title {
      font-family: var(--font-display);
      font-size: 1.3rem; line-height: 1.3;
      color: var(--blue-dark);
    }
    .nov-desc {
      font-size: .95rem; color: #666; line-height: 1.65;
      flex: 1;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    /* ── PIE ── */
    .nov-footer {
      padding: .85rem 1.5rem 1rem;
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
    .nov-fecha { font-size: .82rem; color: var(--gray-500); white-space: nowrap; }

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
      .nov-card-featured .nov-title { font-size: 1.7rem; }
      .nov-page-title h1 { font-size: 2.1rem; }
    }
    @media (max-width: 640px) {
      .nov-grid { grid-template-columns: 1fr; }
      .nov-main { padding: 1.5rem 1.25rem 3rem; }
      .nov-page-title h1 { font-size: 1.8rem; }
    }
CSS;
require __DIR__ . '/partials/header.php';
?>
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
        'Secundario'=> '#2a9d8f',
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
    <div class="nov-page-title">
      <h1>Novedades del <em>Colegio</em></h1>
      <p>Actividades, eventos y comunicados de nuestra comunidad.</p>
    </div>

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
<?php require __DIR__ . '/partials/footer.php';
