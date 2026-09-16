<?php
// ============================================================
//  partials/inscripcion_pagina.php
//  Página pública de un formulario de inscripción.
//
//  Es una página SEPARADA del sitio: no lleva el menú principal,
//  solo una barra con el logo y "Volver al sitio". Se llega desde
//  la página inscripciones.php (menú principal → Inscripciones).
//
//  Si el formulario está deshabilitado desde el panel, se muestra
//  únicamente el aviso "Actualmente el formulario no está habilitado".
//
//  Uso (antes del require):  $insc_clave = 'jardin' | 'primaria' | 'sec' | 'hermanos';
// ============================================================

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

require_once __DIR__ . '/inscripciones.php';

$def = insc_formulario($insc_clave ?? '');
if ($def === null) {
    http_response_code(404);
    exit('Formulario inexistente.');
}

// ── ¿Está habilitado? (sin base = deshabilitado, nunca un error) ──
$pdo        = db_opcional();
$habilitado = false;
if ($pdo !== null && insc_asegurar_tablas($pdo)) {
    try {
        $habilitado = insc_habilitado($pdo, $insc_clave);
    } catch (Throwable $ex) {
        $habilitado = false;
    }
}

if (empty($_SESSION['insc_token'])) {
    $_SESSION['insc_token'] = bin2hex(random_bytes(24));
}

$valores     = [];
$errores     = [];
$error_envio = '';
$enviado     = null;

// ── Envío ────────────────────────────────────────────────────
if ($habilitado && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);

    $token_ok = is_string($_POST['insc_token'] ?? null)
        && hash_equals($_SESSION['insc_token'], $_POST['insc_token']);

    if (!$token_ok) {
        $error_envio = 'La página estuvo abierta mucho tiempo. Revisá los datos y volvé a enviar.';
        $valores = insc_procesar($insc_clave, $_POST)['valores'];
    } elseif (trim((string) ($_POST['sitio_web'] ?? '')) !== '') {
        // Honeypot: un humano no ve este campo. Simulamos éxito sin guardar.
        $_SESSION['insc_enviado'] = ['clave' => $insc_clave, 'grupo' => '—', 'n' => 1];
        header('Location: ' . $def['archivo'] . '?enviado=1');
        exit;
    } elseif (insc_limite_superado($pdo, $ip)) {
        $error_envio = 'Recibimos varios envíos seguidos desde tu conexión. Esperá unos minutos y volvé a intentar.';
        $valores = insc_procesar($insc_clave, $_POST)['valores'];
    } else {
        $r = insc_procesar($insc_clave, $_POST);
        $valores = $r['valores'];
        $errores = $r['errores'];
        if (!$errores) {
            try {
                $grupo = insc_guardar($pdo, $insc_clave, $r['alumnos'], $ip);
                $_SESSION['insc_enviado'] = ['clave' => $insc_clave, 'grupo' => $grupo, 'n' => count($r['alumnos'])];
                header('Location: ' . $def['archivo'] . '?enviado=1');   // PRG: F5 no reenvía
                exit;
            } catch (Throwable $ex) {
                error_log('[juan23] inscripción no guardada: ' . $ex->getMessage());
                $error_envio = 'No pudimos guardar la inscripción por un problema técnico. Probá de nuevo en unos minutos.';
            }
        } else {
            $error_envio = 'Hay datos para revisar. Los campos marcados en rojo necesitan corrección.';
        }
    }
}

if (isset($_GET['enviado']) && ($_SESSION['insc_enviado']['clave'] ?? '') === $insc_clave) {
    $enviado = $_SESSION['insc_enviado'];
    unset($_SESSION['insc_enviado']);
}

// ── Helpers de vista ─────────────────────────────────────────

