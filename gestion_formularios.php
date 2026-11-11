<?php
// ============================================================
//  gestion_formularios.php
//  Panel de Control — Editar un formulario de inscripción
//
//  Lo que se puede cambiar de cada formulario:
//    - los textos (bajada, aviso, pasos, declaración, botón,
//      mensaje de confirmación) y el título de cada sección;
//    - de cada campo: etiqueta, ayuda, texto de ejemplo, si es
//      obligatorio y si se muestra; y las opciones de las listas;
//    - campos nuevos al final de cualquier sección.
//
//  No se puede ocultar ni volver opcional lo que el sistema necesita
//  para clasificar y contactar (insc_campo_fijo()).
//
//  Se guarda solo lo que difiere del código, en
//  inscripcion_formularios.config (ver partials/inscripciones.php).
//  Habilitar el formulario y ponerle fechas se hace en
//  gestion_inscripciones.php, con este mismo permiso.
//
//  Acceso: permiso "Edición de formularios" (o administradores).
// ============================================================
require_once __DIR__ . '/panel_config.php';
require_once __DIR__ . '/partials/inscripciones.php';
exigir_permiso('edita_formularios', 'la edición de formularios');

$pdo = db();
if (!insc_asegurar_tablas($pdo)) {
    die('<p style="font-family:sans-serif;padding:2rem;color:#c1121f;">No se pudieron crear las tablas de inscripciones. Ejecutá <b>migracion_inscripciones.sql</b> en phpMyAdmin.</p>');
}

$base  = insc_formularios_base();
$clave = is_string($_GET['f'] ?? null) && isset($base[$_GET['f']]) ? $_GET['f'] : array_key_first($base);
$b     = $base[$clave];
$cfg   = insc_configs()[$clave] ?? [];
$yo    = (int) usuario_actual()['id_usuario'];

/** Texto de un formulario tal como está en el código (o el que usa la página por defecto). */
function texto_base(array $b, string $k): string {
    return match ($k) {
        'aviso'       => insc_aviso_a_texto($b['aviso'] ?? ''),
        'declaracion' => INSC_DECLARACION,
        'boton'       => INSC_BOTON,
        'ok_titulo'   => $b['ok_titulo'] ?? '¡Recibimos la preinscripción!',
        'ok_texto'    => $b['ok_texto'] ?? 'Registramos los datos del alumno/a.',
        default       => (string) ($b[$k] ?? ''),
    };
}

/** Una línea por opción → lista limpia, sin repetidos. */
function opciones_de_texto(string $texto): array {
    $ops = [];
    foreach (preg_split('~\R~u', $texto) as $l) {
        $l = mb_substr(trim(preg_replace('~\s+~u', ' ', $l)), 0, 80);
        if ($l !== '' && !in_array($l, $ops, true)) $ops[] = $l;
    }
    return array_slice($ops, 0, 40);
}

