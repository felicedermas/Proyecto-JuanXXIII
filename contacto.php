<?php
// Página generada sobre partials/header.php + partials/footer.php.
// El menú se edita en partials/menu.php; los datos de contacto,
// desde el panel (Datos de contacto).
// Los mensajes del formulario se guardan en `mensajes_contacto`
// y se leen en el panel → Mensajes de contacto.
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
require_once __DIR__ . '/partials/mensajes.php';

if (empty($_SESSION['contacto_token'])) {
    $_SESSION['contacto_token'] = bin2hex(random_bytes(24));
}

$c_val   = ['nombre' => '', 'email' => '', 'telefono' => '', 'asunto' => 'Consulta general', 'nivel' => 'General', 'mensaje' => ''];
$c_err   = [];
$c_aviso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip    = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
    $r     = msj_procesar($_POST);
    $c_val = $r['valores'];
    $pdo   = db_opcional();

    if (!is_string($_POST['contacto_token'] ?? null) || !hash_equals($_SESSION['contacto_token'], $_POST['contacto_token'])) {
        $c_aviso = 'La página estuvo abierta mucho tiempo. Revisá los datos y volvé a enviar.';
    } elseif (trim((string) ($_POST['sitio_web'] ?? '')) !== '') {
        // Honeypot: un humano no ve este campo. Simulamos éxito sin guardar.
        $_SESSION['contacto_enviado'] = true;
        header('Location: contacto?enviado=1#formulario');
        exit;
    } elseif ($r['errores']) {
        $c_err   = $r['errores'];
        $c_aviso = 'Hay datos para revisar. Los campos marcados en rojo necesitan corrección.';
    } elseif ($pdo === null || !msj_asegurar_tabla($pdo)) {
        $c_aviso = 'No pudimos enviar tu mensaje por un problema técnico. Probá de nuevo en unos minutos.';
    } elseif (msj_limite_superado($pdo, $ip)) {
        $c_aviso = 'Recibimos varios mensajes seguidos desde tu conexión. Esperá unos minutos y volvé a intentar.';
    } else {
        try {
            msj_guardar($pdo, $c_val, $ip);
            $_SESSION['contacto_enviado'] = true;
            header('Location: contacto?enviado=1#formulario');   // PRG: F5 no reenvía
            exit;
        } catch (Throwable $ex) {
            error_log('[juan23] mensaje de contacto no guardado: ' . $ex->getMessage());
            $c_aviso = 'No pudimos enviar tu mensaje por un problema técnico. Probá de nuevo en unos minutos.';
        }
    }
}

$c_enviado = isset($_GET['enviado']) && !empty($_SESSION['contacto_enviado']);
unset($_SESSION['contacto_enviado']);

/** Clase, atributos y texto de error de un campo del formulario. */
function c_campo_err(array $err, string $campo): array {
    if (!isset($err[$campo])) return ['', '', ''];
    return [
        ' has-error',
        ' aria-invalid="true" aria-describedby="c-' . $campo . '-err"',
        '<span class="field-error" id="c-' . $campo . '-err">' . e($err[$campo]) . '</span>',
    ];
}