/** Dibuja un campo con su etiqueta, valor previo y error. */
function insc_campo(array $c, string $name, string $valor, ?string $error, string $id): void {
    $req   = !empty($c['r']);
    $full  = !empty($c['full']) || $c['t'] === 'textarea';
    $attrs = ' id="' . e($id) . '" name="' . e($name) . '"'
           . ($req ? ' required' : '')
           . ($error ? ' aria-invalid="true" aria-describedby="' . e($id) . '_err"' : '');
    ?>
    <div class="field<?= $full ? ' full' : '' ?><?= $error ? ' has-error' : '' ?>" data-campo="<?= e($c['n']) ?>">
      <label for="<?= e($id) ?>"><?= e($c['l']) ?><?= $req ? ' <span class="req">*</span>' : '' ?></label>
      <?php if ($c['t'] === 'select'): ?>
        <select<?= $attrs ?>>
          <option value=""<?= $valor === '' ? ' selected' : '' ?>>Seleccionar…</option>
          <?php $es_lista = array_is_list($c['o']);
          foreach ($c['o'] as $k => $o):
            if (is_array($o)): ?>
              <optgroup label="<?= e((string) $k) ?>">
                <?php foreach ($o as $kk => $oo): ?>
                  <option value="<?= e((string) $kk) ?>"<?= (string) $kk === $valor ? ' selected' : '' ?>><?= e($oo) ?></option>
                <?php endforeach; ?>
              </optgroup>
            <?php else:
              $val = $es_lista ? (string) $o : (string) $k; ?>
              <option value="<?= e($val) ?>"<?= $val === $valor ? ' selected' : '' ?>><?= e($o) ?></option>
            <?php endif;
          endforeach; ?>
        </select>
      <?php elseif ($c['t'] === 'textarea'): ?>
        <textarea<?= $attrs ?> maxlength="<?= (int) ($c['max'] ?? 2000) ?>" placeholder="<?= e($c['ph'] ?? '') ?>"><?= e($valor) ?></textarea>
      <?php else:
        $tipo = match ($c['t']) { 'dni' => 'text', default => $c['t'] };
        $extra = match ($c['t']) {
            'dni'   => ' inputmode="numeric" pattern="[0-9.\s]{7,10}" maxlength="10"',
            'date'  => ' max="' . date('Y-m-d') . '"',
            'email' => ' autocomplete="email" maxlength="160"',
            'tel'   => ' autocomplete="tel" maxlength="25"',
            default => ' maxlength="' . (int) ($c['max'] ?? 160) . '"',
        }; ?>
        <input type="<?= e($tipo) ?>"<?= $attrs . $extra ?> value="<?= e($valor) ?>" placeholder="<?= e($c['ph'] ?? '') ?>"/>
      <?php endif; ?>
      <?php if (!empty($c['hint'])): ?><span class="hint"><?= e($c['hint']) ?></span><?php endif; ?>
      <?php if ($error): ?><span class="field-error" id="<?= e($id) ?>_err"><?= e($error) ?></span><?php endif; ?>
    </div>
    <?php
}

/** Bloque de un hijo/a (formulario de hermanos). $i puede ser '__N__' para la plantilla JS. */
function insc_bloque_hijo($i, array $vals, array $errores): void {
    $num = is_int($i) ? $i + 1 : '__NUM__';
    ?>
    <div class="herm-row" data-hijo>
      <div class="herm-head">
        <h4>Hijo/a <span class="herm-num"><?= $num ?></span></h4>
        <button type="button" class="herm-quitar" aria-label="Quitar este hijo/a">Quitar</button>
      </div>
      <div class="insc-grid">
        <?php foreach (insc_campos_hijo() as $c):
          insc_campo($c, "hijos[$i][{$c['n']}]", (string) ($vals[$c['n']] ?? ''),
                     $errores["hijos.$i.{$c['n']}"] ?? null, "h{$i}_{$c['n']}");
        endforeach; ?>
      </div>
    </div>
    <?php
}

$colegio = cfg('nombre_colegio');
?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Inscripción <?= e(strip_tags($def['nombre'])) ?> — <?= e($colegio) ?></title>
  <meta name="description" content="Preinscripción <?= e($def['nombre']) ?> del <?= e($colegio) ?>."/>
<?php require __DIR__ . '/favicon.php'; ?>
  <meta name="theme-color" content="#1D3557"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link rel="stylesheet" href="styles.css"/>
  <link rel="stylesheet" href="inscripciones.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet"/>
  <style>:root { --accent: <?= e($def['accent']) ?>; --accent-light: <?= e($def['accent_light']) ?>; }</style>
