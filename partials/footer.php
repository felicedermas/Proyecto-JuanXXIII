<?php
// ============================================================
//  partials/footer.php
//  Pie único de TODO el sitio público + scripts comunes.
//  Toda la información de contacto sale de la tabla
//  `configuracion` (panel → Datos de contacto).
//
//  Variable opcional antes del include:
//    $page_script  string  JS extra propio de la página
// ============================================================

$page_script = $page_script ?? '';
$anio        = date('Y');
?>
  </main>

  <footer class="site-footer">
    <div class="footer-inner">

      <div class="footer-brand">
        <img src="img/logo.png" alt="" class="footer-logo" width="86" height="90"/>
        <p><?= cfg_e('pie_descripcion') ?></p>
        <?php if (cfg('facebook') || cfg('instagram') || cfg('youtube')): ?>
        <div class="footer-social">
          <?php if (cfg('facebook')): ?>
            <a href="<?= cfg_e('facebook') ?>" target="_blank" rel="noopener" aria-label="Facebook">
              <svg viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
            </a>
          <?php endif; ?>
          <?php if (cfg('instagram')): ?>
            <a href="<?= cfg_e('instagram') ?>" target="_blank" rel="noopener" aria-label="Instagram">
              <svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
            </a>
          <?php endif; ?>
          <?php if (cfg('youtube')): ?>
            <a href="<?= cfg_e('youtube') ?>" target="_blank" rel="noopener" aria-label="YouTube">
              <svg viewBox="0 0 24 24"><path d="M22.5 7.2a2.8 2.8 0 0 0-2-2C18.8 4.7 12 4.7 12 4.7s-6.8 0-8.5.5a2.8 2.8 0 0 0-2 2A29 29 0 0 0 1 12a29 29 0 0 0 .5 4.8 2.8 2.8 0 0 0 2 2c1.7.5 8.5.5 8.5.5s6.8 0 8.5-.5a2.8 2.8 0 0 0 2-2A29 29 0 0 0 23 12a29 29 0 0 0-.5-4.8z"/><polygon points="9.8 15.3 15.5 12 9.8 8.7"/></svg>
            </a>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>

      <div class="footer-links">
        <h4>Institucional</h4>
        <ul>
          <li><a href="historia.php">Historia</a></li>
          <li><a href="autoridades.php">Autoridades</a></li>
          <li><a href="propuesta-educativa.php">Propuesta Educativa</a></li>
          <li><a href="becas.php">Becas</a></li>
        </ul>
      </div>

      <div class="footer-links">
        <h4>Comunidad</h4>
        <ul>
          <li><a href="centro-estudiantes.php">Centro de Estudiantes</a></li>
          <li><a href="feria-ciencias.php">Feria de Ciencias</a></li>
          <li><a href="trabaja-con-nosotros.php">Trabajá con Nosotros</a></li>
          <li><a href="pastoral.php">Pastoral</a></li>
          <li><a href="recorrido-360.php">Recorrido Virtual 360°</a></li>
        </ul>
      </div>

      <div class="footer-links footer-contact">
        <h4>Contacto</h4>
        <ul>
          <?php if (cfg('direccion')): ?>
            <li class="fc-dato">
              <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
              <span><?= cfg_e('direccion') ?><?= cfg('localidad') ? '<br>' . cfg_e('localidad') : '' ?></span>
            </li>
          <?php endif; ?>
          <?php if (cfg('telefono')): ?>
            <li><a href="tel:<?= e(tel_href()) ?>"><?= cfg_e('telefono') ?></a></li>
          <?php endif; ?>
          <?php if (cfg('telefono_alt')): ?>
            <li><a href="tel:<?= e(tel_href('telefono_alt_link')) ?>"><?= cfg_e('telefono_alt') ?></a></li>
          <?php endif; ?>
          <?php if (cfg('whatsapp')): ?>
            <li><a href="https://wa.me/<?= e(preg_replace('/\D/', '', cfg('whatsapp_link'))) ?>" target="_blank" rel="noopener">WhatsApp <?= cfg_e('whatsapp') ?></a></li>
          <?php endif; ?>
          <?php if (cfg('email')): ?>
            <li><a href="mailto:<?= cfg_e('email') ?>"><?= cfg_e('email') ?></a></li>
          <?php endif; ?>
          <?php if (cfg('horario_atencion')): ?>
            <li class="fc-horario"><?= cfg_e('horario_atencion') ?></li>
          <?php endif; ?>
          <li><a href="contacto.php">Formulario de contacto</a></li>
          <li><a href="ubicacion.php">Ubicación en el mapa</a></li>
        </ul>
      </div>

    </div>

    <div class="footer-bottom">
      <p>© <?= $anio ?> <?= cfg_e('nombre_colegio') ?>. Todos los derechos reservados.</p>
      <!-- Acceso al panel: discreto, y cambia a "Cerrar sesión" si ya ingresaste -->
      <a href="login.php" id="footerLogin" class="footer-login" rel="nofollow">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
        <span id="footerLoginTxt">Acceso institucional</span>
      </a>
    </div>
  </footer>

  <script>
  (function () {
    'use strict';

    // ── Altura real del header ─────────────────────────────
    //  El hero mide "pantalla menos header" para que sus botones
    //  entren sin scrollear. La altura cambia según el ancho, la
    //  variante compacta y cuándo terminan de cargar las fuentes,
    //  así que la medimos en vez de hardcodearla.
    var cab = document.querySelector('.site-header');
    function medirHeader() {
      if (!cab) return;
      document.documentElement.style.setProperty(
        '--header-alto', Math.round(cab.getBoundingClientRect().height) + 'px');
    }
    medirHeader();
    window.addEventListener('resize', medirHeader);
    if (document.fonts && document.fonts.ready) {
      document.fonts.ready.then(medirHeader);
    }

    // ── Menú hamburguesa (mobile) ──────────────────────────
    var toggle = document.getElementById('menuToggle');
    var nav    = document.getElementById('mainNav');
    if (toggle && nav) {
      toggle.addEventListener('click', function () {
        var abierto = nav.classList.toggle('open');
        toggle.classList.toggle('active', abierto);
        toggle.setAttribute('aria-expanded', abierto ? 'true' : 'false');
      });
    }

    // ── Submenús ───────────────────────────────────────────
    //  El problema anterior: los submenús se abrían con :hover puro
    //  de CSS, así que al mover el mouse en diagonal desde el botón
    //  hacia una opción del panel, el puntero salía un instante del
    //  área del <li> y el menú se cerraba de golpe.
    //
    //  Solución: manejarlo por JS con retardo de cierre. Abre al
    //  instante, pero espera CIERRE_MS antes de cerrar; si el mouse
    //  vuelve a entrar (al botón o al panel) se cancela el cierre.
    var CIERRE_MS = 320;
    var esMobile  = function () { return window.matchMedia('(max-width: 768px)').matches; };
    var items     = Array.prototype.slice.call(document.querySelectorAll('.nav-item.has-dropdown'));

    function cerrarTodos(excepto) {
      items.forEach(function (it) {
        if (it === excepto) return;
        it.classList.remove('open');
        var b = it.querySelector('.nav-dropdown-btn');
        if (b) b.setAttribute('aria-expanded', 'false');
        clearTimeout(it._timer);
      });
    }

    function abrir(item) {
      clearTimeout(item._timer);
      cerrarTodos(item);
      item.classList.add('open');
      var b = item.querySelector('.nav-dropdown-btn');
      if (b) b.setAttribute('aria-expanded', 'true');
    }

    function programarCierre(item) {
      clearTimeout(item._timer);
      item._timer = setTimeout(function () {
        item.classList.remove('open');
        var b = item.querySelector('.nav-dropdown-btn');
        if (b) b.setAttribute('aria-expanded', 'false');
      }, CIERRE_MS);
    }

    items.forEach(function (item) {
      var btn = item.querySelector('.nav-dropdown-btn');

      // Desktop: hover con retardo de cierre
      item.addEventListener('mouseenter', function () {
        if (esMobile()) return;
        abrir(item);
      });
      item.addEventListener('mouseleave', function () {
        if (esMobile()) return;
        programarCierre(item);
      });

      // Click: en mobile abre/cierra; en desktop también, por si el
      // usuario prefiere hacer click en vez de esperar el hover.
      if (btn) {
        btn.addEventListener('click', function (ev) {
          ev.preventDefault();
          ev.stopPropagation();
          if (item.classList.contains('open')) {
            item.classList.remove('open');
            btn.setAttribute('aria-expanded', 'false');
          } else {
            abrir(item);
          }
        });
      }

      // Teclado: Escape cierra y devuelve el foco al botón
      item.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape' && item.classList.contains('open')) {
          item.classList.remove('open');
          if (btn) { btn.setAttribute('aria-expanded', 'false'); btn.focus(); }
        }
      });
      // Foco por teclado (Tab): abrir al entrar, cerrar al salir del grupo
      item.addEventListener('focusin',  function () { if (!esMobile()) abrir(item); });
      item.addEventListener('focusout', function (ev) {
        if (esMobile()) return;
        if (!item.contains(ev.relatedTarget)) programarCierre(item);
      });
    });

    document.addEventListener('click', function () { cerrarTodos(null); });

    // ── Estado de sesión: personita en el header y pie ─────
    fetch('session_status.php', { credentials: 'same-origin' })
      .then(function (r) { return r.ok ? r.json() : { logueado: false }; })
      .then(function (data) {
        if (!data || !data.logueado) return;
        var badge  = document.getElementById('userBadge');
        var avatar = document.getElementById('ubAvatar');
        var name   = document.getElementById('ubName');
        var rol    = document.getElementById('ubRol');
        var footer = document.getElementById('footerLogin');
        var ftxt   = document.getElementById('footerLoginTxt');
        if (name)   name.textContent = data.nombre + ' ' + data.apellido;
        if (rol)    rol.textContent  = data.rol || '';
        if (avatar && data.iniciales) avatar.textContent = data.iniciales;
        if (badge)  badge.style.display = 'inline-flex';
        if (footer) footer.setAttribute('href', 'panel.php');
        if (ftxt)   ftxt.textContent = 'Ir al panel';
      })
      .catch(function () { /* sin sesión: queda "Acceso institucional" */ });
  })();
  </script>
<?php if ($page_script !== ''): ?>
  <script>
<?= $page_script ?>
  </script>
<?php endif; ?>
</body>
</html>
