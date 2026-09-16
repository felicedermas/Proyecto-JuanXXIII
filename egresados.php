<?php
// ============================================================
//  Colegio Parroquial Juan XXIII — Egresados destacados
//  Requiere: XAMPP corriendo, base de datos colegio_juan_xxiii
// ============================================================

require_once __DIR__ . '/conexion.php';
$pdo = db();

// Traer todos los egresados publicados, más recientes primero
$stmt = $pdo->query(
    "SELECT id_egresado, nombre, apellido, anio_egreso, orientacion,
            subtitulo, resumen, foto_perfil, created_at
     FROM egresados
     WHERE publicado = 1
     ORDER BY created_at DESC"
);
$egresados = $stmt->fetchAll();

// Primera foto de galería como fallback de portada si no hay foto_perfil
$ids = array_column($egresados, 'id_egresado');
$fotos_extra = [];
if (!empty($ids)) {
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $fs = $pdo->prepare("SELECT id_egresado, url_imagen FROM imagenes_egresados WHERE id_egresado IN ($ph) ORDER BY id_egresado, orden ASC");
    $fs->execute($ids);
    foreach ($fs->fetchAll() as $f)
        if (!isset($fotos_extra[$f['id_egresado']])) $fotos_extra[$f['id_egresado']] = $f['url_imagen'];
}

function orientacion_color(string $o): string {
    return $o === 'Técnica' ? '#0d1b2a' : '#457B9D';
}
function tiempo_relativo(string $fecha): string {
    $diff = time() - strtotime($fecha);
    if ($diff < 60)     return 'Hace un momento';
    if ($diff < 3600)   return 'Hace ' . floor($diff/60) . ' min';
    if ($diff < 86400)  return 'Hace ' . floor($diff/3600) . ' h';
    if ($diff < 604800) return 'Hace ' . floor($diff/86400) . ' días';
    return date('d/m/Y', strtotime($fecha));
}

