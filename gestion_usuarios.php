<?php
require_once __DIR__ . '/panel_config.php';
exigir_admin();   // ← solo administradores

$u   = usuario_actual();
$pdo = db();

// ============================================================
//  El NIVEL DE PERMISOS es el rol del usuario.
//  La columna usuarios.rol queda como campo legado (la usa la
//  vista v_novedades_completas); acá se mantiene sincronizada:
//  'admin' si el nivel tiene es_admin=1, 'docente' en el resto.
// ============================================================

// ── Niveles de permiso disponibles (null si faltan migraciones) ──
try {
    $niveles = $pdo->query(
        'SELECT id_nivel, nombre, es_admin FROM niveles_permiso ORDER BY es_admin DESC, nombre ASC'
    )->fetchAll();
    $hay_columna_nivel = (bool)$pdo->query(
        "SELECT COUNT(*) FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios' AND COLUMN_NAME = 'id_nivel'"
    )->fetchColumn();
} catch (Throwable $e) {
    $niveles = null;
    $hay_columna_nivel = false;
}
$niveles_ok = $niveles !== null && $hay_columna_nivel;

/** Fila del nivel a partir de su id (dentro de $niveles) */
function nivel_por_id(?array $niveles, int $id): ?array {
    foreach ($niveles ?? [] as $n) {
        if ((int)$n['id_nivel'] === $id) return $n;
    }
    return null;
}

/** Rol legado que corresponde a un nivel (mantiene viva la columna usuarios.rol) */
function rol_legado(?array $nivel): string {
    return ($nivel && (int)$nivel['es_admin'] === 1) ? 'admin' : 'docente';
}

// ── Cambiar el nivel de un usuario existente ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'cambiar_nivel' && $niveles_ok) {
    csrf_check();
    $id    = (int)($_POST['id'] ?? 0);
    $nivel = (int)($_POST['id_nivel'] ?? 0);
    $filaNivel = nivel_por_id($niveles, $nivel);

    if ($id === (int)$u['id_usuario']) {
        flash('error', 'No podés cambiar tu propio nivel de permisos (evita que te quedes sin acceso).');
    } elseif ($nivel !== 0 && $filaNivel === null) {
        flash('error', 'El nivel de permisos seleccionado no existe.');
    } else {
        $pdo->prepare('UPDATE usuarios SET id_nivel = ?, rol = ? WHERE id_usuario = ?')
            ->execute([$nivel > 0 ? $nivel : null, rol_legado($filaNivel), $id]);
        flash('ok', 'Nivel de permisos actualizado.');
    }
    header('Location: gestion_usuarios.php');
    exit;
}

// ── Borrado ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'borrar') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    if ($id === (int)$u['id_usuario']) {
        flash('error', 'No podés eliminar tu propio usuario.');
    } else {
        // ¿Tiene contenido asociado? (FK sin ON DELETE) — avisamos en vez de romper
        $tieneNov = (int)$pdo->query('SELECT COUNT(*) FROM novedades WHERE id_usuario=' . $id)->fetchColumn();
        $tieneEvt = 0;
        try { $tieneEvt = (int)$pdo->query('SELECT COUNT(*) FROM agenda WHERE id_usuario=' . $id)->fetchColumn(); } catch (Throwable $e) {}
        if ($tieneNov > 0 || $tieneEvt > 0) {
            flash('error', 'No se puede eliminar: el usuario tiene novedades o eventos asociados. Reasignalos o eliminá ese contenido primero.');
        } else {
            $pdo->prepare('DELETE FROM usuarios WHERE id_usuario = ?')->execute([$id]);
            flash('ok', 'Usuario eliminado correctamente.');
        }
    }
    header('Location: gestion_usuarios.php');
    exit;
}

