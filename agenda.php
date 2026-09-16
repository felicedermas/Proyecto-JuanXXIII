<?php
// ============================================================
//  Colegio Parroquial Juan XXIII — Agenda / Calendario
//  Requiere: XAMPP corriendo, base de datos colegio_juan_xxiii
//  Tabla: agenda  (ver agenda.sql)
// ============================================================

require_once __DIR__ . '/conexion.php';
$pdo = db();

$etiquetas_validas = ['Inicial', 'Primario', 'Secundario', 'Técnica', 'Orientada', 'Global'];
$filtro = isset($_GET['etiqueta']) && in_array($_GET['etiqueta'], $etiquetas_validas)
    ? $_GET['etiqueta'] : 'todas';

// ¿Mostrar también eventos pasados?  (?ver=todos)
$ver_pasados = isset($_GET['ver']) && $_GET['ver'] === 'todos';

$sql = "SELECT a.id_evento, a.titulo, a.descripcion, a.etiqueta, a.tipo,
               a.fecha_evento, a.hora_inicio, a.hora_fin, a.lugar, a.enlace,
               CONCAT(u.nombre, ' ', u.apellido) AS autor
        FROM agenda a JOIN usuarios u ON u.id_usuario = a.id_usuario";

$cond = [];
if (!$ver_pasados)        $cond[] = "a.fecha_evento >= CURDATE()";
if ($filtro !== 'todas')  $cond[] = "a.etiqueta = :etiqueta";
if ($cond) $sql .= " WHERE " . implode(' AND ', $cond);

// Próximos primero cuando miramos hacia adelante; pasados más recientes primero al ver todo
$sql .= $ver_pasados
    ? " ORDER BY a.fecha_evento DESC, a.hora_inicio ASC"
    : " ORDER BY a.fecha_evento ASC, a.hora_inicio ASC";

$stmt = $pdo->prepare($sql);
if ($filtro !== 'todas') $stmt->bindParam(':etiqueta', $filtro);
$stmt->execute();
$eventos = $stmt->fetchAll();

// ── Agrupar por fecha: SOLO quedan las fechas que tienen eventos ──
$por_fecha = [];
foreach ($eventos as $ev) {
    $por_fecha[$ev['fecha_evento']][] = $ev;
}

