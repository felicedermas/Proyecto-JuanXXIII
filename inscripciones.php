<?php
// ============================================================
//  inscripciones.php
//  Página de Inscripciones: reúne los botones a cada formulario.
//  Es a la que lleva "Inscripciones" en el menú principal.
//  Los formularios (nombre, ícono, color) salen de
//  partials/inscripciones.php y su estado (abierto / cerrado)
//  se maneja desde el panel → Inscripciones.
// ============================================================
require_once __DIR__ . '/partials/inscripciones.php';

$page_title      = 'Inscripciones';
$page_desc       = 'Preinscripción en línea al Colegio Parroquial Juan XXIII: Jardín de Infantes, Nivel Primario, Nivel Secundario (Orientada y Técnica), hermanos, mesas de examen y solicitud de beca.';
$nav_active      = 'inscripciones';
$nav_active_link = 'inscripciones';

// Estado de cada formulario (sin base = todos cerrados, la página se ve igual)
$estados = [];
$pdo = db_opcional();
if ($pdo !== null && insc_asegurar_tablas($pdo)) {
    try {
        $estados = insc_estados_formularios($pdo);
    } catch (Throwable $ex) {
        $estados = [];
    }
}
$estado_de = fn(string $clave): array => insc_estado($estados[$clave] ?? null);

//  'nivel' = una tarjeta por nivel (jardín, primaria, secundaria).
//  'otros' = trámites que no son el ingreso a un nivel: hermanos,
//            mesas de examen y solicitud de beca. Van a lo ancho.
$formularios = insc_formularios();
$por_nivel   = array_filter($formularios, fn($f) => ($f['grupo'] ?? 'nivel') === 'nivel');
$otros       = array_filter($formularios, fn($f) => ($f['grupo'] ?? 'nivel') === 'otros');