</head>
<body class="insc-body">

  <header class="insc-top">
    <div class="insc-top-inner">
      <a href="index.php" class="insc-brand">
        <img src="img/logo.png" alt="" width="40" height="42"/>
        <span><?= e($colegio) ?></span>
      </a>
      <a href="inscripciones.php" class="insc-volver">
        <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
        Todas las inscripciones
      </a>
    </div>
  </header>

  <section class="insc-hero">
    <div class="insc-hero-inner">
      <div class="insc-hero-icon" aria-hidden="true"><?= $def['icono'] ?></div>
      <div>
        <h1><?= $def['titulo'] ?></h1>
        <p><?= e($def['bajada']) ?></p>
      </div>
    </div>
  </section>

  <main class="insc-page" id="contenido">

  <?php if (!$habilitado && !$enviado): ?>

    <div class="insc-cerrado" role="status">
      <div class="insc-cerrado-icono" aria-hidden="true">
        <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      </div>
      <p>Actualmente el formulario no está habilitado.</p>
    </div>

  <?php elseif ($enviado): ?>

    <div class="insc-ok" role="status">
      <div class="insc-ok-icono" aria-hidden="true">
        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
      </div>
      <h2>¡Recibimos la preinscripción!</h2>
      <p>
        <?= $enviado['n'] > 1 ? 'Registramos a los ' . (int) $enviado['n'] . ' hijos/as.' : 'Registramos los datos del alumno/a.' ?>
        La Secretaría se va a comunicar con vos por teléfono o correo para seguir con el proceso.
      </p>
      <?php if ($enviado['grupo'] !== '—'): ?>
        <p class="insc-ok-codigo">Código de envío: <b><?= e($enviado['grupo']) ?></b></p>
      <?php endif; ?>
      <a href="index.php" class="insc-submit">Volver al sitio</a>
    </div>

  <?php else: ?>

    <?php if (!empty($def['pasos'])): ?>
    <ol class="insc-steps">
      <?php foreach ($def['pasos'] as $n => [$t, $d]): ?>
        <li class="insc-step"><div class="n"><?= $n + 1 ?></div><div class="t"><?= e($t) ?></div><div class="d"><?= e($d) ?></div></li>
      <?php endforeach; ?>
    </ol>
    <?php endif; ?>

    <?php if (!empty($def['aviso'])): ?>
      <div class="insc-banner"><?= $def['aviso'] ?></div>
    <?php endif; ?>

    <?php if ($error_envio): ?>
      <div class="insc-alerta" role="alert"><?= e($error_envio) ?></div>
    <?php endif; ?>

    <form class="insc-form" method="post" action="<?= e($def['archivo']) ?>">
      <input type="hidden" name="insc_token" value="<?= e($_SESSION['insc_token']) ?>"/>
      <div class="insc-hp" aria-hidden="true">
        <label for="sitio_web">No completar</label>
        <input type="text" id="sitio_web" name="sitio_web" tabindex="-1" autocomplete="off"/>
      </div>

      <?php foreach ($def['secciones'] as $sec): ?>
        <fieldset class="insc-fieldset">
          <legend class="insc-legend"><span class="ix" aria-hidden="true">●</span> <?= e($sec['titulo']) ?></legend>

          <?php if (!empty($sec['hijos'])): ?>
            <?php if (isset($errores['hijos'])): ?>
              <div class="insc-alerta insc-alerta--chica" role="alert"><?= e($errores['hijos']) ?></div>
            <?php endif; ?>
            <div id="hijosWrap">
              <?php foreach (($valores['hijos'] ?? [[], []]) as $i => $vh):
                insc_bloque_hijo($i, $vh, $errores);
              endforeach; ?>
            </div>
            <button type="button" class="herm-add" id="addHijo">＋ Agregar otro/a hijo/a</button>
            <template id="tplHijo"><?php insc_bloque_hijo('__N__', [], []); ?></template>
          <?php else: ?>
            <div class="insc-grid">
              <?php foreach ($sec['campos'] as $c):
                insc_campo($c, $c['n'], (string) ($valores[$c['n']] ?? ''), $errores[$c['n']] ?? null, 'f_' . $c['n']);
              endforeach; ?>
            </div>
          <?php endif; ?>
        </fieldset>
      <?php endforeach; ?>

      <label class="insc-check<?= isset($errores['acepto']) ? ' has-error' : '' ?>">
        <input type="checkbox" name="acepto" value="1"<?= !empty($valores['acepto']) ? ' checked' : '' ?> required/>
        <span>Declaro que los datos consignados son correctos y acepto ser contactado/a por la institución.
          <?php if (isset($errores['acepto'])): ?><span class="field-error"><?= e($errores['acepto']) ?></span><?php endif; ?>
        </span>
      </label>

      <div class="insc-actions">
        <button type="submit" class="insc-submit">Enviar preinscripción</button>
        <span class="insc-note">* Campos obligatorios</span>
      </div>
    </form>

  <?php endif; ?>
  </main>

  <footer class="insc-pie">
    <p>
      ¿Dudas? Escribinos a <a href="mailto:<?= cfg_e('email_admisiones') ?>"><?= cfg_e('email_admisiones') ?></a>
      <?php if (cfg('telefono')): ?> o llamanos al <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', cfg('telefono_link'))) ?>"><?= cfg_e('telefono') ?></a><?php endif; ?>.
    </p>
    <p class="insc-pie-copy">© <?= date('Y') ?> <?= e($colegio) ?></p>
  </footer>