// ── Alta de usuario ──
$errores = [];
$val = ['nombre' => '', 'apellido' => '', 'email' => '', 'telefono' => '', 'id_nivel' => 0];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'crear') {
    csrf_check();
    $val['nombre']   = trim((string)($_POST['nombre'] ?? ''));
    $val['apellido'] = trim((string)($_POST['apellido'] ?? ''));
    $val['email']    = trim((string)($_POST['email'] ?? ''));
    $val['telefono'] = trim((string)($_POST['telefono'] ?? ''));
    $val['id_nivel'] = (int)($_POST['id_nivel'] ?? 0);
    $pass  = (string)($_POST['password'] ?? '');
    $pass2 = (string)($_POST['password2'] ?? '');

    if ($val['nombre'] === '')   $errores[] = 'El nombre es obligatorio.';
    if ($val['apellido'] === '') $errores[] = 'El apellido es obligatorio.';
    if (!filter_var($val['email'], FILTER_VALIDATE_EMAIL)) $errores[] = 'El email no es válido.';
    if (!$niveles_ok) {
        $errores[] = 'Falta ejecutar las migraciones de permisos (migracion_permisos.sql, migracion_permisos_usuarios.sql y migracion_unificar_roles.sql).';
    } elseif ($val['id_nivel'] <= 0 || nivel_por_id($niveles, $val['id_nivel']) === null) {
        $errores[] = 'Seleccioná el nivel de permisos del usuario.';
    }
    if (mb_strlen($pass) < 8) $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    if ($pass !== $pass2)     $errores[] = 'Las contraseñas no coinciden.';

    // Email único
    if (!$errores) {
        $ex = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE email = ?');
        $ex->execute([$val['email']]);
        if ((int)$ex->fetchColumn() > 0) $errores[] = 'Ya existe un usuario con ese email.';
    }

    if (!$errores) {
        $hash      = password_hash($pass, PASSWORD_BCRYPT);
        $filaNivel = nivel_por_id($niveles, $val['id_nivel']);
        $ins = $pdo->prepare(
            'INSERT INTO usuarios (nombre, apellido, rol, id_nivel, email, telefono, password)
             VALUES (:n, :a, :r, :nv, :e, :t, :p)'
        );
        $ins->execute([
            ':n' => $val['nombre'], ':a' => $val['apellido'],
            ':r' => rol_legado($filaNivel), ':nv' => $val['id_nivel'],
            ':e' => $val['email'], ':t' => ($val['telefono'] !== '' ? $val['telefono'] : null),
            ':p' => $hash,
        ]);
        $nuevoId = (int)$pdo->lastInsertId();
        flash('ok', "Usuario creado correctamente. ID asignado: #$nuevoId");
        header('Location: gestion_usuarios.php');
        exit;
    }
}

// ── Listado ──
if ($niveles_ok) {
    $lista = $pdo->query(
        'SELECT u.id_usuario, u.nombre, u.apellido, u.id_nivel, u.email, u.telefono, u.created_at,
                n.nombre AS nivel_nombre, n.es_admin
           FROM usuarios u
           LEFT JOIN niveles_permiso n ON n.id_nivel = u.id_nivel
          ORDER BY u.id_usuario ASC'
    )->fetchAll();
} else {
    $lista = $pdo->query(
        'SELECT id_usuario, nombre, apellido, rol, email, telefono, created_at
         FROM usuarios ORDER BY id_usuario ASC'
    )->fetchAll();
}

