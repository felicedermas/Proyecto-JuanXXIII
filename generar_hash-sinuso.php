<?php
// ============================================================
//  generar_hash.php  —  UTILIDAD OPCIONAL
//  Colegio Parroquial Juan XXIII
// ============================================================
//  Sirve para generar el hash de una contraseña y pegarlo
//  manualmente en la base (columna usuarios.password) si querés
//  crear el primer admin sin usar el panel.
//
//  USO:  abrí en el navegador  http://localhost/JUAN23/generar_hash.php?pass=TuClave
//
//  ⚠️ IMPORTANTE: borrá este archivo del servidor cuando termines,
//  no debe quedar accesible en producción.
// ============================================================

header('Content-Type: text/plain; charset=utf-8');

$pass = $_GET['pass'] ?? '';
if ($pass === '') {
    echo "Pasá una contraseña por la URL, por ejemplo:\n";
    echo "  generar_hash.php?pass=MiClaveSegura123\n";
    exit;
}

$hash = password_hash($pass, PASSWORD_BCRYPT);
echo "Contraseña : $pass\n";
echo "Hash bcrypt: $hash\n\n";
echo "SQL de ejemplo para crear un admin:\n";
echo "INSERT INTO usuarios (nombre, apellido, rol, email, password)\n";
echo "VALUES ('Admin', 'Sistema', 'admin', 'admin@juanxxiii.edu.ar', '$hash');\n";
