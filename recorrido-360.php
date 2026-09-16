<?php
// ============================================================
//  recorrido-360.php
//  Página pública del Recorrido Virtual 360°
//  Lee las escenas y puntos cargados desde el panel
//  (gestion_tour.php) y arma el tour estilo Street View.
// ============================================================
require_once __DIR__ . '/conexion.php';   // conexión única del sitio
$pdo = db();

$escenas = $pdo->query(
    'SELECT * FROM tour_escenas WHERE activa = 1 ORDER BY zona, orden, nombre'
)->fetchAll();

$ids = array_column($escenas, 'id_escena');
$hotspots = [];
if ($ids) {
    $in = implode(',', array_fill(0, count($ids), '?'));
    $st = $pdo->prepare(
        "SELECT h.*, d.nombre AS destino_nombre, d.activa AS destino_activa
           FROM tour_hotspots h
           LEFT JOIN tour_escenas d ON d.id_escena = h.id_destino
          WHERE h.id_escena IN ($in)"
    );
    $st->execute($ids);
    foreach ($st->fetchAll() as $h) {
        // Ignorar transiciones hacia escenas ocultas o eliminadas
        if ($h['tipo'] === 'nav' && (empty($h['id_destino']) || !$h['destino_activa'])) continue;
        $hotspots[(int)$h['id_escena']][] = $h;
    }
}