// ============================================================
//  GUARDAR
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $accion = (string) ($_POST['accion'] ?? '');
    $linea  = fn($v, int $max): string => mb_substr(trim(preg_replace('~\s+~u', ' ', is_string($v) ? $v : '')), 0, $max);
    $avisos = [];

    if ($accion === 'restaurar') {
        $pdo->prepare('UPDATE inscripcion_formularios SET config = NULL, id_usuario = ? WHERE clave = ?')->execute([$yo, $clave]);
        flash('ok', 'Formulario «' . $b['nombre'] . '»: volvió a su versión original.');
        header('Location: gestion_formularios?f=' . rawurlencode($clave));
        exit;
    }

    if ($accion === 'guardar') {
        $nuevo = ['sig' => max(1, (int) ($cfg['sig'] ?? 1))];

        // ── Textos ──
        $p_textos = (array) ($_POST['textos'] ?? []);
        foreach (array_keys(INSC_TEXTOS_EDITABLES) as $k) {
            if (!array_key_exists($k, $p_textos)) continue;
            if ($k === 'aviso') {
                $v = mb_substr(trim(str_replace("\r\n", "\n", (string) $p_textos[$k])), 0, 900);
                if ($v !== trim(texto_base($b, $k))) $nuevo['textos'][$k] = insc_texto_a_aviso($v);
                continue;
            }
            $v = $linea($p_textos[$k], $k === 'declaracion' || $k === 'bajada_larga' ? 400 : 200);
            if ($v === texto_base($b, $k)) continue;
            // Vacío solo vale donde la página tiene qué mostrar en su lugar
            if ($v === '' && $k !== 'bajada_larga') continue;
            $nuevo['textos'][$k] = $v;
        }

        // ── Pasos ──
        $pasos = [];
        foreach ((array) ($_POST['pasos'] ?? []) as $p) {
            $t = $linea($p['t'] ?? '', 60);
            if ($t !== '') $pasos[] = [$t, $linea($p['d'] ?? '', 140)];
        }
        $pasos = array_slice($pasos, 0, 5);
        if ($pasos !== array_map('array_values', $b['pasos'])) $nuevo['pasos'] = $pasos;

        // ── Secciones y campos ──
        $p_sec    = (array) ($_POST['sec'] ?? []);
        $p_campos = (array) ($_POST['campo'] ?? []);
        foreach ($b['secciones'] as $i => $sec) {
            $t = $linea($p_sec[$i]['titulo'] ?? '', 80);
            if ($t !== '' && $t !== $sec['titulo']) $nuevo['secciones'][$i] = ['titulo' => $t];
            if (!empty($sec['hijos'])) continue;

            foreach ($sec['campos'] as $c) {
                $p = (array) ($p_campos[$c['n']] ?? []);
                if (!$p) continue;
                $fijo = insc_campo_fijo($c);
                $o = [];

                $l = $linea($p['l'] ?? '', 120);
                if ($l !== '' && $l !== $c['l']) $o['l'] = $l;

                $hint = $linea($p['hint'] ?? '', 200);
                if ($hint !== (string) ($c['hint'] ?? '')) $o['hint'] = $hint;

                if (array_key_exists('ph', $p)) {
                    $ph = $linea($p['ph'], 120);
                    if ($ph !== (string) ($c['ph'] ?? '')) $o['ph'] = $ph;
                }
                if (!$fijo) {
                    $r = !empty($p['r']);
                    if ($r !== !empty($c['r'])) $o['r'] = $r ? 1 : 0;
                    if (empty($p['visible'])) $o['oculto'] = 1;
                }
                if (insc_opciones_editables($c) && array_key_exists('o', $p)) {
                    $ops = opciones_de_texto((string) $p['o']);
                    if (count($ops) < 2) {
                        $avisos[] = 'La lista «' . $c['l'] . '» necesita al menos dos opciones: quedó como estaba.';
                        if (isset($cfg['campos'][$c['n']]['o'])) $o['o'] = $cfg['campos'][$c['n']]['o'];
                    } elseif ($ops !== array_values($c['o'])) {
                        $o['o'] = $ops;
                    }
                }
                if ($o) $nuevo['campos'][$c['n']] = $o;
            }
        }

        // ── Campos agregados: los que ya estaban ──
        $p_extra = (array) ($_POST['extra'] ?? []);
        $armar_extra = function (array $p, int $id, int $sec, ?array $previo) use ($linea, &$avisos): ?array {
            $l = $linea($p['l'] ?? '', 120);
            if ($l === '') return null;
            $t = isset(INSC_TIPOS_EXTRA[$p['t'] ?? '']) ? $p['t'] : 'text';
            $x = ['id' => $id, 'sec' => $sec, 'l' => $l, 't' => $t, 'r' => !empty($p['r']) ? 1 : 0,
                  'ph' => $linea($p['ph'] ?? '', 120), 'hint' => $linea($p['hint'] ?? '', 200)];
            if ($t === 'select') {
                $x['o'] = opciones_de_texto((string) ($p['o'] ?? ''));
                if (count($x['o']) < 2) {
                    if ($previo && ($previo['t'] ?? '') === 'select') {
                        $x['o'] = $previo['o'];
                        $avisos[] = 'La lista «' . $l . '» necesita al menos dos opciones: quedó como estaba.';
                    } else {
                        $avisos[] = 'El campo «' . $l . '» es una lista y necesita al menos dos opciones (una por línea): no se agregó.';
                        return null;
                    }
                }
            }
            return $x;
        };
        foreach ((array) ($cfg['extra'] ?? []) as $x) {
            $id = (int) $x['id'];
            $p  = (array) ($p_extra[$id] ?? []);
            if (!$p) { $nuevo['extra'][] = $x; continue; }           // no vino en el formulario: se conserva
            if (!empty($p['borrar'])) continue;
            $armado = $armar_extra($p, $id, (int) $x['sec'], $x);
            $nuevo['extra'][] = $armado ?? $x;
        }
        // ── Campos agregados: nuevos (uno por sección) ──
        foreach ((array) ($_POST['nuevo'] ?? []) as $i => $p) {
            $i = (int) $i;
            if (!isset($b['secciones'][$i]) || !empty($b['secciones'][$i]['hijos'])) continue;
            if (count($nuevo['extra'] ?? []) >= INSC_MAX_EXTRAS) { $avisos[] = 'Se llegó al máximo de campos agregados.'; break; }
            $armado = $armar_extra((array) $p, $nuevo['sig'], $i, null);
            if ($armado) { $nuevo['extra'][] = $armado; $nuevo['sig']++; }
        }

        $hay_cambios = count($nuevo) > 1;
        $json = ($hay_cambios || $nuevo['sig'] > 1) ? json_encode($nuevo, JSON_UNESCAPED_UNICODE) : null;
        $pdo->prepare('UPDATE inscripcion_formularios SET config = ?, id_usuario = ? WHERE clave = ?')->execute([$json, $yo, $clave]);

        if ($avisos) flash('info', 'Guardado, con observaciones: ' . implode(' ', $avisos));
        else         flash('ok', 'Formulario «' . $b['nombre'] . '» guardado. Ya se ve así en el sitio.');
        header('Location: gestion_formularios?f=' . rawurlencode($clave));
        exit;
    }
}

