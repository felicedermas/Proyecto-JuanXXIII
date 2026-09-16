<?php
// ============================================================
//  Colegio Parroquial Juan XXIII — Detalle de Evento (Agenda)
// ============================================================

require_once __DIR__ . '/conexion.php';
$pdo = db();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) { header('Location: agenda.php'); exit; }

$stmt = $pdo->prepare("
    SELECT a.id_evento, a.titulo, a.descripcion, a.etiqueta, a.tipo,
           a.fecha_evento, a.hora_inicio, a.hora_fin, a.lugar, a.enlace,
           CONCAT(u.nombre, ' ', u.apellido) AS autor
    FROM agenda a
    JOIN usuarios u ON u.id_usuario = a.id_usuario
    WHERE a.id_evento = ?
");
$stmt->execute([$id]);
$ev = $stmt->fetch();
if (!$ev) { header('Location: agenda.php'); exit; }

// Próximos eventos de la misma etiqueta (excluyendo el actual)
$rel_stmt = $pdo->prepare("
    SELECT id_evento, titulo, etiqueta, tipo, fecha_evento, hora_inicio
    FROM agenda
    WHERE etiqueta = ? AND id_evento != ? AND fecha_evento >= CURDATE()
    ORDER BY fecha_evento ASC, hora_inicio ASC
    LIMIT 3
");
$rel_stmt->execute([$ev['etiqueta'], $id]);
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
function fecha_larga(string $fecha): string {
    $dias  = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    $meses = ['', 'enero','febrero','marzo','abril','mayo','junio',
              'julio','agosto','septiembre','octubre','noviembre','diciembre'];
    $t = strtotime($fecha);
    return $dias[(int)date('w',$t)] . ', ' . date('j', $t) . ' de ' . $meses[(int)date('n', $t)] . ' de ' . date('Y', $t);
}
function rango_horario(?string $ini, ?string $fin): string {
    if (!$ini) return 'Todo el día';
    $h = substr($ini, 0, 5);
    if ($fin) $h .= ' a ' . substr($fin, 0, 5);
    return $h . ' hs';
}

$color  = etiqueta_color($ev['etiqueta']);
$es_pasado = $ev['fecha_evento'] < date('Y-m-d');

$page_title      = (string) ($ev['titulo']);
$page_desc       = (string) (mb_substr(strip_tags((string)($ev['descripcion'] ?? '')), 0, 180));
$nav_active_link = 'evento.php';
$header_compacto = true;
$page_style = <<<'CSS'
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    /* ── DROPDOWN ── */
    .nav-item.has-dropdown { position: relative; }
    .nav-dropdown-btn { display:flex;align-items:center;gap:.45rem;padding:.55rem 1rem;border-radius:8px;color:rgba(255,255,255,.85);font-weight:600;font-size:.88rem;font-family:var(--font-body);background:none;border:none;cursor:pointer;transition:var(--transition);white-space:nowrap; }
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
    .breadcrumb { background: var(--blue-dark); padding: .7rem 2rem; display: flex; align-items: center; gap: .5rem; font-size: .78rem; border-bottom: 1px solid rgba(255,255,255,.1); }
    .breadcrumb a { color: rgba(255,255,255,.6); text-decoration: none; transition: color .18s; }
    .breadcrumb a:hover { color: #fff; }
    .breadcrumb-sep { color: rgba(255,255,255,.3); font-size: .7rem; }
    .breadcrumb-current { color: rgba(255,255,255,.9); font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 340px; }

    /* ── LAYOUT ── */
    .ev-wrap { max-width: 760px; margin: 0 auto; padding: 2.5rem 2rem 5rem; }

    .btn-volver { display:inline-flex; align-items:center; gap:.5rem; font-size:.8rem; font-weight:700; color:var(--gray-500); text-decoration:none; margin-bottom:1.5rem; transition:color .18s; }
    .btn-volver svg { width:16px; height:16px; stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
    .btn-volver:hover { color: var(--blue-dark); }

    .ev-top { display:flex; align-items:center; gap:.6rem; flex-wrap:wrap; margin-bottom:1rem; }
    .ev-badge { display:inline-block; color:#fff; font-size:.7rem; font-weight:800; letter-spacing:.1em; text-transform:uppercase; padding:.3rem .85rem; }
    .ev-tipo-tag { display:inline-flex; align-items:center; gap:.35rem; font-size:.74rem; font-weight:700; color:var(--gray-500); text-transform:uppercase; letter-spacing:.05em; }
    .ev-pasado-tag { font-size:.66rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:#fff; background:#9aa0a6; padding:.2rem .55rem; }

    .ev-title { font-family: var(--font-display); font-size: clamp(1.7rem, 4vw, 2.4rem); line-height:1.15; color: var(--blue-dark); margin-bottom: 1.5rem; }

    /* ── TARJETA RESUMEN ── */
    .ev-summary { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:1px; background:var(--gray-200); border:1px solid var(--gray-200); margin-bottom:2rem; }
    .ev-summary-item { background:#fff; padding:1rem 1.1rem; }
    .ev-summary-item .lbl { font-size:.62rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; color:var(--gray-500); margin-bottom:.3rem; }
    .ev-summary-item .val { font-size:.92rem; font-weight:700; color:var(--blue-dark); }

    .ev-desc { font-size:1rem; line-height:1.85; color:#444; white-space:pre-line; margin-bottom:2rem; }

    .ev-cta { display:inline-flex; align-items:center; gap:.5rem; background:var(--red); color:#fff; font-weight:800; font-size:.85rem; text-transform:uppercase; letter-spacing:.04em; padding:.85rem 1.5rem; text-decoration:none; transition:background .2s; margin-bottom:2.5rem; }
    .ev-cta:hover { background: var(--red-dark); }

    /* ── RELACIONADAS ── */
    .ev-rel h3 { font-family: var(--font-display); font-size:1.2rem; color:var(--blue-dark); margin-bottom:1rem; padding-top:1.5rem; border-top:1px solid var(--gray-200); }
    .rel-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:.75rem; }
    .rel-card { display:flex; gap:.7rem; align-items:center; background:#fff; box-shadow:0 1px 6px rgba(29,53,87,.07); padding:.75rem .9rem; text-decoration:none; transition:box-shadow .25s, transform .25s; border-left:4px solid #ccc; }
    .rel-card:hover { box-shadow:0 6px 20px rgba(29,53,87,.13); transform:translateY(-2px); }
    .rel-fecha { text-align:center; flex-shrink:0; }
    .rel-fecha .rd { font-family:var(--font-display); font-size:1.3rem; line-height:1; color:var(--blue-dark); }
    .rel-fecha .rm { font-size:.6rem; font-weight:800; text-transform:uppercase; color:var(--gray-500); }
    .rel-title { font-size:.82rem; font-weight:700; color:var(--blue-dark); line-height:1.3; }

    @media (max-width: 560px) { .ev-wrap { padding: 1.75rem 1rem 3.5rem; } }
CSS;
require __DIR__ . '/partials/header.php';
?>
<!-- ══ BREADCRUMB ══════════════════════════════════════════ -->
<div class="breadcrumb">
  <a href="index.php">Inicio</a>
  <span class="breadcrumb-sep">›</span>
  <a href="agenda.php">Agenda</a>
  <span class="breadcrumb-sep">›</span>
  <span class="breadcrumb-current"><?= htmlspecialchars($ev['titulo']) ?></span>
</div>

<!-- ══ CONTENIDO ═══════════════════════════════════════════ -->
<div class="ev-wrap">

  <a href="agenda.php" class="btn-volver">
    <svg viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
    Volver a la agenda
  </a>

  <div class="ev-top">
    <span class="ev-badge" style="background:<?= $color ?>"><?= htmlspecialchars($ev['etiqueta']) ?></span>
    <span class="ev-tipo-tag"><?= tipo_icono($ev['tipo']) ?> <?= htmlspecialchars($ev['tipo']) ?></span>
    <?php if ($es_pasado): ?><span class="ev-pasado-tag">Evento finalizado</span><?php endif; ?>
  </div>

  <h1 class="ev-title"><?= htmlspecialchars($ev['titulo']) ?></h1>

  <!-- Resumen -->
  <div class="ev-summary">
    <div class="ev-summary-item">
      <div class="lbl">Fecha</div>
      <div class="val"><?= fecha_larga($ev['fecha_evento']) ?></div>
    </div>
    <div class="ev-summary-item">
      <div class="lbl">Horario</div>
      <div class="val"><?= rango_horario($ev['hora_inicio'], $ev['hora_fin']) ?></div>
    </div>
    <?php if (!empty($ev['lugar'])): ?>
    <div class="ev-summary-item">
      <div class="lbl">Lugar</div>
      <div class="val"><?= htmlspecialchars($ev['lugar']) ?></div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Descripción -->
  <?php if (!empty($ev['descripcion'])): ?>
    <div class="ev-desc"><?= htmlspecialchars($ev['descripcion']) ?></div>
  <?php endif; ?>

  <!-- Enlace / Formulario -->
  <?php if (!empty($ev['enlace'])): ?>
    <a href="<?= htmlspecialchars($ev['enlace']) ?>" class="ev-cta" target="_blank" rel="noopener noreferrer">
      🔗 Abrir formulario / enlace
    </a>
  <?php endif; ?>

  <!-- Próximos relacionados -->
  <?php if (!empty($relacionadas)): ?>
  <div class="ev-rel">
    <h3>Próximos eventos de <?= htmlspecialchars($ev['etiqueta']) ?></h3>
    <div class="rel-grid">
      <?php foreach ($relacionadas as $rel):
        $rc = etiqueta_color($rel['etiqueta']);
        $rt = strtotime($rel['fecha_evento']);
        $rmes = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'][(int)date('n',$rt)];
      ?>
      <a href="evento.php?id=<?= $rel['id_evento'] ?>" class="rel-card" style="border-left-color:<?= $rc ?>">
        <div class="rel-fecha">
          <div class="rd"><?= date('j',$rt) ?></div>
          <div class="rm"><?= $rmes ?></div>
        </div>
        <div class="rel-title"><?= tipo_icono($rel['tipo']) ?> <?= htmlspecialchars($rel['titulo']) ?></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</div>

<!-- ══ FOOTER ══════════════════════════════════════════════ -->
<?php require __DIR__ . '/partials/footer.php';
