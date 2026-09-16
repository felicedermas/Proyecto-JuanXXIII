<?php
require_once __DIR__ . '/panel_config.php';

// Si ya está logueado, al panel directo
if (esta_logueado()) {
    header('Location: panel.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim((string)($_POST['email'] ?? ''));
    $pass  = (string)($_POST['password'] ?? '');

    if ($email === '' || $pass === '') {
        $error = 'Completá el email y la contraseña.';
    } else {
        $stmt = db()->prepare(
            'SELECT id_usuario, nombre, apellido, rol, email, password
             FROM usuarios WHERE email = :email LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $u = $stmt->fetch();

        if ($u && password_verify($pass, $u['password'])) {
            // Regenerar ID de sesión para evitar fijación
            session_regenerate_id(true);
            $_SESSION['usuario'] = [
                'id_usuario' => (int)$u['id_usuario'],
                'nombre'     => $u['nombre'],
                'apellido'   => $u['apellido'],
                'rol'        => $u['rol'],
                'email'      => $u['email'],
            ];
            header('Location: panel.php');
            exit;
        }
        $error = 'Email o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Acceso al Panel — Colegio Parroquial Juan XXIII</title>
<?php require __DIR__ . '/partials/favicon.php'; ?>
  <link rel="stylesheet" href="styles.css"/>
  <link rel="stylesheet" href="panel.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet"/>
</head>
<body>
  <div class="login-shell">
    <div class="login-box">
      <div class="login-brand">
        <img src="img/logo.png" alt="Logo del Colegio"/>
        <h1>Panel de Control</h1>
        <p>Colegio Parroquial Juan XXIII</p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="post" action="login.php" autocomplete="off">
        <?= csrf_input() ?>
        <div class="field">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required autofocus
                 placeholder="usuario@juanxxiii.edu.ar"
                 value="<?= e($_POST['email'] ?? '') ?>"/>
        </div>
        <div class="field">
          <label for="password">Contraseña</label>
          <input type="password" id="password" name="password" required placeholder="••••••••"/>
        </div>
        <div class="form-actions" style="border:none;padding-top:.4rem;margin-top:.6rem;">
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Ingresar</button>
        </div>
      </form>

      <p style="text-align:center;margin-top:1.4rem;font-size:.82rem;">
        <a href="index.php" class="back-link" style="justify-content:center;">
          <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
          Volver al sitio
        </a>
      </p>
    </div>
  </div>
</body>
</html>