// ============================================================
//  DATOS PARA LA VISTA
// ============================================================
$fila   = insc_fila_formulario($pdo, $clave);
$estado = insc_estado($fila);
$editado = (bool) array_diff(array_keys($cfg), ['sig']);

$val_texto = function (string $k) use ($b, $cfg): string {
    if (isset($cfg['textos'][$k])) {
        return $k === 'aviso' ? insc_aviso_a_texto($cfg['textos'][$k]) : (string) $cfg['textos'][$k];
    }
    return texto_base($b, $k);
};
$pasos = $cfg['pasos'] ?? $b['pasos'];
$extras_de = function (int $i) use ($cfg): array {
    return array_values(array_filter((array) ($cfg['extra'] ?? []), fn($x) => (int) ($x['sec'] ?? -1) === $i));
};
const TIPO_LEGIBLE = ['text' => 'Texto', 'textarea' => 'Texto largo', 'select' => 'Lista', 'email' => 'Correo',
                      'tel' => 'Teléfono', 'dni' => 'DNI', 'date' => 'Fecha'];

$panel_page_title = 'Editar formulario';
$panel_heading    = 'Editar <span>formulario</span>';
$panel_sub        = 'Cambiá los textos y los campos de cada formulario de inscripción. Lo que guardás se ve en el sitio al instante.';
require __DIR__ . '/panel_header.php';
?>

<div class="panel-toolbar">
  <a href="gestion_inscripciones" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Volver a inscripciones
  </a>
</div>

<nav class="fe-tabs" aria-label="Formularios">
  <?php foreach ($base as $k => $fd): ?>
    <a href="gestion_formularios?f=<?= e($k) ?>" class="fe-tab<?= $k === $clave ? ' act' : '' ?>"<?= $k === $clave ? ' aria-current="page"' : '' ?>>
      <span aria-hidden="true"><?= $fd['icono'] ?></span> <?= e($fd['nombre']) ?>
    </a>
  <?php endforeach; ?>