$page_title      = 'Contacto';
$page_desc       = 'Contactate con el Colegio Parroquial Juan XXIII: teléfono, email, horarios de secretaría y formulario de consulta.';
$nav_active      = 'contacto';
$nav_active_link = 'contacto';
require __DIR__ . '/partials/header.php';
?>
  

  <?php page_hero(['Inicio' => './', 'Contacto' => null], 'Estamos para ayudarte', '<em>Contacto</em>', '¿Tenés una consulta sobre inscripciones, niveles o la institución? Escribinos y te responderemos a la brevedad.'); ?>

  <section class="content-section">
    <div class="split">
      <div>
        <div class="form-wrap" id="formulario">
          <?php if ($c_enviado): ?>
            <div class="form-ok" role="status">
              <div class="form-ok-icono"><svg viewBox="0 0 24 24"><polyline points="4 13 9 18 20 6"/></svg></div>
              <h3>¡Gracias por escribirnos!</h3>
              <p>Recibimos tu mensaje. Te vamos a responder a la brevedad al correo que nos dejaste.</p>
              <a href="contacto#formulario" class="btn btn-primary">Enviar otro mensaje</a>
            </div>
          <?php else: ?>
          <?php if ($c_aviso): ?><div class="form-alerta" role="alert"><?= e($c_aviso) ?></div><?php endif; ?>
          <form method="post" action="contacto#formulario" class="form-grid" novalidate>
            <input type="hidden" name="contacto_token" value="<?= e($_SESSION['contacto_token']) ?>">
            <div class="form-hp" aria-hidden="true">
              <label for="c-web">No completar este campo</label>
              <input id="c-web" type="text" name="sitio_web" tabindex="-1" autocomplete="off">
            </div>
            <?php [$cls, $att, $msg] = c_campo_err($c_err, 'nombre'); ?>
            <div class="form-field<?= $cls ?>">
              <label for="c-nombre">Nombre y apellido</label>
              <input id="c-nombre" name="nombre" type="text" placeholder="Tu nombre" required maxlength="120" autocomplete="name" value="<?= e($c_val['nombre']) ?>"<?= $att ?>>
              <?= $msg ?>
            </div>
            <?php [$cls, $att, $msg] = c_campo_err($c_err, 'email'); ?>
            <div class="form-field<?= $cls ?>">
              <label for="c-email">Correo electrónico</label>
              <input id="c-email" name="email" type="email" placeholder="tucorreo@ejemplo.com" required maxlength="160" autocomplete="email" value="<?= e($c_val['email']) ?>"<?= $att ?>>
              <?= $msg ?>
            </div>
            <?php [$cls, $att, $msg] = c_campo_err($c_err, 'telefono'); ?>
            <div class="form-field<?= $cls ?>">
              <label for="c-tel">Teléfono <span class="form-opc">(opcional)</span></label>
              <input id="c-tel" name="telefono" type="tel" placeholder="(011) 1234-5678" maxlength="25" autocomplete="tel" value="<?= e($c_val['telefono']) ?>"<?= $att ?>>
              <?= $msg ?>
            </div>
            <?php [$cls, $att, $msg] = c_campo_err($c_err, 'asunto'); ?>
            <div class="form-field<?= $cls ?>">
              <label for="c-asunto">Asunto</label>
              <select id="c-asunto" name="asunto"<?= $att ?>>
                <?php foreach (MSJ_ASUNTOS as $op): ?>
                  <option<?= $c_val['asunto'] === $op ? ' selected' : '' ?>><?= e($op) ?></option>
                <?php endforeach; ?>
              </select>
              <?= $msg ?>
            </div>
            <?php [$cls, $att, $msg] = c_campo_err($c_err, 'nivel'); ?>
            <div class="form-field full<?= $cls ?>">
              <label for="c-nivel">¿Sobre qué nivel es tu consulta?</label>
              <select id="c-nivel" name="nivel"<?= $att ?>>
                <?php foreach (['General' => 'General / no aplica', 'Inicial' => 'Nivel Inicial', 'Primario' => 'Nivel Primario', 'Secundario' => 'Nivel Secundario'] as $k => $op): ?>
                  <option value="<?= e($k) ?>"<?= $c_val['nivel'] === $k ? ' selected' : '' ?>><?= e($op) ?></option>
                <?php endforeach; ?>
              </select>
              <?= $msg ?>
            </div>
            <?php [$cls, $att, $msg] = c_campo_err($c_err, 'mensaje'); ?>
            <div class="form-field full<?= $cls ?>">
              <label for="c-msg">Mensaje</label>
              <textarea id="c-msg" name="mensaje" placeholder="Escribí tu consulta aquí..." required maxlength="4000"<?= $att ?>><?= e($c_val['mensaje']) ?></textarea>
              <?= $msg ?>
            </div>
            <div class="form-field full">
              <button type="submit" class="btn btn-primary">Enviar mensaje</button>
              <p class="form-aviso">
                También podés escribirnos a
                <a href="mailto:<?= cfg_e('email') ?>"><?= cfg_e('email') ?></a>
                o llamarnos al <a href="tel:<?= e(tel_href()) ?>"><?= cfg_e('telefono') ?></a>.
              </p>
            </div>
          </form>
          <?php endif; ?>
        </div>
      </div>
      <div class="split-media">
        <div class="contact-list">
          <?php /* Todos estos datos salen de la tabla `configuracion`.
                   Se editan en el panel → Datos de contacto. */ ?>
          <?php if (cfg('direccion')): ?>
          <div class="contact-item">
            <div class="ci-icon"><svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></div>
            <div>
              <h4>Dirección</h4>
              <p><?= cfg_e('direccion') ?><?= cfg('localidad') ? ', ' . cfg_e('localidad') : '' ?><?= cfg('codigo_postal') ? ' (' . cfg_e('codigo_postal') . ')' : '' ?></p>
              <?php if (cfg('mapa_link')): ?>
                <p><a href="<?= cfg_e('mapa_link') ?>" target="_blank" rel="noopener" class="link-arrow">Cómo llegar &rarr;</a></p>
              <?php else: ?>
                <p><a href="ubicacion" class="link-arrow">Ver en el mapa &rarr;</a></p>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>

          <?php if (cfg('telefono')): ?>
          <div class="contact-item">
            <div class="ci-icon"><svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div>
            <div>
              <h4>Teléfono</h4>
              <p><a href="tel:<?= e(tel_href()) ?>"><?= cfg_e('telefono') ?></a></p>
              <?php if (cfg('telefono_alt')): ?>
                <p><a href="tel:<?= e(tel_href('telefono_alt_link')) ?>"><?= cfg_e('telefono_alt') ?></a></p>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>

          <?php if (cfg('whatsapp')): ?>
          <div class="contact-item">
            <div class="ci-icon"><svg viewBox="0 0 24 24"><path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 8.5 8.5 0 0 1-3.9-.9L3 20.5l1.6-4.9A8.4 8.4 0 0 1 12 3.1a8.4 8.4 0 0 1 9 8.4Z"/></svg></div>
            <div>
              <h4>WhatsApp</h4>
              <p><a href="https://wa.me/<?= e(preg_replace('/\D/', '', cfg('whatsapp_link'))) ?>" target="_blank" rel="noopener"><?= cfg_e('whatsapp') ?></a></p>
            </div>
          </div>
          <?php endif; ?>

          <?php if (cfg('email')): ?>
          <div class="contact-item">
            <div class="ci-icon"><svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 5L2 7"/></svg></div>
            <div>
              <h4>Correo</h4>
              <p><a href="mailto:<?= cfg_e('email') ?>"><?= cfg_e('email') ?></a></p>
              <?php if (cfg('email_admisiones')): ?>
                <p class="ci-extra">Inscripciones: <a href="mailto:<?= cfg_e('email_admisiones') ?>"><?= cfg_e('email_admisiones') ?></a></p>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>

          <?php if (cfg('horario_atencion')): ?>
          <div class="contact-item">
            <div class="ci-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></div>
            <div>
              <h4>Horario de atención</h4>
              <p><?= cfg_e('horario_atencion') ?></p>
              <?php if (cfg('horario_admision')): ?>
                <p class="ci-extra">Admisiones: <?= cfg_e('horario_admision') ?></p>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  
<?php require __DIR__ . '/partials/footer.php';
