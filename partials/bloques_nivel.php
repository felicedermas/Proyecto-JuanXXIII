<?php
// ============================================================
//  Bloques por nivel: próximas fechas de la Agenda y últimas
//  Novedades, filtradas por etiqueta.
//
//  Los usan las páginas de Secundaria Orientada y Técnica para
//  mostrar lo que se carga desde el panel (Agenda / Novedades)
//  en lugar de fechas y avisos escritos a mano.
//
//  Uso:
//    require_once __DIR__ . '/partials/bloques_nivel.php';
//    nb_agenda(['Técnica', 'Secundario'], 'Técnica');
//    nb_novedades(['Técnica', 'Secundario'], 'Técnica');
//  (el segundo parámetro es la etiqueta del enlace "Ver todo")
// ============================================================

require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/inscripciones.php';

/** Próximos eventos de la agenda (y períodos de inscripción) para esas etiquetas. */
function nb_eventos(array $etiquetas, int $limite = 8): ?array {
    $pdo = db_opcional();
    if ($pdo === null) return null;
    try {
        $ph = implode(',', array_fill(0, count($etiquetas), '?'));
        $st = $pdo->prepare(
            "SELECT titulo, descripcion, etiqueta, tipo, fecha_evento, hora_inicio, hora_fin, lugar, enlace
             FROM agenda
             WHERE etiqueta IN ($ph) AND fecha_evento >= CURDATE()
             ORDER BY fecha_evento ASC, hora_inicio ASC
             LIMIT " . (int) $limite
        );
        $st->execute($etiquetas);
        $eventos = $st->fetchAll();
    } catch (Throwable $ex) {
        return null;
    }
    $hoy = insc_hoy();
    foreach (insc_eventos_agenda($pdo) as $ev) {
        if ($ev['fecha_evento'] >= $hoy && in_array($ev['etiqueta'], $etiquetas, true)) $eventos[] = $ev;
    }
    usort($eventos, fn($a, $b) => [$a['fecha_evento'], (string) $a['hora_inicio']] <=> [$b['fecha_evento'], (string) $b['hora_inicio']]);
    return array_slice($eventos, 0, $limite);
}

/** Últimas novedades para esas etiquetas, con su primera imagen. */
function nb_lista_novedades(array $etiquetas, int $limite = 4): ?array {
    $pdo = db_opcional();
    if ($pdo === null) return null;
    try {
        $ph = implode(',', array_fill(0, count($etiquetas), '?'));
        $st = $pdo->prepare(
            "SELECT id_novedad, titulo, descripcion, etiqueta, created_at
             FROM novedades WHERE etiqueta IN ($ph)
             ORDER BY created_at DESC LIMIT " . (int) $limite
        );
        $st->execute($etiquetas);
        $novs = $st->fetchAll();
        if ($novs) {
            $ids = array_column($novs, 'id_novedad');
            $ph2 = implode(',', array_fill(0, count($ids), '?'));
            $im = $pdo->prepare("SELECT id_novedad, url_imagen FROM imagenes_novedades WHERE id_novedad IN ($ph2) ORDER BY orden ASC");
            $im->execute($ids);
            $primera = [];
            foreach ($im->fetchAll() as $f) $primera[$f['id_novedad']] ??= $f['url_imagen'];
            foreach ($novs as &$n) $n['imagen'] = $primera[$n['id_novedad']] ?? null;
            unset($n);
        }
        return $novs;
    } catch (Throwable $ex) {
        return null;
    }
}