// Conteos por etiqueta para el sidebar (respetando si se ven pasados o no)
$cond_count = $ver_pasados ? "" : " WHERE fecha_evento >= CURDATE()";
$conteos = [];
$total_todas = 0;
foreach ($pdo->query("SELECT etiqueta, COUNT(*) as total FROM agenda$cond_count GROUP BY etiqueta")->fetchAll() as $r) {
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
function tipo_icono(string $t): string {
    return match($t) {
        'Acto'        => '🎤',
        'Formulario'  => '📝',
        'Reunión'     => '👥',
        'Examen'      => '📚',
        'Feriado'     => '🏖',
        'Inscripción' => '🗂',
        default       => '📌',
    };
}
function dia_semana(int $w): string {
    return ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'][$w];
}
function mes_corto(int $m): string {
    return ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'][$m];
}
function rango_horario(?string $ini, ?string $fin): string {
    if (!$ini) return 'Todo el día';
    $h = substr($ini, 0, 5);
    if ($fin) $h .= ' – ' . substr($fin, 0, 5);
    return $h . ' hs';
}
$hoy = date('Y-m-d');

$page_title      = 'Agenda';
$page_desc       = 'Agenda institucional del Colegio Parroquial Juan XXIII: actos, reuniones, exámenes y fechas importantes.';
$nav_active_link = 'agenda.php';
$header_compacto = true;
$page_style = <<<'CSS'
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

    /* ════════════════════════════════════════════════════════
       AGENDA — VISTA CARRUSEL A PANTALLA COMPLETA
       ════════════════════════════════════════════════════════ */
    html, body { height: 100%; }
    body { overflow: hidden; }   /* la agenda ocupa toda la ventana */

    /* ── HEADER FIJO (siempre visible) ──
       La línea roja inferior ya la dibuja .site-header::after en styles.css;
       no se agrega un border-bottom acá para que no quede duplicada. */
    .site-header {
      position: fixed; top: 0; left: 0; right: 0; z-index: 500;
    }

    /* ── CONTENEDOR FULL-SCREEN (debajo del header fijo) ── */
    .ag-stage {
      position: fixed; left: 0; right: 0; bottom: 0;
      top: var(--header-alto, 110px);
      display: flex; flex-direction: row;
      background: var(--gray-100);
    }

    /* ── BARRA DE FILTROS (columna vertical, lado izquierdo) ── */
    .ag-topbar {
      flex-shrink: 0;
      width: 210px;
      display: flex; flex-direction: column; align-items: stretch; gap: .9rem;
      padding: 1.5rem 1.1rem;
      background: var(--blue-dark);
      border-right: 3px solid var(--red);
      overflow-y: auto;
    }
    .ag-topbar-title {
      display: flex; align-items: center; gap: .65rem;
      color: #fff; flex-shrink: 0; order: -1;       /* título arriba de todo */
      padding-bottom: .9rem; border-bottom: 1px solid rgba(255,255,255,.12);
    }
    .ag-topbar-title .ico {
      width: 38px; height: 38px; border-radius: 9px; flex-shrink: 0;
      background: rgba(255,255,255,.1);
      display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
    }
    .ag-topbar-title h1 { font-family: var(--font-display); font-size: 1.15rem; line-height: 1.15; }
    .ag-topbar-title h1 em { font-style: italic; color: #ffb3b8; }

    .filtro-list {
      list-style: none; display: flex; flex-direction: column; align-items: stretch; gap: .35rem;
    }
    .filtro-link {
      display: flex; align-items: center; gap: .5rem;
      padding: .5rem .8rem; font-size: .82rem; font-weight: 700;
      color: rgba(255,255,255,.8); text-decoration: none; border-radius: 8px;
      background: rgba(255,255,255,.08);
      transition: background .18s, color .18s;
    }
    .filtro-link:hover { background: rgba(255,255,255,.18); color: #fff; }
    .filtro-link.active { background: #fff; color: var(--blue-dark); }
    .filtro-link .f-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .filtro-link .f-count {
      margin-left: auto; font-size: .66rem; font-weight: 800;
      background: rgba(0,0,0,.18); padding: .08rem .42rem; border-radius: 50px;
    }
    .filtro-link.active .f-count { background: var(--gray-200); }

    .ver-toggle {
      display: flex; flex-direction: column; align-items: stretch; gap: .5rem;
      margin-top: auto;                            /* empuja el toggle al fondo */
      padding-top: .9rem; border-top: 1px solid rgba(255,255,255,.12);
    }
    .ver-toggle a {
      font-size: .76rem; font-weight: 700; text-decoration: none;
      color: rgba(255,255,255,.6); transition: color .18s;
    }
    .ver-toggle a:hover { color: #fff; }
    .ver-toggle a.on { color: #ffb3b8; }

    /* ── ZONA DEL CARRUSEL ── */
    .ag-viewport {
      position: relative; flex: 1; min-width: 0;
      display: flex; align-items: stretch;
    }

    /* Pista que scrollea horizontalmente */
    .ag-track {
      flex: 1; display: flex; gap: 1.25rem;
      overflow-x: auto; overflow-y: hidden;
      scroll-behavior: smooth; scroll-snap-type: x mandatory;
      padding: 1.75rem 6rem 1.5rem;          /* deja aire para las flechas */
      align-items: stretch;
    }
    .ag-track::-webkit-scrollbar { height: 8px; }
    .ag-track::-webkit-scrollbar-thumb { background: var(--gray-200); border-radius: 50px; }

    /* ── TARJETA = UNA FECHA (columna vertical) ── */
    .ag-day {
      scroll-snap-align: center;
      flex: 0 0 clamp(300px, 30vw, 380px);
      display: flex; flex-direction: column;
      background: #fff; border: 1px solid var(--gray-200);
      border-top: 4px solid var(--blue-dark);
      border-radius: 14px; overflow: hidden;
      box-shadow: 0 4px 20px rgba(29,53,87,.07);
    }
    .ag-day.is-today { border-top-color: var(--red); box-shadow: 0 6px 28px rgba(230,57,70,.18); }

    /* Encabezado de la fecha dentro de la tarjeta */
    .ag-day-date {
      flex-shrink: 0;
      display: flex; align-items: center; gap: .7rem;
      padding: 1.1rem 1.25rem; border-bottom: 1px solid var(--gray-200);
      background: var(--gray-100);
    }
    .ag-day-date .d-num {
      font-family: var(--font-display); font-size: 2.2rem; line-height: 1;
      color: var(--blue-dark);
    }
    .ag-day.is-today .ag-day-date .d-num { color: var(--red); }
    .ag-day-date .d-txt { display: flex; flex-direction: column; }
    .ag-day-date .d-sem { font-size: .9rem; font-weight: 800; color: var(--blue-dark); text-transform: capitalize; }
    .ag-day-date .d-mes { font-size: .68rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--gray-500); }
    .ag-day-date .d-hoy {
      margin-left: auto; font-size: .58rem; font-weight: 800; letter-spacing: .08em;
      text-transform: uppercase; color: #fff; background: var(--red);
      padding: .2rem .55rem; border-radius: 50px;
    }

    /* Eventos apilados verticalmente, con scroll propio si sobran */
    .ag-day-events {
      flex: 1; min-height: 0; overflow-y: auto;
      display: flex; flex-direction: column; gap: .75rem;
      padding: 1rem 1.1rem 1.25rem;
    }
    .ag-day-events::-webkit-scrollbar { width: 6px; }
    .ag-day-events::-webkit-scrollbar-thumb { background: var(--gray-200); border-radius: 50px; }

    /* ── EVENTO (cada uno en su fila, vertical) ── */
    .ag-event {
      position: relative; display: block; text-decoration: none;
      padding: .8rem .95rem .85rem;
      background: #fff; border: 1px solid var(--gray-200);
      border-left: 4px solid var(--blue-dark); border-radius: 9px;
      transition: box-shadow .25s ease, transform .2s ease;
    }
    .ag-event:hover { box-shadow: 0 6px 20px rgba(29,53,87,.13); transform: translateY(-2px); }
    /* Evento único: ocupa toda la ficha, sin blanco abajo */
    .ag-event:only-child {
      flex: 1; display: flex; flex-direction: column;
    }
    .ag-event:only-child .ag-event-desc {
      -webkit-line-clamp: unset; flex: 1;
    }
    .ag-event-top { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; margin-bottom: .4rem; }
    .ag-event-icon { font-size: 1rem; }
    .ag-event-badge {
      color: #fff; font-size: .58rem; font-weight: 800; letter-spacing: .07em;
      text-transform: uppercase; padding: .16rem .5rem; border-radius: 4px;
    }
    .ag-event-tipo { font-size: .65rem; font-weight: 700; color: var(--gray-500); text-transform: uppercase; letter-spacing: .05em; }
    .ag-event-hora { margin-left: auto; font-size: .72rem; font-weight: 700; color: var(--blue-dark); white-space: nowrap; }
    .ag-event-title { font-family: var(--font-display); font-size: 1rem; line-height: 1.3; color: var(--blue-dark); margin-bottom: .2rem; }
    .ag-event-desc {
      font-size: .8rem; color: #666; line-height: 1.55;
      display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
    }
    .ag-event-meta { display: flex; align-items: center; gap: .9rem; flex-wrap: wrap; font-size: .72rem; color: var(--gray-500); margin-top: .45rem; }
    .ag-event-meta span { display: inline-flex; align-items: center; gap: .3rem; }
    .ag-event-link {
      display: inline-flex; align-items: center; gap: .35rem;
      font-size: .72rem; font-weight: 800; color: var(--red);
      text-transform: uppercase; letter-spacing: .04em;
    }

    /* ── FLECHAS DE NAVEGACIÓN ── */
    .ag-arrow {
      position: absolute; top: 50%; transform: translateY(-50%);
      z-index: 10; width: 52px; height: 52px; border-radius: 50%;
      background: #fff; border: 1px solid var(--gray-200);
      box-shadow: 0 6px 22px rgba(29,53,87,.18);
      display: flex; align-items: center; justify-content: center;
      cursor: pointer; transition: background .2s, transform .2s, opacity .2s;
    }
    .ag-arrow:hover { background: var(--blue-dark); transform: translateY(-50%) scale(1.06); }
    .ag-arrow:hover svg { stroke: #fff; }
    .ag-arrow svg { width: 24px; height: 24px; stroke: var(--blue-dark); fill: none; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; }
    .ag-arrow.prev { left: 1.25rem; }
    .ag-arrow.next { right: 1.25rem; }
    .ag-arrow[disabled] { opacity: .35; pointer-events: none; }

    /* ── SIN RESULTADOS ── */
    .sin-resultados { margin: auto; padding: 4rem 2rem; text-align: center; color: var(--gray-500); }
    .sin-resultados h3 { font-family: var(--font-display); font-size: 1.3rem; color: var(--blue-dark); margin: .75rem 0 .4rem; }

    /* ── RESPONSIVE ── */
    @media (max-width: 768px) {
      .ag-stage { flex-direction: column; }
      .ag-topbar {
        width: auto; flex-direction: row; align-items: center; flex-wrap: wrap;
        gap: .6rem; padding: .7rem 1rem;
        border-right: none; border-bottom: 3px solid var(--red);
      }
      .ag-topbar-title { order: 0; padding-bottom: 0; border-bottom: none; }
      .ag-topbar-title h1 { font-size: 1rem; }
      .filtro-list { flex-direction: row; flex-wrap: wrap; }
      .filtro-link { border-radius: 50px; padding: .35rem .8rem; font-size: .78rem; }
      .filtro-link .f-count { margin-left: 0; }
      .ver-toggle {
        flex-direction: row; gap: .9rem; margin-top: 0; margin-left: auto;
        padding-top: 0; border-top: none;
      }
      .ag-track { padding: 2.5rem 4.5rem 1.25rem; }
      .ag-day { flex-basis: 82vw; }
      .ag-arrow { width: 44px; height: 44px; }
      .ag-arrow.prev { left: .5rem; } .ag-arrow.next { right: .5rem; }
    }
CSS;
require __DIR__ . '/partials/header.php';
?>
<!-- ══ STAGE: AGENDA A PANTALLA COMPLETA ══════════════════════ -->
<div class="ag-stage">

  <!-- BARRA DE FILTROS (al pie): filtros · título · toggle -->
  <div class="ag-topbar">
    <ul class="filtro-list">
      <li>
        <a href="agenda.php<?= $ver_pasados ? '?ver=todos' : '' ?>" class="filtro-link <?= $filtro==='todas'?'active':'' ?>">
          <span class="f-dot" style="background:#fff"></span>
          Todas
          <span class="f-count"><?= $total_todas ?></span>
        </a>
      </li>
      <?php
      $etiq_config = [
        'Inicial'   => '#E63946',
        'Primario'  => '#1D3557',
        'Secundario'=> '#2a9d8f',
        'Técnica'   => '#0d1b2a',
        'Orientada' => '#457B9D',
        'Global'    => '#6d6875',
      ];
      foreach ($etiq_config as $etiq => $color):
        $qs = 'etiqueta=' . urlencode($etiq) . ($ver_pasados ? '&ver=todos' : '');
      ?>
      <li>
        <a href="agenda.php?<?= $qs ?>" class="filtro-link <?= $filtro===$etiq?'active':'' ?>">
          <span class="f-dot" style="background:<?= $color ?>"></span>
          <?= htmlspecialchars($etiq) ?>
          <span class="f-count"><?= $conteos[$etiq] ?? 0 ?></span>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>

    <div class="ag-topbar-title">
      <h1>Agenda del <em>Colegio</em></h1>
    </div>

    <div class="ver-toggle">
      <?php $base_q = $filtro !== 'todas' ? 'etiqueta=' . urlencode($filtro) : ''; ?>
      <a href="agenda.php<?= $base_q ? '?'.$base_q : '' ?>" class="<?= !$ver_pasados ? 'on' : '' ?>">▸ Próximos</a>
      <a href="agenda.php?<?= $base_q ? $base_q.'&' : '' ?>ver=todos" class="<?= $ver_pasados ? 'on' : '' ?>">▸ Ver todo</a>
    </div>
  </div>

  <!-- VIEWPORT DEL CARRUSEL -->
  <div class="ag-viewport">

    <?php if (empty($por_fecha)): ?>
      <div class="sin-resultados">
        <div style="font-size:2.5rem;opacity:.3">🗓</div>
        <h3>Sin eventos en la agenda</h3>
        <p>No hay fechas programadas <?= $filtro!=='todas' ? 'en esta categoría' : '' ?> por ahora.</p>
      </div>
    <?php else: ?>

      <button class="ag-arrow prev" id="agPrev" aria-label="Anterior">
        <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
      </button>
      <button class="ag-arrow next" id="agNext" aria-label="Siguiente">
        <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
      </button>

      <div class="ag-track" id="agTrack">
      <?php foreach ($por_fecha as $fecha => $items):
        $t      = strtotime($fecha);
        $es_hoy = ($fecha === $hoy);
      ?>
        <div class="ag-day <?= $es_hoy ? 'is-today' : '' ?>">
          <!-- Encabezado de fecha -->
          <div class="ag-day-date">
            <span class="d-num"><?= date('j', $t) ?></span>
            <span class="d-txt">
              <span class="d-sem"><?= dia_semana((int)date('w', $t)) ?></span>
              <span class="d-mes"><?= mes_corto((int)date('n', $t)) ?> <?= date('Y', $t) ?></span>
            </span>
            <?php if ($es_hoy): ?><span class="d-hoy">Hoy</span><?php endif; ?>
          </div>

          <!-- Eventos del día (apilados verticalmente) -->
          <div class="ag-day-events">
            <?php foreach ($items as $ev):
              $color = etiqueta_color($ev['etiqueta']);
            ?>
            <a href="evento.php?id=<?= $ev['id_evento'] ?>" class="ag-event"
               style="border-left-color:<?= $color ?>;">
              <div class="ag-event-top">
                <span class="ag-event-icon"><?= tipo_icono($ev['tipo']) ?></span>
                <span class="ag-event-badge" style="background:<?= $color ?>"><?= htmlspecialchars($ev['etiqueta']) ?></span>
                <span class="ag-event-tipo"><?= htmlspecialchars($ev['tipo']) ?></span>
                <span class="ag-event-hora"><?= rango_horario($ev['hora_inicio'], $ev['hora_fin']) ?></span>
              </div>
              <div class="ag-event-title"><?= htmlspecialchars($ev['titulo']) ?></div>
              <?php if (!empty($ev['descripcion'])): ?>
                <div class="ag-event-desc"><?= htmlspecialchars($ev['descripcion']) ?></div>
              <?php endif; ?>
              <div class="ag-event-meta">
                <?php if (!empty($ev['lugar'])): ?>
                  <span>📍 <?= htmlspecialchars($ev['lugar']) ?></span>
                <?php endif; ?>
                <?php if (!empty($ev['enlace'])): ?>
                  <span class="ag-event-link">🔗 Abrir formulario / enlace</span>
                <?php endif; ?>
              </div>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
      </div><!-- /.ag-track -->

    <?php endif; ?>
  </div><!-- /.ag-viewport -->

</div><!-- /.ag-stage -->
<?php require __DIR__ . '/partials/footer.php';