</nav>

<div class="fe-estado">
  <div>
    <b><?= e($b['nombre']) ?></b>
    <span class="fe-pill fe-pill--<?= $estado['abierto'] ? 'on' : 'off' ?>">
      <?= $estado['abierto'] ? 'Abierto en el sitio' : 'Cerrado en el sitio' ?>
    </span>
    <?php if ($editado): ?><span class="fe-pill fe-pill--mod">Con cambios respecto del original</span><?php endif; ?>
    <small>Para habilitarlo o ponerle fechas, andá a <a href="gestion_inscripciones">Inscripciones</a>.</small>
  </div>
  <a href="<?= e($b['archivo']) ?>?vista=1" target="_blank" rel="noopener" class="btn btn-secundario">Ver cómo queda ↗</a>
</div>

<form method="post" action="gestion_formularios?f=<?= e($clave) ?>" class="fe-form" autocomplete="off">
  <?= csrf_input() ?>
  <input type="hidden" name="accion" value="guardar"/>

  <!-- ── Textos ── -->
  <section class="fe-card">
    <h2>Textos del formulario</h2>
    <div class="fe-grid2">
      <?php foreach (INSC_TEXTOS_EDITABLES as $k => $para):
        $largo = in_array($k, ['aviso', 'declaracion', 'bajada_larga'], true); ?>
        <div class="field<?= $largo ? ' fe-ancho' : '' ?>">
          <label for="t-<?= e($k) ?>"><?= e($para) ?></label>
          <?php if ($largo): ?>
            <textarea id="t-<?= e($k) ?>" name="textos[<?= e($k) ?>]" rows="<?= $k === 'aviso' ? 4 : 2 ?>"><?= e($val_texto($k)) ?></textarea>
          <?php else: ?>
            <input type="text" id="t-<?= e($k) ?>" name="textos[<?= e($k) ?>]" value="<?= e($val_texto($k)) ?>" maxlength="200"/>
          <?php endif; ?>
          <?php if ($k === 'aviso'): ?>
            <span class="hint">Para resaltar una parte en negrita, encerrala entre asteriscos: *así*. Si lo dejás vacío, no se muestra.</span>
          <?php elseif ($k === 'bajada_larga'): ?>
            <span class="hint">Es el texto de la tarjeta en la página Inscripciones. Vacío = se usa el texto bajo el título.</span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <h3>Pasos que se muestran arriba del formulario</h3>
    <p class="fe-nota">Hasta 5. Para sacar un paso, borrale el título.</p>
    <?php for ($n = 0; $n < 5; $n++): ?>
      <div class="fe-paso">
        <span class="fe-paso-n"><?= $n + 1 ?></span>
        <input type="text" name="pasos[<?= $n ?>][t]" value="<?= e($pasos[$n][0] ?? '') ?>" maxlength="60" placeholder="Título del paso" aria-label="Título del paso <?= $n + 1 ?>"/>
        <input type="text" name="pasos[<?= $n ?>][d]" value="<?= e($pasos[$n][1] ?? '') ?>" maxlength="140" placeholder="Detalle" aria-label="Detalle del paso <?= $n + 1 ?>"/>
      </div>
    <?php endfor; ?>
  </section>

  <!-- ── Secciones y campos ── -->
  <?php foreach ($b['secciones'] as $i => $sec):
    $titulo_sec = $cfg['secciones'][$i]['titulo'] ?? $sec['titulo']; ?>
    <section class="fe-card">
      <div class="field fe-sec-titulo">
        <label for="sec-<?= $i ?>">Sección <?= $i + 1 ?></label>
        <input type="text" id="sec-<?= $i ?>" name="sec[<?= $i ?>][titulo]" value="<?= e($titulo_sec) ?>" maxlength="80"/>
      </div>

      <?php if (!empty($sec['hijos'])): ?>
        <p class="fe-nota">En esta sección la familia carga a cada hijo/a (nombre, DNI, fecha de nacimiento, nivel y modalidad). Esos datos son los que clasifican cada inscripción y no se modifican desde acá.</p>
      <?php else: ?>

        <?php foreach ($sec['campos'] as $c):
          $o      = (array) ($cfg['campos'][$c['n']] ?? []);
          $fijo   = insc_campo_fijo($c);
          $oculto = !$fijo && !empty($o['oculto']);
          $req    = $fijo ? !empty($c['r']) : (array_key_exists('r', $o) ? (bool) $o['r'] : !empty($c['r']));
          $n      = 'campo[' . $c['n'] . ']';
          $id     = 'c-' . $c['n'];
          $con_ph = !in_array($c['t'], ['select', 'date'], true); ?>
          <div class="fe-campo<?= $oculto ? ' is-oculto' : '' ?>">
            <div class="fe-campo-top">
              <div class="field fe-campo-l">
                <label for="<?= e($id) ?>">Etiqueta <span class="fe-tipo"><?= e(TIPO_LEGIBLE[$c['t']] ?? $c['t']) ?></span></label>
                <input type="text" id="<?= e($id) ?>" name="<?= e($n) ?>[l]" value="<?= e($o['l'] ?? $c['l']) ?>" maxlength="120" required/>
              </div>
              <?php if ($fijo): ?>
                <p class="fe-fijo" title="El sistema lo usa para clasificar la inscripción o para contactar a la familia">
                  🔒 Dato del sistema<?= !empty($c['r']) ? ' · obligatorio' : '' ?>
                </p>
              <?php else: ?>
                <label class="fe-check"><input type="checkbox" name="<?= e($n) ?>[r]" value="1"<?= $req ? ' checked' : '' ?>/> Obligatorio</label>
                <label class="fe-check"><input type="checkbox" name="<?= e($n) ?>[visible]" value="1"<?= $oculto ? '' : ' checked' ?>/> Se muestra</label>
              <?php endif; ?>
            </div>
            <div class="fe-grid2">
              <div class="field">
                <label for="<?= e($id) ?>-h">Ayuda bajo el campo <span class="hint">(opcional)</span></label>
                <input type="text" id="<?= e($id) ?>-h" name="<?= e($n) ?>[hint]" value="<?= e($o['hint'] ?? ($c['hint'] ?? '')) ?>" maxlength="200"/>
              </div>
              <?php if ($con_ph): ?>
              <div class="field">
                <label for="<?= e($id) ?>-p">Texto de ejemplo dentro del campo <span class="hint">(opcional)</span></label>
                <input type="text" id="<?= e($id) ?>-p" name="<?= e($n) ?>[ph]" value="<?= e($o['ph'] ?? ($c['ph'] ?? '')) ?>" maxlength="120"/>
              </div>
              <?php endif; ?>
            </div>
            <?php if (insc_opciones_editables($c)): ?>
              <div class="field">
                <label for="<?= e($id) ?>-o">Opciones de la lista <span class="hint">(una por línea)</span></label>
                <textarea id="<?= e($id) ?>-o" name="<?= e($n) ?>[o]" rows="<?= min(8, max(3, count($o['o'] ?? $c['o']))) ?>"><?= e(implode("\n", $o['o'] ?? $c['o'])) ?></textarea>
              </div>
            <?php elseif ($c['t'] === 'select'): ?>
              <p class="fe-nota">Las opciones de esta lista son las que ordenan las inscripciones por nivel, modalidad y año: no se cambian desde acá.</p>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>

        <?php foreach ($extras_de($i) as $x):
          $n  = 'extra[' . (int) $x['id'] . ']';
          $id = 'x-' . (int) $x['id']; ?>
          <div class="fe-campo fe-campo--extra">
            <div class="fe-campo-top">
              <div class="field fe-campo-l">
                <label for="<?= e($id) ?>">Etiqueta <span class="fe-tipo fe-tipo--extra">Agregado</span></label>
                <input type="text" id="<?= e($id) ?>" name="<?= e($n) ?>[l]" value="<?= e($x['l']) ?>" maxlength="120" required/>
              </div>
              <div class="field fe-campo-t">
                <label for="<?= e($id) ?>-t">Tipo</label>
                <select id="<?= e($id) ?>-t" name="<?= e($n) ?>[t]" data-tipo>
                  <?php foreach (INSC_TIPOS_EXTRA as $tk => $tl): ?><option value="<?= e($tk) ?>"<?= $tk === $x['t'] ? ' selected' : '' ?>><?= e($tl) ?></option><?php endforeach; ?>
                </select>
              </div>
              <label class="fe-check"><input type="checkbox" name="<?= e($n) ?>[r]" value="1"<?= !empty($x['r']) ? ' checked' : '' ?>/> Obligatorio</label>
              <label class="fe-check fe-check--borrar"><input type="checkbox" name="<?= e($n) ?>[borrar]" value="1"/> Quitar</label>
            </div>
            <div class="fe-grid2">
              <div class="field">
                <label for="<?= e($id) ?>-h">Ayuda bajo el campo <span class="hint">(opcional)</span></label>
                <input type="text" id="<?= e($id) ?>-h" name="<?= e($n) ?>[hint]" value="<?= e($x['hint'] ?? '') ?>" maxlength="200"/>
              </div>
              <div class="field">
                <label for="<?= e($id) ?>-p">Texto de ejemplo dentro del campo <span class="hint">(opcional)</span></label>
                <input type="text" id="<?= e($id) ?>-p" name="<?= e($n) ?>[ph]" value="<?= e($x['ph'] ?? '') ?>" maxlength="120"/>
              </div>
            </div>
            <div class="field" data-opciones<?= $x['t'] === 'select' ? '' : ' hidden' ?>>
              <label for="<?= e($id) ?>-o">Opciones de la lista <span class="hint">(una por línea)</span></label>
              <textarea id="<?= e($id) ?>-o" name="<?= e($n) ?>[o]" rows="4"><?= e(implode("\n", (array) ($x['o'] ?? []))) ?></textarea>
            </div>
          </div>
        <?php endforeach; ?>

        <details class="fe-nuevo">
          <summary>＋ Agregar un campo a esta sección</summary>
          <div class="fe-campo fe-campo--extra">
            <div class="fe-campo-top">
              <div class="field fe-campo-l">
                <label for="n-<?= $i ?>">Etiqueta <span class="hint">(lo que se le pregunta a la familia)</span></label>
                <input type="text" id="n-<?= $i ?>" name="nuevo[<?= $i ?>][l]" maxlength="120" placeholder="Ej.: ¿Tiene obra social?"/>
              </div>
              <div class="field fe-campo-t">
                <label for="n-<?= $i ?>-t">Tipo</label>
                <select id="n-<?= $i ?>-t" name="nuevo[<?= $i ?>][t]" data-tipo>
                  <?php foreach (INSC_TIPOS_EXTRA as $tk => $tl): ?><option value="<?= e($tk) ?>"><?= e($tl) ?></option><?php endforeach; ?>
                </select>
              </div>
              <label class="fe-check"><input type="checkbox" name="nuevo[<?= $i ?>][r]" value="1"/> Obligatorio</label>
            </div>
            <div class="field" data-opciones hidden>
              <label for="n-<?= $i ?>-o">Opciones de la lista <span class="hint">(una por línea, al menos dos)</span></label>
              <textarea id="n-<?= $i ?>-o" name="nuevo[<?= $i ?>][o]" rows="4" placeholder="Sí&#10;No"></textarea>
            </div>
            <p class="fe-nota">Se agrega al final de la sección cuando guardás. Lo que respondan las familias aparece en el detalle de cada inscripción y en el CSV.</p>
          </div>
        </details>

      <?php endif; ?>
    </section>
  <?php endforeach; ?>

  <div class="fe-guardar">
    <button type="submit" class="btn btn-primary">Guardar cambios</button>
    <a href="<?= e($b['archivo']) ?>?vista=1" target="_blank" rel="noopener" class="btn btn-secundario">Ver cómo queda ↗</a>
    <span>Los cambios valen para las inscripciones que lleguen de ahora en más; las ya recibidas no se modifican.</span>
  </div>