$panel_page_title = 'Gestión de Usuarios';
$panel_heading    = 'Gestión de <span>Usuarios</span>';
$panel_sub        = 'Alta y administración de usuarios. El nivel de permisos define qué puede hacer cada uno.';
require __DIR__ . '/panel_header.php';
?>

    <div class="panel-toolbar">
      <a href="panel.php" class="back-link">
        <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg> Volver al panel
      </a>
    </div>

    <?php if (!$niveles_ok): ?>
      <div class="alert alert-info">
        Para dar de alta usuarios ejecutá primero las migraciones de permisos en phpMyAdmin:
        <strong>migracion_permisos.sql</strong>, <strong>migracion_permisos_usuarios.sql</strong> y
        <strong>migracion_unificar_roles.sql</strong>.
      </div>
    <?php endif; ?>

    <div class="form-card" style="margin-bottom:2.5rem;">
      <h2 style="font-family:var(--font-display);color:var(--blue-dark);font-size:1.35rem;margin-bottom:.4rem;">
        Nuevo usuario
      </h2>
      <p style="color:#667;font-size:.86rem;margin-bottom:1.3rem;">
        El <strong>ID de usuario</strong> lo asigna el sistema automáticamente. El
        <strong>nivel de permisos</strong> es el rol del usuario: define a qué secciones
        del panel accede (se administran desde
        <a href="gestion_permisos.php" style="color:var(--blue-mid);font-weight:700;">Permisos</a>).
      </p>

      <?php if ($errores): ?>
        <div class="alert alert-error"><?= implode('<br>', array_map('e', $errores)) ?></div>
      <?php endif; ?>

      <form method="post" action="gestion_usuarios.php" autocomplete="off">
        <?= csrf_input() ?>
        <input type="hidden" name="accion" value="crear">

        <div class="field-row">
          <div class="field">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" maxlength="100" required value="<?= e($val['nombre']) ?>">
          </div>
          <div class="field">
            <label for="apellido">Apellido</label>
            <input type="text" id="apellido" name="apellido" maxlength="100" required value="<?= e($val['apellido']) ?>">
          </div>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="email">Email <span class="hint">(se usa para iniciar sesión)</span></label>
            <input type="email" id="email" name="email" maxlength="180" required value="<?= e($val['email']) ?>"
                   placeholder="usuario@juanxxiii.edu.ar">
          </div>
          <div class="field">
            <label for="telefono">Teléfono <span class="hint">(opcional)</span></label>
            <input type="text" id="telefono" name="telefono" maxlength="30" value="<?= e($val['telefono']) ?>">
          </div>
        </div>

        <div class="field">
          <label for="id_nivel">Nivel de permisos (rol)</label>
          <?php if ($niveles_ok): ?>
            <select id="id_nivel" name="id_nivel" required>
              <option value="" disabled <?= $val['id_nivel'] === 0 ? 'selected' : '' ?>>— Elegí un nivel —</option>
              <?php foreach ($niveles as $nv): ?>
                <option value="<?= (int)$nv['id_nivel'] ?>" <?= $val['id_nivel'] === (int)$nv['id_nivel'] ? 'selected' : '' ?>>
                  <?= e($nv['nombre']) ?><?= (int)$nv['es_admin'] === 1 ? ' — acceso total' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          <?php else: ?>
            <select disabled><option>Ejecutá las migraciones de permisos</option></select>
          <?php endif; ?>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="password">Contraseña <span class="hint">(mín. 8 caracteres)</span></label>
            <input type="password" id="password" name="password" required minlength="8">
          </div>
          <div class="field">
            <label for="password2">Repetir contraseña</label>
            <input type="password" id="password2" name="password2" required minlength="8">
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary" <?= $niveles_ok ? '' : 'disabled' ?>>Crear usuario</button>
        </div>
      </form>
    </div>

    <h2 style="font-family:var(--font-display);color:var(--blue-dark);font-size:1.25rem;margin-bottom:1rem;">
      Usuarios registrados
    </h2>

    <div style="overflow-x:auto;">
    <table class="mng-table">
      <thead>
        <tr><th>ID</th><th>Nombre</th><th>Nivel de permisos</th><th>Email</th><th>Teléfono</th><th style="text-align:right;">Acciones</th></tr>
      </thead>
      <tbody>
      <?php foreach ($lista as $row): $esYo = (int)$row['id_usuario'] === (int)$u['id_usuario']; ?>
        <tr>
          <td style="font-weight:700;color:var(--blue-dark);">#<?= (int)$row['id_usuario'] ?></td>
          <td><?= e($row['nombre'] . ' ' . $row['apellido']) ?><?= $esYo ? ' <span style="font-size:.72rem;color:var(--gray-500);">(vos)</span>' : '' ?></td>
          <td>
            <?php if (!$niveles_ok): ?>
              <span class="mng-badge" style="background:#6d6875;"><?= e(rol_label($row['rol'])) ?></span>
            <?php elseif ($esYo): ?>
              <span class="mng-badge" style="background:<?= !empty($row['es_admin']) ? '#E63946' : '#457B9D' ?>;">
                <?= e($row['nivel_nombre'] ?? 'Sin nivel') ?>
              </span>
            <?php else: ?>
              <form method="post" action="gestion_usuarios.php" style="display:inline-flex;align-items:center;gap:.4rem;">
                <?= csrf_input() ?>
                <input type="hidden" name="accion" value="cambiar_nivel">
                <input type="hidden" name="id" value="<?= (int)$row['id_usuario'] ?>">
                <select name="id_nivel" onchange="this.form.submit()" title="Cambiar nivel de permisos"
                        style="font-family:var(--font-body);font-size:.8rem;padding:.3rem .4rem;border:1.5px solid #dfe3e8;border-radius:8px;color:var(--text);max-width:190px;">
                  <option value="0" <?= empty($row['id_nivel']) ? 'selected' : '' ?>>Sin nivel</option>
                  <?php foreach ($niveles as $nv): ?>
                    <option value="<?= (int)$nv['id_nivel'] ?>" <?= (int)($row['id_nivel'] ?? 0) === (int)$nv['id_nivel'] ? 'selected' : '' ?>>
                      <?= e($nv['nombre']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <?php if (!empty($row['es_admin'])): ?>
                  <span class="mng-badge" style="background:#E63946;">Admin</span>
                <?php endif; ?>
              </form>
            <?php endif; ?>
          </td>
          <td style="color:#556;"><?= e($row['email']) ?></td>
          <td style="color:#667;"><?= e($row['telefono'] ?? '—') ?></td>
          <td>
            <div class="mng-actions" style="justify-content:flex-end;">
              <?php if ($esYo): ?>
                <span style="font-size:.75rem;color:var(--gray-500);align-self:center;">Vos</span>
              <?php else: ?>
                <form method="post" action="gestion_usuarios.php" style="display:inline;"
                      onsubmit="return confirm('¿Eliminar al usuario <?= e(addslashes($row['nombre'].' '.$row['apellido'])) ?>?');">
                  <?= csrf_input() ?>
                  <input type="hidden" name="accion" value="borrar">
                  <input type="hidden" name="id" value="<?= (int)$row['id_usuario'] ?>">
                  <button type="submit" class="icon-btn danger" title="Eliminar usuario">
                    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  </button>
                </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>

  </div>
</body>
</html>