function nb_estilos(): void {
    static $listo = false;
    if ($listo) return;
    $listo = true; ?>
<style>
  .nb-lista { display: grid; gap: .8rem; }
  .nb-ev { display: grid; grid-template-columns: 64px 1fr; gap: 1rem; align-items: start;
           background: #fff; border: 1px solid rgba(29,53,87,.1); border-radius: 12px; padding: .9rem 1rem; color: #1D3557; }
  .nb-fecha { text-align: center; border-radius: 10px; background: var(--nb-acento, #1D3557); color: #fff; padding: .45rem .2rem; line-height: 1.1; }
  .nb-fecha strong { display: block; font-size: 1.35rem; }
  .nb-fecha span { font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; }
  .nb-ev h4 { margin: 0 0 .2rem; font-size: 1rem; color: #1D3557; }
  .nb-ev p { margin: 0; font-size: .88rem; color: #4a5568; line-height: 1.5; }
  .nb-meta { display: flex; flex-wrap: wrap; gap: .35rem .8rem; margin-top: .35rem; font-size: .78rem; color: #5d6878; }
  .nb-meta b { color: var(--nb-acento, #1D3557); }
  .nb-vacio { background: #fff; border: 1px dashed rgba(29,53,87,.25); border-radius: 12px; padding: 1.6rem 1.2rem; text-align: center; color: #4a5568; }
  .nb-vacio h4 { margin: 0 0 .3rem; color: #1D3557; }
  .nb-mas { margin-top: 1.1rem; text-align: center; }
  .nb-mas a { display: inline-block; font-weight: 800; color: #fff; background: var(--nb-acento, #1D3557); padding: .65rem 1.4rem; border-radius: 999px; text-decoration: none; }
  .nb-novs { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 1rem; }
  .nb-nov { display: flex; flex-direction: column; background: #fff; border-radius: 12px; overflow: hidden;
            border: 1px solid rgba(29,53,87,.1); color: #1D3557; text-decoration: none; transition: transform .2s; }
  .nb-nov:hover { transform: translateY(-3px); }
  .nb-nov-img { aspect-ratio: 16/10; background: #e8edf3; display: flex; align-items: center; justify-content: center; font-size: 2rem; }
  .nb-nov-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .nb-nov-body { padding: .9rem 1rem 1.1rem; display: flex; flex-direction: column; gap: .3rem; }
  .nb-nov-body h4 { margin: 0; font-size: .98rem; }
  .nb-nov-body p { margin: 0; font-size: .85rem; color: #4a5568; line-height: 1.5;
                   display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
  .nb-nov-body small { color: #5d6878; font-size: .76rem; }
</style>
<?php }

/** Lista de próximas fechas, o un aviso si no hay ninguna. */
function nb_agenda(array $etiquetas, string $etiqueta_link): void {
    $eventos = nb_eventos($etiquetas);
    $meses = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    echo '<div data-ed-no>';   // sale del panel: no se edita desde la página
    nb_estilos();
    if (!$eventos) { ?>
      <div class="nb-vacio">
        <h4>No hay fechas próximas cargadas</h4>
        <p>Cuando se publiquen actos, reuniones, mesas o inscripciones, van a aparecer acá.</p>
      </div>
    <?php } else { ?>
      <div class="nb-lista">
      <?php foreach ($eventos as $ev):
        [, $m, $d] = explode('-', $ev['fecha_evento']);
        $hora = $ev['hora_inicio'] ? substr($ev['hora_inicio'], 0, 5) . ($ev['hora_fin'] ? ' a ' . substr($ev['hora_fin'], 0, 5) : '') . ' h' : '';
      ?>
        <div class="nb-ev">
          <div class="nb-fecha"><strong><?= (int) $d ?></strong><span><?= $meses[(int) $m] ?></span></div>
          <div>
            <h4><?= e($ev['titulo']) ?></h4>
            <?php if (!empty($ev['descripcion'])): ?><p><?= e($ev['descripcion']) ?></p><?php endif; ?>
            <div class="nb-meta">
              <b><?= e($ev['tipo']) ?></b>
              <?php if ($hora): ?><span><?= e($hora) ?></span><?php endif; ?>
              <?php if (!empty($ev['lugar'])): ?><span><?= e($ev['lugar']) ?></span><?php endif; ?>
              <?php if (!empty($ev['url'])): ?><a href="<?= e($ev['url']) ?>">Ir al formulario</a><?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      </div>
    <?php } ?>
      <p class="nb-mas"><a href="agenda?etiqueta=<?= urlencode($etiqueta_link) ?>">Ver la agenda completa</a></p>
    <?php
    echo '</div>';
}

/** Últimas novedades, o un aviso si todavía no hay. */
function nb_novedades(array $etiquetas, string $etiqueta_link): void {
    $novs = nb_lista_novedades($etiquetas);
    echo '<div data-ed-no>';
    nb_estilos();
    if (!$novs) { ?>
      <div class="nb-vacio">
        <h4>Todavía no hay novedades publicadas</h4>
        <p>Las publicaciones que se carguen desde el panel van a aparecer acá.</p>
      </div>
    <?php } else { ?>
      <div class="nb-novs">
      <?php foreach ($novs as $n): ?>
        <a class="nb-nov" href="novedad?id=<?= (int) $n['id_novedad'] ?>">
          <div class="nb-nov-img"><?php if ($n['imagen']): ?><img src="<?= e($n['imagen']) ?>" alt="" loading="lazy"/><?php else: ?>📰<?php endif; ?></div>
          <div class="nb-nov-body">
            <small><?= e(date('d/m/Y', strtotime($n['created_at']))) ?> · <?= e($n['etiqueta']) ?></small>
            <h4><?= e($n['titulo']) ?></h4>
            <p><?= e($n['descripcion']) ?></p>
          </div>
        </a>
      <?php endforeach; ?>
      </div>
    <?php } ?>
      <p class="nb-mas"><a href="novedades?etiqueta=<?= urlencode($etiqueta_link) ?>">Ver todas las novedades</a></p>
    <?php
    echo '</div>';
}