// Armar la estructura del tour para el JS
$tour = [];
foreach ($escenas as $s) {
    $id  = (int)$s['id_escena'];
    $nav = $info = [];
    foreach ($hotspots[$id] ?? [] as $h) {
        if ($h['tipo'] === 'nav') {
            $nav[] = ['a' => 'esc' . (int)$h['id_destino'], 'yaw' => (float)$h['yaw'],
                      'pitch' => (float)$h['pitch'],
                      'txt' => $h['texto'] !== '' ? $h['texto'] : 'Ir a ' . $h['destino_nombre']];
        } else {
            $info[] = ['yaw' => (float)$h['yaw'], 'pitch' => (float)$h['pitch'], 'txt' => $h['texto']];
        }
    }
    $tour[] = [
        'id'     => 'esc' . $id,
        'nombre' => $s['nombre'],
        'zona'   => $s['zona'],
        'img'    => $s['url_imagen'],
        'vista'  => ['yaw' => (float)$s['yaw_inicial'], 'pitch' => (float)$s['pitch_inicial']],
        'nav'    => $nav,
        'info'   => $info,
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Recorrido Virtual 360° — Colegio Parroquial Juan XXIII</title>
<?php require __DIR__ . '/partials/favicon.php'; ?>
<link rel="stylesheet" href="pannellum/pannellum.css"/>
<script src="pannellum/pannellum.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet"/>
<style>
  :root{
    --navy:#1d3557; --navy-2:#274a76; --steel:#457b9d; --gold:#e9c46a; --ink:#12233d;
    --font-display:'Playfair Display',serif; --font-body:'Nunito',sans-serif;
  }
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  html,body{height:100%;overflow:hidden;font-family:var(--font-body);background:var(--ink)}

  .tour-header{position:fixed;top:0;left:0;right:0;height:58px;z-index:60;
    display:flex;align-items:center;gap:.9rem;padding:0 1rem;
    background:linear-gradient(90deg,var(--navy) 0%,var(--navy-2) 100%);
    border-bottom:1px solid rgba(255,255,255,.14);box-shadow:0 4px 18px rgba(0,0,0,.35)}
  .th-back{color:rgba(255,255,255,.75);text-decoration:none;font-weight:700;font-size:.8rem;
    display:flex;align-items:center;gap:.3rem}
  .th-back:hover{color:#fff}
  .th-badge{width:34px;height:34px;border-radius:50%;flex-shrink:0;background:var(--gold);color:var(--navy);
    display:grid;place-items:center;font-weight:800;font-size:.8rem;font-family:var(--font-display)}
  .th-titles{line-height:1.15;min-width:0}
  .th-school{color:#fff;font-family:var(--font-display);font-weight:700;font-size:.95rem;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .th-sub{color:rgba(255,255,255,.65);font-size:.72rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase}
  .th-toggle{margin-left:auto;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);
    color:#fff;border-radius:9px;padding:.45rem .85rem;font-family:var(--font-body);font-weight:700;
    font-size:.82rem;cursor:pointer;display:flex;align-items:center;gap:.45rem;transition:background .2s}
  .th-toggle:hover{background:rgba(255,255,255,.2)}
  .th-toggle svg{width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2.4;stroke-linecap:round;stroke-linejoin:round}

  .tour-side{position:fixed;top:58px;bottom:0;left:0;width:264px;z-index:50;
    background:rgba(18,35,61,.92);backdrop-filter:blur(8px);border-right:1px solid rgba(255,255,255,.1);
    overflow-y:auto;padding:1rem .8rem 5rem;transition:transform .3s cubic-bezier(.4,0,.2,1)}
  .tour-side.hidden{transform:translateX(-100%)}
  .side-group{margin-bottom:1.1rem}
  .side-label{color:var(--gold);font-size:.68rem;font-weight:800;letter-spacing:.16em;
    text-transform:uppercase;padding:.3rem .6rem .45rem}
  .side-btn{display:flex;align-items:center;gap:.6rem;width:100%;text-align:left;background:none;border:none;
    cursor:pointer;color:rgba(255,255,255,.82);font-family:var(--font-body);font-weight:600;font-size:.88rem;
    padding:.55rem .6rem;border-radius:9px;transition:background .18s,color .18s}
  .side-btn:hover{background:rgba(255,255,255,.1);color:#fff}
  .side-btn.active{background:var(--steel);color:#fff}
  .side-dot{width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,.35);flex-shrink:0}
  .side-btn.active .side-dot{background:var(--gold)}

  #panorama{position:fixed;inset:58px 0 0 0}
  .pnlm-container{font-family:var(--font-body)!important}

  .scene-chip{position:fixed;top:74px;right:16px;z-index:55;pointer-events:none;
    background:rgba(29,53,87,.88);backdrop-filter:blur(6px);color:#fff;border:1px solid rgba(255,255,255,.18);
    border-radius:12px;padding:.55rem 1rem;box-shadow:0 8px 24px rgba(0,0,0,.35);max-width:70vw}
  .scene-chip .sc-zone{font-size:.66rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:var(--gold)}
  .scene-chip .sc-name{font-family:var(--font-display);font-weight:700;font-size:1.05rem;line-height:1.2}

  .tour-hint{position:fixed;bottom:14px;left:50%;transform:translateX(-50%);z-index:55;pointer-events:none;
    background:rgba(18,35,61,.78);color:rgba(255,255,255,.85);border:1px solid rgba(255,255,255,.14);
    border-radius:999px;padding:.4rem 1.1rem;font-size:.76rem;font-weight:600;white-space:nowrap;transition:opacity .6s}
  .tour-hint.fade{opacity:0}

  /* Flecha de navegación (anclada a la esfera, clickeable) — más grande y visible */
  .hs-nav{width:68px;height:68px;border-radius:50%;background:rgba(29,53,87,.9);border:3px solid #fff;
    display:grid;place-items:center;cursor:pointer;box-shadow:0 6px 20px rgba(0,0,0,.5),0 0 0 0 rgba(233,196,106,.6);
    animation:pulse 2.2s infinite;transition:transform .18s,background .18s}
  .hs-nav:hover{transform:scale(1.15);background:var(--steel)}
  .hs-nav::after{content:"";width:22px;height:22px;border-top:5px solid var(--gold);border-right:5px solid var(--gold);
    transform:rotate(-45deg) translate(-3px,3px)}
  @keyframes pulse{0%{box-shadow:0 6px 20px rgba(0,0,0,.5),0 0 0 0 rgba(233,196,106,.6)}70%{box-shadow:0 6px 20px rgba(0,0,0,.5),0 0 0 20px rgba(233,196,106,0)}100%{box-shadow:0 6px 20px rgba(0,0,0,.5),0 0 0 0 rgba(233,196,106,0)}}
  .hs-info{width:38px;height:38px;border-radius:50%;background:var(--gold);border:2px solid #fff;color:var(--navy);
    display:grid;place-items:center;font-weight:800;font-family:var(--font-display);font-size:1.05rem;cursor:pointer;
    box-shadow:0 4px 14px rgba(0,0,0,.4)}
  .hs-tip{position:absolute;bottom:calc(100% + 12px);left:50%;transform:translateX(-50%);background:#fff;color:var(--ink);
    border-radius:10px;padding:.55rem .9rem;font-size:.85rem;font-weight:700;white-space:nowrap;
    box-shadow:0 8px 24px rgba(0,0,0,.35);opacity:0;pointer-events:none;transition:opacity .2s}
  .hs-wrap:hover .hs-tip{opacity:1}
  /* OJO: debe ser absolute (no relative) para no pisar el anclaje
     de Pannellum (.pnlm-hotspot-base). Con relative, las flechas
     entran al flujo normal y se corren hacia abajo. */
  .hs-wrap{position:absolute}
  .tour-vacio{position:fixed;inset:58px 0 0 0;display:grid;place-items:center;color:rgba(255,255,255,.8);
    font-size:1rem;text-align:center;padding:2rem}

  @media (max-width:720px){
    .tour-side{width:min(78vw,300px)}
    .th-school{font-size:.82rem}
    .scene-chip{top:auto;bottom:56px;right:12px;left:12px;max-width:none}
  }
  @media (prefers-reduced-motion:reduce){.hs-nav{animation:none}.tour-side{transition:none}}
</style>
</head>
<body>

<header class="tour-header">
  <a class="th-back" href="index.php" title="Volver al sitio">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    Sitio
  </a>
  <div class="th-badge">XXIII</div>
  <div class="th-titles">
    <div class="th-school">Colegio Parroquial Juan XXIII</div>
    <div class="th-sub">Recorrido Virtual 360°</div>
  </div>
  <button class="th-toggle" id="sideToggle" aria-label="Mostrar u ocultar lista de espacios">
    <svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
    <span>Espacios</span>
  </button>
</header>

<?php if (!$tour): ?>
  <div class="tour-vacio">El recorrido virtual todavía no tiene escenas publicadas.<br>Volvé a visitarnos pronto.</div>
<?php else: ?>

<nav class="tour-side" id="tourSide" aria-label="Espacios del colegio"></nav>
<div id="panorama"></div>

<div class="scene-chip" id="sceneChip">
  <div class="sc-zone" id="chipZone"></div>
  <div class="sc-name" id="chipName"></div>
</div>

<div class="tour-hint" id="tourHint">Arrastrá para mirar alrededor · Tocá las flechas para avanzar</div>

<script>
const TOUR = <?= json_encode($tour, JSON_UNESCAPED_UNICODE) ?>;

function hsNav(h){
  return { pitch:h.pitch, yaw:h.yaw, type:"scene", sceneId:h.a,
    createTooltipFunc:(el)=>{ el.classList.add("hs-wrap");
      el.innerHTML = `<div class="hs-nav" role="button" aria-label="${h.txt}"></div><div class="hs-tip">${h.txt}</div>`; },
    clickHandlerFunc:()=>viewer.loadScene(h.a) };
}
function hsInfo(h){
  return { pitch:h.pitch, yaw:h.yaw, type:"info",
    createTooltipFunc:(el)=>{ el.classList.add("hs-wrap");
      el.innerHTML = `<div class="hs-info">i</div><div class="hs-tip">${h.txt}</div>`; } };
}

const scenes = {};
TOUR.forEach(s=>{
  scenes[s.id] = { title:s.nombre, type:"equirectangular", panorama:s.img,
    yaw:s.vista.yaw, pitch:s.vista.pitch, hfov:100, minHfov:50, maxHfov:120,
    hotSpots:[...s.nav.map(hsNav), ...s.info.map(hsInfo)] };
});

const viewer = pannellum.viewer("panorama", {
  default:{ firstScene:TOUR[0].id, sceneFadeDuration:800, autoLoad:true, showControls:true, compass:false,
            hfov:100, minHfov:50, maxHfov:120 },
  scenes
});

// Menú lateral agrupado por zona
const side = document.getElementById("tourSide");
[...new Set(TOUR.map(s=>s.zona))].forEach(z=>{
  const g = document.createElement("div"); g.className="side-group";
  g.innerHTML = `<div class="side-label">${z}</div>`;
  TOUR.filter(s=>s.zona===z).forEach(s=>{
    const b = document.createElement("button");
    b.className="side-btn"; b.dataset.scene=s.id;
    b.innerHTML = `<span class="side-dot"></span>${s.nombre}`;
    b.onclick = ()=>{ viewer.loadScene(s.id); if(window.innerWidth<720) side.classList.add("hidden"); };
    g.appendChild(b);
  });
  side.appendChild(g);
});

function refresh(id){
  const s = TOUR.find(t=>t.id===id);
  document.getElementById("chipZone").textContent = s.zona;
  document.getElementById("chipName").textContent = s.nombre;
  document.querySelectorAll(".side-btn").forEach(b=>b.classList.toggle("active", b.dataset.scene===id));
}
viewer.on("scenechange", refresh);
refresh(TOUR[0].id);

document.getElementById("sideToggle").onclick = ()=> side.classList.toggle("hidden");
if(window.innerWidth<720) side.classList.add("hidden");
setTimeout(()=>document.getElementById("tourHint").classList.add("fade"), 6000);
</script>
<?php endif; ?>
</body>
</html>