$page_title      = 'Egresados';
$page_desc       = 'Egresados del Colegio Parroquial Juan XXIII: historias y trayectorias de nuestros ex-alumnos.';
$nav_active      = 'comunidad';
$nav_active_link = 'egresados.php';
$header_compacto = true;
$page_style = <<<'CSS'
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    /* ── HERO INTERNO ── */
    .egr-hero {
      background: linear-gradient(135deg, #0d1b2a 0%, #1D3557 60%, #1a3a5c 100%);
      padding: 4.5rem 2rem 3.5rem;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .egr-hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(ellipse 70% 60% at 50% 120%, rgba(230,57,70,.18) 0%, transparent 70%);
      pointer-events: none;
    }
    .egr-hero-eyebrow {
      font-size: .75rem;
      font-weight: 800;
      letter-spacing: .2em;
      text-transform: uppercase;
      color: var(--blue-light);
      margin-bottom: .75rem;
    }
    .egr-hero-title {
      font-family: var(--font-display);
      font-size: clamp(2rem, 5vw, 3.2rem);
      color: #fff;
      line-height: 1.1;
      margin-bottom: .9rem;
    }
    .egr-hero-title em { font-style: italic; color: #ffb3b8; }
    .egr-hero-desc {
      color: rgba(255,255,255,.7);
      font-size: 1rem;
      max-width: 540px;
      margin: 0 auto;
    }

    /* ── LAYOUT PRINCIPAL ── */
    .egr-page {
      max-width: 1140px;
      margin: 0 auto;
      padding: 3.5rem 2rem 5rem;
    }

    /* ── CONTADOR ── */
    .egr-count {
      font-size: .8rem;
      font-weight: 700;
      color: var(--gray-500);
      letter-spacing: .06em;
      text-transform: uppercase;
      margin-bottom: 2rem;
    }
    .egr-count span { color: var(--red); }

    /* ── CARD DESTACADA (primera) ── */
    .egr-featured {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0;
      background: #fff;
      border-radius: var(--radius-lg);
      overflow: hidden;
      box-shadow: var(--shadow-md);
      margin-bottom: 2.5rem;
      text-decoration: none;
      color: inherit;
      transition: var(--transition);
    }
    .egr-featured:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }

    .egr-featured-img {
      position: relative;
      background: linear-gradient(135deg, #1D3557, #457B9D);
      min-height: 360px;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .egr-featured-img img {
      width: 100%; height: 100%;
      object-fit: cover;
      position: absolute; inset: 0;
    }
    .egr-featured-img .egr-avatar-big {
      width: 110px; height: 110px;
      border-radius: 50%;
      background: rgba(255,255,255,.15);
      border: 3px solid rgba(255,255,255,.3);
      display: flex; align-items: center; justify-content: center;
      font-family: var(--font-display);
      font-size: 2.4rem;
      font-weight: 900;
      color: #fff;
      position: relative;
      z-index: 1;
    }
    .egr-featured-body {
      padding: 2.5rem 2.25rem;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .egr-pub-label {
      font-size: .7rem;
      font-weight: 800;
      letter-spacing: .15em;
      text-transform: uppercase;
      color: var(--red);
      margin-bottom: .7rem;
    }
    .egr-orientacion-badge {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      font-size: .72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .08em;
      color: #fff;
      padding: .28rem .75rem;
      border-radius: 50px;
      margin-bottom: 1rem;
    }
    .egr-featured-title {
      font-family: var(--font-display);
      font-size: 1.9rem;
      line-height: 1.15;
      color: var(--blue-dark);
      margin-bottom: .5rem;
    }
    .egr-featured-title em { font-style: italic; }
    .egr-subtitle {
      font-size: .88rem;
      color: var(--blue-mid);
      font-weight: 600;
      margin-bottom: 1rem;
    }
    .egr-resumen {
      font-size: .92rem;
      color: #555;
      line-height: 1.7;
      -webkit-line-clamp: 4;
      display: -webkit-box;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    .egr-featured-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 1.5rem;
      padding-top: 1.25rem;
      border-top: 1px solid var(--gray-200);
    }
    .egr-anio {
      font-size: .78rem;
      color: var(--gray-500);
      font-weight: 600;
    }
    .egr-read-btn {
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      font-size: .82rem;
      font-weight: 700;
      color: var(--red);
      transition: gap .2s;
    }
    .egr-read-btn:hover { gap: .7rem; }

    /* ── GRILLA RESTO ── */
    .egr-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.5rem;
    }

    .egr-card {
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
    .egr-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-md); }

    .egr-card-img {
      position: relative;
      aspect-ratio: 4/3;
      background: linear-gradient(135deg, #1D3557, #457B9D);
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .egr-card-img img {
      width: 100%; height: 100%;
      object-fit: cover;
      position: absolute; inset: 0;
    }
    .egr-avatar {
      width: 72px; height: 72px;
      border-radius: 50%;
      background: rgba(255,255,255,.15);
      border: 2px solid rgba(255,255,255,.3);
      display: flex; align-items: center; justify-content: center;
      font-family: var(--font-display);
      font-size: 1.5rem;
      font-weight: 900;
      color: #fff;
      position: relative; z-index: 1;
    }
    .egr-card-badge {
      position: absolute;
      top: .75rem; left: .75rem;
      font-size: .65rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: .08em;
      color: #fff;
      padding: .22rem .65rem;
      border-radius: 50px;
      z-index: 2;
    }

    .egr-card-body {
      padding: 1.35rem 1.35rem 1rem;
      flex: 1;
      display: flex;
      flex-direction: column;
    }
    .egr-card-title {
      font-family: var(--font-display);
      font-size: 1.15rem;
      color: var(--blue-dark);
      margin-bottom: .25rem;
      line-height: 1.25;
    }
    .egr-card-sub {
      font-size: .8rem;
      color: var(--blue-mid);
      font-weight: 600;
      margin-bottom: .7rem;
    }
    .egr-card-resumen {
      font-size: .85rem;
      color: #666;
      line-height: 1.65;
      -webkit-line-clamp: 3;
      display: -webkit-box;
      -webkit-box-orient: vertical;
      overflow: hidden;
      flex: 1;
    }
    .egr-card-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 1rem;
      padding-top: .85rem;
      border-top: 1px solid var(--gray-200);
    }
    .egr-card-anio {
      font-size: .73rem;
      color: var(--gray-500);
      font-weight: 600;
    }
    .egr-card-arrow {
      font-size: .78rem;
      font-weight: 700;
      color: var(--red);
    }

    /* ── ESTADO VACÍO ── */
    .egr-empty {
      text-align: center;
      padding: 5rem 2rem;
      color: var(--gray-500);
    }
    .egr-empty-icon { font-size: 3rem; opacity: .3; margin-bottom: 1rem; }
    .egr-empty h3 { font-family: var(--font-display); font-size: 1.4rem; color: var(--blue-dark); margin-bottom: .5rem; }

    /* ── SEPARADOR VISUAL ── */
    .egr-section-sep {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin: 2rem 0 1.75rem;
    }
    .egr-section-sep span {
      font-size: .72rem;
      font-weight: 800;
      letter-spacing: .15em;
      text-transform: uppercase;
      color: var(--gray-500);
      white-space: nowrap;
    }
    .egr-section-sep::before,
    .egr-section-sep::after {
      content: '';
      flex: 1;
      height: 1px;
      background: var(--gray-200);
    }

    /* ── RESPONSIVE ── */
    @media(max-width:1024px) {
      .egr-grid { grid-template-columns: 1fr 1fr; }
    }
    @media(max-width:768px) {
      .egr-featured { grid-template-columns: 1fr; }
      .egr-featured-img { min-height: 220px; }
      .egr-grid { grid-template-columns: 1fr; }
    }
CSS;
require __DIR__ . '/partials/header.php';
?>
<!-- ===== HERO INTERNO ===== -->
<div class="egr-hero">
  <p class="egr-hero-eyebrow">Orgullo Juan XXIII</p>
  <h1 class="egr-hero-title">Conocemos a nuestros<br/><em>Egresados</em></h1>
  <p class="egr-hero-desc">Historias de ex-alumnos que siguieron su camino y llegaron lejos. Cada uno de ellos fue formado entre estos pasillos.</p>
</div>

<!-- ===== CONTENIDO ===== -->
<div class="egr-page">

  <?php if (empty($egresados)): ?>
    <div class="egr-empty">
      <div class="egr-empty-icon">🎓</div>
      <h3>Próximamente</h3>
      <p>Estamos preparando las primeras historias. ¡Volvé pronto!</p>
    </div>

  <?php else:
    $destacado = $egresados[0];
    $resto     = array_slice($egresados, 1);

    // Foto del destacado
    $foto_dest = $destacado['foto_perfil'] ?? $fotos_extra[$destacado['id_egresado']] ?? null;
    $iniciales_dest = strtoupper(substr($destacado['nombre'],0,1).substr($destacado['apellido'],0,1));
  ?>

  <p class="egr-count">
    <span><?= count($egresados) ?></span> histori<?= count($egresados)===1?'a':'as' ?> publicada<?= count($egresados)===1?'':'s' ?>
  </p>

  <!-- ── CARD DESTACADA ── -->
  <a href="egresado.php?id=<?= $destacado['id_egresado'] ?>" class="egr-featured">
    <div class="egr-featured-img">
      <?php if ($foto_dest): ?>
        <img src="<?= htmlspecialchars($foto_dest) ?>" alt="<?= htmlspecialchars($destacado['nombre'].' '.$destacado['apellido']) ?>"/>
      <?php else: ?>
        <div class="egr-avatar-big"><?= $iniciales_dest ?></div>
      <?php endif; ?>
    </div>
    <div class="egr-featured-body">
      <p class="egr-pub-label">✦ Última publicación</p>
      <span class="egr-orientacion-badge" style="background:<?= orientacion_color($destacado['orientacion']) ?>">
        <?= htmlspecialchars($destacado['orientacion']) ?>
      </span>
      <h2 class="egr-featured-title">Conocemos a<br/><em><?= htmlspecialchars($destacado['nombre'].' '.$destacado['apellido']) ?></em></h2>
      <p class="egr-subtitle"><?= htmlspecialchars($destacado['subtitulo']) ?></p>
      <p class="egr-resumen"><?= htmlspecialchars($destacado['resumen']) ?></p>
      <div class="egr-featured-footer">
        <span class="egr-anio">Egresado/a <?= htmlspecialchars($destacado['anio_egreso']) ?></span>
        <span class="egr-read-btn">Leer historia →</span>
      </div>
    </div>
  </a>

  <?php if (!empty($resto)): ?>
  <!-- ── SEPARADOR ── -->
  <div class="egr-section-sep"><span>Más historias</span></div>

  <!-- ── GRILLA RESTO ── -->
  <div class="egr-grid">
    <?php foreach ($resto as $egr):
      $foto = $egr['foto_perfil'] ?? $fotos_extra[$egr['id_egresado']] ?? null;
      $ini  = strtoupper(substr($egr['nombre'],0,1).substr($egr['apellido'],0,1));
    ?>
    <a href="egresado.php?id=<?= $egr['id_egresado'] ?>" class="egr-card">
      <div class="egr-card-img">
        <?php if ($foto): ?>
          <img src="<?= htmlspecialchars($foto) ?>" alt="<?= htmlspecialchars($egr['nombre'].' '.$egr['apellido']) ?>"/>
        <?php else: ?>
          <div class="egr-avatar"><?= $ini ?></div>
        <?php endif; ?>
        <span class="egr-card-badge" style="background:<?= orientacion_color($egr['orientacion']) ?>"><?= htmlspecialchars($egr['orientacion']) ?></span>
      </div>
      <div class="egr-card-body">
        <h3 class="egr-card-title">Conocemos a <?= htmlspecialchars($egr['nombre'].' '.$egr['apellido']) ?></h3>
        <p class="egr-card-sub"><?= htmlspecialchars($egr['subtitulo']) ?></p>
        <p class="egr-card-resumen"><?= htmlspecialchars($egr['resumen']) ?></p>
        <div class="egr-card-footer">
          <span class="egr-card-anio">Egresado/a <?= htmlspecialchars($egr['anio_egreso']) ?></span>
          <span class="egr-card-arrow">→</span>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php endif; ?>
</div><!-- /.egr-page -->

<!-- ===== FOOTER ===== -->
<?php require __DIR__ . '/partials/footer.php';