<?php if ($habilitado && !$enviado): ?>
  <script>
  (function () {
    'use strict';
    var form = document.querySelector('.insc-form');

    // Llevar la vista al primer error del servidor
    var primerError = document.querySelector('.has-error, .insc-alerta');
    if (primerError) primerError.scrollIntoView({ block: 'center' });

    // Evitar doble envío
    form.addEventListener('submit', function () {
      var b = form.querySelector('.insc-submit');
      setTimeout(function () { b.disabled = true; b.textContent = 'Enviando…'; }, 0);
    });

    // Secundario: 7° año solo existe en la modalidad Técnica
    function atarModalidadAnio(selMod, selAnio, valorDe7) {
      if (!selMod || !selAnio) return;
      function sync() {
        var opt = selAnio.querySelector('option[value="' + valorDe7 + '"]');
        if (!opt) return;
        var orientada = selMod.value === 'Orientada';
        opt.disabled = orientada;
        if (orientada && selAnio.value === valorDe7) selAnio.value = '';
      }
      selMod.addEventListener('change', sync);
      sync();
    }
    atarModalidadAnio(document.getElementById('f_modalidad'), document.getElementById('f_anio'), '7');

    // ── Hermanos: agregar / quitar hijos ──
    var wrap = document.getElementById('hijosWrap');
    var tpl  = document.getElementById('tplHijo');
    var add  = document.getElementById('addHijo');
    if (!wrap || !tpl || !add) return;
    var MAX = <?= INSC_MAX_HIJOS ?>;
    var sig = wrap.querySelectorAll('[data-hijo]').length;

    function prepararBloque(bloque) {
      var nivel = bloque.querySelector('[data-campo="nivel"] select');
      var modCampo = bloque.querySelector('[data-campo="modalidad"]');
      var mod = modCampo.querySelector('select');
      function sync() {
        var esSec = nivel.value.indexOf('Secundario|') === 0;
        modCampo.hidden = !esSec;
        mod.required = esSec;
        if (!esSec) mod.value = '';
        var op7 = nivel.querySelector('option[value="Secundario|7"]');
        if (op7) {
          op7.disabled = mod.value === 'Orientada';
          if (op7.disabled && nivel.value === 'Secundario|7') nivel.value = '';
        }
      }
      nivel.addEventListener('change', sync);
      mod.addEventListener('change', sync);
      sync();
      bloque.querySelector('.herm-quitar').addEventListener('click', function () {
        bloque.remove();
        renumerar();
      });
    }
    function renumerar() {
      var bloques = wrap.querySelectorAll('[data-hijo]');
      bloques.forEach(function (b, i) {
        b.querySelector('.herm-num').textContent = i + 1;
        b.querySelector('.herm-quitar').hidden = bloques.length <= 2;
      });
      add.hidden = bloques.length >= MAX;
    }

    wrap.querySelectorAll('[data-hijo]').forEach(prepararBloque);
    renumerar();

    add.addEventListener('click', function () {
      var html = tpl.innerHTML.replace(/__N__/g, String(sig++)).replace(/__NUM__/g, '');
      var tmp = document.createElement('div');
      tmp.innerHTML = html;
      var bloque = tmp.firstElementChild;
      wrap.appendChild(bloque);
      prepararBloque(bloque);
      renumerar();
      var primero = bloque.querySelector('input');
      if (primero) primero.focus();
    });
  })();
  </script>
<?php endif; ?>
</body>
</html>
