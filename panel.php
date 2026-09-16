<?php
require_once __DIR__ . '/panel_config.php';
exigir_login();

$u = usuario_actual();

// Contadores rápidos para mostrar en las tarjetas
try {
    $n_nov = (int) db()->query('SELECT COUNT(*) FROM novedades')->fetchColumn();
    $n_evt = (int) db()->query('SELECT COUNT(*) FROM agenda')->fetchColumn();
} catch (Throwable $e) {
    $n_nov = $n_evt = 0;
}
// Escenas del recorrido virtual (en try aparte por si la tabla aún no existe)
try {
    $n_esc = (int) db()->query('SELECT COUNT(*) FROM tour_escenas')->fetchColumn();
} catch (Throwable $e) {
    $n_esc = 0;
}
// Cuentas de Instagram configuradas
try {
    $n_ig = (int) db()->query('SELECT COUNT(*) FROM ig_cuentas')->fetchColumn();
} catch (Throwable $e) {
    $n_ig = 0;
}

// Inscripciones nuevas sin revisar
try {
    $n_ins_nuevas = (int) db()->query("SELECT COUNT(*) FROM inscripciones WHERE estado = 'Nueva'")->fetchColumn();
} catch (Throwable $e) {
    $n_ins_nuevas = 0;
}

$panel_page_title = 'Panel de Control';
$panel_heading    = 'Panel de <span>Control</span>';
$panel_sub        = 'Gestioná los contenidos del sitio del colegio.';
require __DIR__ . '/panel_header.php';
?>

    <div class="action-grid">

      <!-- Novedades -->
      <a href="gestion_novedades.php" class="action-card">
        <div class="ac-icon">
          <svg viewBox="0 0 24 24"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8V6Z"/></svg>
        </div>
        <h3>Novedades</h3>
        <p>Creá y gestioná las novedades que se publican en el sitio. Actualmente hay <strong><?= $n_nov ?></strong> publicada<?= $n_nov === 1 ? '' : 's' ?>.</p>
        <span class="ac-cta">Ir a novedades
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </span>
      </a>

      <!-- Agenda -->
      <a href="gestion_agenda.php" class="action-card blue">
        <div class="ac-icon">
          <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <h3>Agenda</h3>
        <p>Cargá y administrá los eventos del calendario institucional. Hay <strong><?= $n_evt ?></strong> evento<?= $n_evt === 1 ? '' : 's' ?> cargado<?= $n_evt === 1 ? '' : 's' ?>.</p>
        <span class="ac-cta">Ir a agenda
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </span>
      </a>

      <!-- Recorrido Virtual 360° -->
      <a href="gestion_tour.php" class="action-card blue">
        <div class="ac-icon">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
        </div>
        <h3>Recorrido Virtual 360°</h3>
        <p>Subí fotos esféricas del colegio y marcá los puntos de transición entre espacios. Hay <strong><?= $n_esc ?></strong> escena<?= $n_esc === 1 ? '' : 's' ?> cargada<?= $n_esc === 1 ? '' : 's' ?>.</p>
        <span class="ac-cta">Ir al recorrido
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </span>
      </a>

      <?php if (puede('inscripciones')): ?>
      <!-- Inscripciones -->
      <a href="gestion_inscripciones.php" class="action-card">
        <div class="ac-icon">
          <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
        </div>
        <h3>Inscripciones</h3>
        <p>Habilitá los formularios y revisá lo que enviaron las familias por nivel, modalidad y año.
          <?php if ($n_ins_nuevas > 0): ?>Hay <strong><?= $n_ins_nuevas ?></strong> nueva<?= $n_ins_nuevas === 1 ? '' : 's' ?> sin revisar.<?php endif; ?></p>
        <span class="ac-cta">Ir a inscripciones
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </span>
      </a>
      <?php else: ?>
      <!-- Inscripciones (sin permiso) -->
      <div class="action-card muted is-locked" aria-disabled="true">
        <span class="lock-badge">
          <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          Sin permiso
        </span>
        <div class="ac-icon">
          <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
        </div>
        <h3>Inscripciones</h3>
        <p>Tu nivel de permisos no incluye la gestión de inscripciones.</p>
        <span class="ac-cta" style="color:var(--gray-500);">Pedile acceso a un administrador</span>
      </div>
      <?php endif; ?>

      <?php if (es_admin()): ?>
      <!-- Usuarios (solo admin) -->
      <a href="gestion_usuarios.php" class="action-card">
        <div class="ac-icon">
          <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
        </div>
        <h3>Usuarios</h3>
        <p>Alta y administración de usuarios del sistema. Solo disponible para administradores.</p>
        <span class="ac-cta">Gestionar usuarios
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </span>
      </a>

      <!-- Instagram (solo admin) -->
      <a href="gestion_instagram.php" class="action-card">
        <div class="ac-icon">
          <svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
        </div>
        <h3>Instagram</h3>
        <p>Cada cuenta publica sola en su etiqueta de novedades. Hay <strong><?= $n_ig ?></strong> cuenta<?= $n_ig === 1 ? '' : 's' ?> configurada<?= $n_ig === 1 ? '' : 's' ?>.</p>
        <span class="ac-cta">Ver cuentas
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </span>
      </a>

      <!-- Datos de contacto (solo admin) -->
      <a href="gestion_contacto.php" class="action-card">
        <div class="ac-icon">
          <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
        </div>
        <h3>Datos de contacto</h3>
        <p>Teléfono, email, dirección, horarios y redes sociales. Lo que cambies acá se actualiza en todo el sitio al instante.</p>
        <span class="ac-cta">Editar datos
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </span>
      </a>

      <!-- Permisos (solo admin) -->
      <a href="gestion_permisos.php" class="action-card blue">
        <div class="ac-icon">
          <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/><circle cx="12" cy="16" r="1.6"/></svg>
        </div>
        <h3>Permisos</h3>
        <p>Creá niveles de permiso y definí qué puede hacer cada uno: novedades, agenda, recorrido 360°, usuarios e inscripciones.</p>
        <span class="ac-cta">Gestionar permisos
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </span>
      </a>
      <?php endif; ?>

    </div>

    <p style="margin-top:2.5rem;">
      <a href="index.php" class="back-link">
        <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
        Volver al sitio público
      </a>
    </p>
  </div>
</body>
</html>