$page_style = <<<'CSS'
  .ih-wrap { max-width: 1120px; margin: 0 auto; padding: 3rem 1.5rem 4.5rem; }

  .ih-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1.4rem; }

  .ih-card {
    position: relative; display: flex; flex-direction: column;
    background: #fff; border-radius: 20px; overflow: hidden;
    border: 1px solid rgba(29,53,87,.1); box-shadow: 0 2px 14px rgba(29,53,87,.07);
    color: inherit; text-decoration: none;
    transition: transform .28s cubic-bezier(.4,0,.2,1), box-shadow .28s cubic-bezier(.4,0,.2,1);
  }
  .ih-card:hover, .ih-card:focus-visible { transform: translateY(-5px); box-shadow: 0 20px 44px rgba(29,53,87,.18); }
  .ih-card:focus-visible { outline: 3px solid var(--acc); outline-offset: 3px; }
  .ih-card::before { content: ''; height: 5px; background: var(--acc); }

  .ih-cuerpo { display: flex; flex-direction: column; flex: 1; padding: 1.6rem 1.6rem 1.7rem; }
  .ih-top { display: flex; align-items: flex-start; justify-content: space-between; gap: .8rem; margin-bottom: 1.1rem; }
  .ih-icono {
    width: 58px; height: 58px; border-radius: 16px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 1.8rem;
    background: color-mix(in srgb, var(--acc) 11%, #fff);
  }
  .ih-estado {
    display: inline-flex; align-items: center; gap: .4rem;
    font-size: .7rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase;
    padding: .3rem .7rem; border-radius: 999px; white-space: nowrap;
  }
  .ih-estado::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
  .ih-estado.abierta { color: #1b7a44; background: #eaf7ef; }
  .ih-estado.cerrada { color: #6b7280; background: var(--gray-200); }
  .ih-estado.proxima { color: #9a6700; background: #fff4d6; }

  .ih-periodo {
    display: flex; align-items: center; gap: .45rem; margin: -.6rem 0 1.2rem;
    font-size: .86rem; font-weight: 700; color: var(--blue-dark);
  }
  .ih-periodo svg { width: 16px; height: 16px; flex-shrink: 0; fill: none; stroke: var(--acc); stroke-width: 2.2; stroke-linecap: round; stroke-linejoin: round; }
  .ih-card--ancha .ih-periodo { margin: .5rem 0 0; }

  .ih-card h2 { font-family: var(--font-display); font-size: 1.45rem; color: var(--blue-dark); line-height: 1.15; }
  .ih-card h2 em { color: var(--acc); }
  .ih-bajada { color: #5b6270; font-size: .92rem; margin: .35rem 0 1.4rem; flex: 1; }

  .ih-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
    padding: .85rem 1.2rem; border-radius: 12px; font-weight: 800; font-size: .92rem;
    color: #fff; background: var(--acc); transition: filter .2s, gap .2s;
  }
  .ih-card:hover .ih-btn { filter: brightness(.92); gap: .8rem; }
  .ih-card.is-cerrada .ih-btn { background: #fff; color: var(--blue-dark); border: 1.5px solid rgba(29,53,87,.2); }
  .ih-btn svg { width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 2.6; stroke-linecap: round; stroke-linejoin: round; }

  /* Hermanos, mesas y becas: tarjetas horizontales a lo ancho */
  .ih-otros { margin-top: 2.8rem; }
  .ih-otros-titulo {
    font-family: var(--font-display); font-size: 1.5rem; color: var(--blue-dark);
    text-align: center; margin-bottom: .4rem;
  }
  .ih-otros-bajada { text-align: center; color: #5b6270; font-size: .93rem; margin-bottom: 1rem; }
  .ih-card--ancha { margin-top: 1.4rem; }
  .ih-card--ancha .ih-cuerpo { flex-direction: row; align-items: center; gap: 1.4rem; padding: 1.5rem 1.8rem; }
  .ih-card--ancha .ih-top { margin: 0; }
  .ih-card--ancha .ih-texto { flex: 1; }
  .ih-card--ancha .ih-bajada { margin-bottom: 0; }
  .ih-card--ancha .ih-lado { display: flex; flex-direction: column; align-items: flex-end; gap: .7rem; flex-shrink: 0; }

  .ih-proceso { margin-top: 3.2rem; }
  .ih-proceso h2 { font-family: var(--font-display); font-size: 1.5rem; color: var(--blue-dark); text-align: center; margin-bottom: 1.4rem; }
  .ih-pasos { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; list-style: none; counter-reset: paso; }
  .ih-pasos li { background: #fff; border-radius: 14px; padding: 1.2rem 1.3rem; box-shadow: var(--shadow-sm); counter-increment: paso; }
  .ih-pasos li::before { content: counter(paso); font-family: var(--font-display); font-weight: 900; font-size: 1.4rem; color: var(--red); display: block; }
  .ih-pasos b { display: block; color: var(--blue-dark); margin: .1rem 0 .2rem; }
  .ih-pasos span { font-size: .87rem; color: #5b6270; }

  .ih-pie { margin-top: 2.4rem; text-align: center; font-size: .92rem; color: #5b6270; }
  .ih-pie a { color: var(--blue-mid); font-weight: 700; }

  @media (max-width: 900px) {
    .ih-grid { grid-template-columns: 1fr; }
    .ih-pasos { grid-template-columns: 1fr; }
  }
  @media (max-width: 640px) {
    .ih-wrap { padding: 2.2rem 1rem 3.5rem; }
    .ih-card--ancha .ih-cuerpo { flex-direction: column; align-items: stretch; padding: 1.5rem; }
    .ih-card--ancha .ih-top { justify-content: space-between; }
    .ih-card--ancha .ih-lado { align-items: stretch; }
  }
CSS;

require __DIR__ . '/partials/header.php';

/** Una tarjeta de formulario. */
function ih_tarjeta(string $clave, array $f, array $est, bool $ancha = false): void {
    $abierta = $est['abierto'];
    $flecha  = '<svg viewBox="0 0 24 24" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>';
    $estado  = match ($est['motivo']) {
        'abierto'      => '<span class="ih-estado abierta">Inscripción abierta</span>',
        'proximamente' => '<span class="ih-estado proxima">Próximamente</span>',
        default        => '<span class="ih-estado cerrada">Cerrada</span>',
    };
    $boton   = '<span class="ih-btn">' . ($abierta ? 'Completar formulario' : 'Ver formulario') . $flecha . '</span>';
    $periodo = $est['periodo'] === '' ? '' :
        '<p class="ih-periodo"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>'
        . e($est['periodo']) . '</p>';
    ?>
    <a href="<?= e($f['archivo']) ?>" class="ih-card<?= $ancha ? ' ih-card--ancha' : '' ?><?= $abierta ? '' : ' is-cerrada' ?>"
       style="--acc:<?= e($f['accent']) ?>">
      <div class="ih-cuerpo">
        <?php if ($ancha): ?>
          <div class="ih-top"><span class="ih-icono" aria-hidden="true"><?= $f['icono'] ?></span></div>
          <div class="ih-texto">
            <h2><?= $f['titulo'] ?></h2>
            <p class="ih-bajada"><?= e($f['bajada_larga'] ?? $f['bajada']) ?></p>
            <?= $periodo ?>
          </div>
          <div class="ih-lado"><?= $estado . $boton ?></div>
        <?php else: ?>
          <div class="ih-top">
            <span class="ih-icono" aria-hidden="true"><?= $f['icono'] ?></span>
            <?= $estado ?>
          </div>
          <h2><?= $f['titulo'] ?></h2>
          <p class="ih-bajada"><?= e($f['bajada']) ?></p>
          <?= $periodo . $boton ?>
        <?php endif; ?>
      </div>
    </a>
    <?php
}
?>

<?php page_hero(
  ['Inicio' => './', 'Inscripciones' => null],
  'Admisiones',
  'Inscripciones <em>en línea</em>',
  'Elegí el nivel al que querés inscribir y completá la preinscripción. Si vas a inscribir a más de un hijo/a, usá el formulario de hermanos. Más abajo están la inscripción a mesas de examen y la solicitud de beca.'
); ?>

<section class="ih-wrap">
  <div class="ih-grid">
    <?php foreach ($por_nivel as $clave => $f) ih_tarjeta($clave, $f, $estado_de($clave)); ?>
  </div>

  <div class="ih-otros">
    <h2 class="ih-otros-titulo">Otros trámites en línea</h2>
    <p class="ih-otros-bajada">Hermanos, mesas de examen y solicitud de beca: se completan y se envían por acá, igual que las inscripciones.</p>
    <?php foreach ($otros as $clave => $f) ih_tarjeta($clave, $f, $estado_de($clave), true); ?>
  </div>

  <div class="ih-proceso">
    <h2>¿Cómo sigue?</h2>
    <ol class="ih-pasos">
      <li><b>Completá el formulario</b><span>Datos del alumno/a y del responsable.</span></li>
      <li><b>Presentá la documentación</b><span>DNI, partida de nacimiento y libreta sanitaria.</span></li>
      <li><b>Entrevista</b><span>La Secretaría te contacta para coordinar una entrevista con la familia.</span></li>
    </ol>
  </div>

  <p class="ih-pie">
    ¿Dudas sobre vacantes o requisitos? Escribinos a
    <a href="mailto:<?= cfg_e('email_admisiones') ?>"><?= cfg_e('email_admisiones') ?></a><?php if (cfg('horario_admision')): ?>
    · Atención de admisiones: <?= cfg_e('horario_admision') ?><?php endif; ?>.
  </p>
</section>

<?php require __DIR__ . '/partials/footer.php';