</form>

<?php if ($editado): ?>
  <form method="post" action="gestion_formularios?f=<?= e($clave) ?>" class="fe-restaurar"
        onsubmit="return confirm('¿Volver «<?= e(addslashes($b['nombre'])) ?>» a su versión original? Se pierden los textos cambiados y los campos agregados de este formulario.');">
    <?= csrf_input() ?>
    <input type="hidden" name="accion" value="restaurar"/>
    <button type="submit">Volver este formulario a su versión original</button>
  </form>
<?php endif; ?>

<script>
  // Las opciones solo se piden cuando el tipo es "Lista de opciones"
  document.querySelectorAll('select[data-tipo]').forEach(function (sel) {
    var caja = sel.closest('.fe-campo').querySelector('[data-opciones]');
    if (!caja) return;
    function sync() { caja.hidden = sel.value !== 'select'; }
    sel.addEventListener('change', sync);
    sync();
  });
  // Un campo que no se muestra queda atenuado al destildar "Se muestra"
  document.querySelectorAll('input[name$="[visible]"]').forEach(function (chk) {
    chk.addEventListener('change', function () {
      chk.closest('.fe-campo').classList.toggle('is-oculto', !chk.checked);
    });
  });
  // Aviso si se sale con cambios sin guardar
  (function () {
    var form = document.querySelector('.fe-form'), sucio = false;
    form.addEventListener('input', function () { sucio = true; });
    form.addEventListener('submit', function () { sucio = false; });
    window.addEventListener('beforeunload', function (ev) { if (sucio) { ev.preventDefault(); ev.returnValue = ''; } });
  })();
