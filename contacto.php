<?php
// Página generada sobre partials/header.php + partials/footer.php.
// El menú se edita en partials/menu.php; los datos de contacto,
// desde el panel (Datos de contacto).
$page_title      = 'Contacto';
$page_desc       = 'Contactate con el Colegio Parroquial Juan XXIII: teléfono, email, horarios de secretaría y formulario de consulta.';
$nav_active      = 'contacto';
$nav_active_link = 'contacto.php';
require __DIR__ . '/partials/header.php';
?>
  

  <?php page_hero(['Inicio' => 'index.php', 'Contacto' => null], 'Estamos para ayudarte', '<em>Contacto</em>', '¿Tenés una consulta sobre inscripciones, niveles o la institución? Escribinos y te responderemos a la brevedad.'); ?>

  <section class="content-section">
    <div class="split">
      <div>
        <div class="form-wrap">
          <div class="form-grid">
            <div class="form-field">
              <label for="c-nombre">Nombre y apellido</label>
              <input id="c-nombre" type="text" placeholder="Tu nombre">
            </div>
            <div class="form-field">
              <label for="c-email">Correo electrónico</label>
              <input id="c-email" type="email" placeholder="tucorreo@ejemplo.com">
            </div>
            <div class="form-field">
              <label for="c-tel">Teléfono</label>
              <input id="c-tel" type="tel" placeholder="(011) 1234-5678">
            </div>
            <div class="form-field">
              <label for="c-asunto">Asunto</label>
              <select id="c-asunto">
                <option>Consulta general</option>
                <option>Inscripciones</option>
                <option>Becas</option>
                <option>Administración</option>
                <option>Otro</option>
              </select>
            </div>
            <div class="form-field full">
              <label for="c-msg">Mensaje</label>
              <textarea id="c-msg" placeholder="Escribí tu consulta aquí..."></textarea>
            </div>
            <div class="form-field full">
              <button type="button" class="btn btn-primary" disabled>Enviar mensaje</button>
              <p class="form-aviso">
                El envío del formulario todavía no está conectado.
                Mientras tanto, escribinos a
                <a href="mailto:<?= cfg_e('email') ?>"><?= cfg_e('email') ?></a>
                o llamanos al <a href="tel:<?= e(tel_href()) ?>"><?= cfg_e('telefono') ?></a>.
              </p>
            </div>
          </div>
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
                <p><a href="ubicacion.php" class="link-arrow">Ver en el mapa &rarr;</a></p>
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
