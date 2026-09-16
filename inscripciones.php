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
$page_desc       = 'Preinscripción en línea al Colegio Parroquial Juan XXIII: Jardín de Infantes, Nivel Primario, Nivel Secundario (Orientada y Técnica) y hermanos.';
$nav_active      = 'inscripciones';
$nav_active_link = 'inscripciones.php';

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
$abierto = fn(string $clave): bool => (int) ($estados[$clave]['habilitado'] ?? 0) === 1;

$formularios = insc_formularios();
$por_nivel   = array_filter($formularios, fn($f) => $f['nivel'] !== null);
$hermanos    = $formularios['hermanos'];

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

  /* Hermanos: tarjeta horizontal a lo ancho */
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
function ih_tarjeta(string $clave, array $f, bool $abierta, bool $ancha = false): void {
    $flecha = '<svg viewBox="0 0 24 24" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>';
    $estado = '<span class="ih-estado ' . ($abierta ? 'abierta">Inscripción abierta' : 'cerrada">Cerrada') . '</span>';
    $boton  = '<span class="ih-btn">' . ($abierta ? 'Completar formulario' : 'Ver formulario') . $flecha . '</span>';
    ?>
    <a href="<?= e($f['archivo']) ?>" class="ih-card<?= $ancha ? ' ih-card--ancha' : '' ?><?= $abierta ? '' : ' is-cerrada' ?>"
       style="--acc:<?= e($f['accent']) ?>">
      <div class="ih-cuerpo">
        <?php if ($ancha): ?>
          <div class="ih-top"><span class="ih-icono" aria-hidden="true"><?= $f['icono'] ?></span></div>
          <div class="ih-texto">
            <h2><?= $f['titulo'] ?></h2>
            <p class="ih-bajada"><?= e($f['bajada']) ?>: completás una sola vez los datos de la familia y cargás a cada hijo/a en su nivel.</p>
          </div>
          <div class="ih-lado"><?= $estado . $boton ?></div>
        <?php else: ?>
          <div class="ih-top">
            <span class="ih-icono" aria-hidden="true"><?= $f['icono'] ?></span>
            <?= $estado ?>
          </div>
          <h2><?= $f['titulo'] ?></h2>
          <p class="ih-bajada"><?= e($f['bajada']) ?></p>
          <?= $boton ?>
        <?php endif; ?>
      </div>
    </a>
    <?php
}
?>

<?php page_hero(
  ['Inicio' => 'index.php', 'Inscripciones' => null],
  'Admisiones',
  'Inscripciones <em>en línea</em>',
  'Elegí el nivel al que querés inscribir y completá la preinscripción. Si vas a inscribir a más de un hijo/a, usá el formulario de hermanos.'
); ?>

<section class="ih-wrap">
  <div class="ih-grid">
    <?php foreach ($por_nivel as $clave => $f) ih_tarjeta($clave, $f, $abierto($clave)); ?>
  </div>

  <?php ih_tarjeta('hermanos', $hermanos, $abierto('hermanos'), true); ?>

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