</script>

<style>
  .fe-tabs { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1.2rem; }
  .fe-tab { background: #fff; border-radius: 999px; padding: .5rem 1rem; font-size: .88rem; font-weight: 700; color: var(--blue-dark); box-shadow: var(--shadow-sm); border: 2px solid transparent; }
  .fe-tab:hover { border-color: var(--blue-light); }
  .fe-tab.act { background: var(--blue-dark); color: #fff; }

  .fe-estado { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; background: #fff; border-radius: 16px; box-shadow: var(--shadow-sm); padding: 1rem 1.3rem; margin-bottom: 1.2rem; }
  .fe-estado b { font-family: var(--font-display); font-size: 1.15rem; color: var(--blue-dark); margin-right: .5rem; }
  .fe-estado small { display: block; font-size: .8rem; color: #667; margin-top: .3rem; }
  .fe-estado small a { color: var(--blue-mid); font-weight: 700; }
  .fe-estado .btn, .fe-guardar .btn { padding: .6rem 1.2rem; font-size: .88rem; }
  .fe-pill { display: inline-block; font-size: .72rem; font-weight: 800; padding: .15rem .6rem; border-radius: 999px; margin-right: .3rem; }
  .fe-pill--on  { background: #eaf7ef; color: #1b7a44; }
  .fe-pill--off { background: var(--gray-200); color: #556; }
  .fe-pill--mod { background: #fff4d6; color: #7a5b00; }

  .fe-card { background: #fff; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); padding: 1.5rem 1.6rem; margin-bottom: 1.2rem; }
  .fe-card h2 { font-family: var(--font-display); font-size: 1.25rem; color: var(--blue-dark); margin-bottom: 1rem; }
  .fe-card h3 { font-family: var(--font-display); font-size: 1.02rem; color: var(--blue-dark); margin: 1.2rem 0 .2rem; }
  .fe-grid2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 1rem; }
  .fe-ancho { grid-column: 1 / -1; }
  .fe-card .field { margin-bottom: .8rem; }
  .fe-card .field textarea { min-height: 0; }
  .fe-nota { font-size: .8rem; color: #7a8294; margin: 0 0 .7rem; }

  .fe-paso { display: grid; grid-template-columns: 28px minmax(0, 1fr) minmax(0, 1.6fr); gap: .5rem; align-items: center; margin-bottom: .45rem; }
  .fe-paso-n { width: 28px; height: 28px; border-radius: 50%; background: var(--gray-200); color: var(--blue-dark); font-weight: 800; font-size: .8rem; display: flex; align-items: center; justify-content: center; }
  .fe-paso input { font-family: var(--font-body); font-size: .9rem; padding: .5rem .7rem; border: 1.5px solid #dfe3e8; border-radius: 9px; min-width: 0; }

  .fe-sec-titulo label { text-transform: uppercase; letter-spacing: .05em; font-size: .72rem; color: var(--blue-mid); }
  .fe-sec-titulo input { font-family: var(--font-display); font-size: 1.15rem; font-weight: 700; color: var(--blue-dark); }

  .fe-campo { border: 1px solid #e6e9ed; border-radius: 14px; padding: 1rem 1.1rem .4rem; margin-bottom: .8rem; background: #fafbfc; }
  .fe-campo.is-oculto { opacity: .55; background: repeating-linear-gradient(135deg, #fafbfc 0 12px, #f1f3f5 12px 24px); }
  .fe-campo--extra { border-color: #cfe0ee; background: #f5f9fc; }
  .fe-campo-top { display: flex; flex-wrap: wrap; gap: .4rem 1.2rem; align-items: flex-end; }
  .fe-campo-l { flex: 1 1 260px; }
  .fe-campo-t { flex: 0 1 190px; }
  .fe-tipo { font-size: .66rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; background: var(--gray-200); color: #556; padding: .1rem .45rem; border-radius: 999px; margin-left: .3rem; }
  .fe-tipo--extra { background: #d9e8f3; color: #1D3557; }
  .fe-check { display: inline-flex; align-items: center; gap: .4rem; font-size: .86rem; font-weight: 700; color: var(--blue-dark); margin-bottom: 1.1rem; cursor: pointer; white-space: nowrap; }
  .fe-check input { width: 17px; height: 17px; accent-color: var(--blue-dark); }
  .fe-check--borrar { color: #c1121f; }
  .fe-check--borrar input { accent-color: #c1121f; }
  .fe-fijo { font-size: .8rem; font-weight: 700; color: #7a8294; margin-bottom: 1.15rem; white-space: nowrap; }

  .fe-nuevo { margin-top: .4rem; }
  .fe-nuevo summary { cursor: pointer; font-size: .88rem; font-weight: 800; color: var(--blue-mid); padding: .5rem 0; list-style: none; }
  .fe-nuevo summary::-webkit-details-marker { display: none; }
  .fe-nuevo summary:hover { color: var(--red); }
  .fe-nuevo[open] summary { margin-bottom: .5rem; }

  .fe-guardar { position: sticky; bottom: 0; z-index: 5; display: flex; align-items: center; gap: .8rem; flex-wrap: wrap; background: rgba(255,255,255,.96); backdrop-filter: blur(6px); border-radius: 16px 16px 0 0; box-shadow: 0 -6px 24px rgba(29,53,87,.14); padding: .9rem 1.3rem; }
  .fe-guardar span { font-size: .8rem; color: #667; flex: 1 1 260px; }
  .fe-restaurar { margin-top: 1.4rem; text-align: right; }
  .fe-restaurar button { background: none; border: 1.5px solid rgba(193,18,31,.35); color: #c1121f; font-weight: 700; font-family: var(--font-body); font-size: .84rem; padding: .5rem 1rem; border-radius: 10px; cursor: pointer; }
  .fe-restaurar button:hover { background: #fdecee; }

  @media (max-width: 720px) {
    .fe-grid2 { grid-template-columns: 1fr; }
    .fe-paso { grid-template-columns: 28px minmax(0, 1fr); }
    .fe-paso input:last-child { grid-column: 2; }
  }
</style>

  </div>
</body>
</html>
