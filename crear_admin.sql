-- ============================================================
--  crear_admin.sql
--  Colegio Parroquial Juan XXIII
--  Crea un usuario administrador para entrar al panel.
--  Ejecutar en phpMyAdmin, base `colegio_juan_xxiii`.
-- ============================================================
--
--  Usuario:     felipe@juanxxiii.edu.ar
--  Contraseña:  Rio-ver-9308#
--
--  Cambiala apenas entres (Panel → Usuarios). El hash de abajo
--  es bcrypt cost 12: la contraseña en texto plano NO queda
--  guardada en ningún lado.
-- ============================================================

USE `colegio_juan_xxiii`;

-- ── 1) Crear el admin ───────────────────────────────────────
INSERT INTO `usuarios` (`nombre`, `apellido`, `rol`, `email`, `telefono`, `password`)
VALUES (
  'Felipe',
  'Admin',
  'admin',
  'felipe@juanxxiii.edu.ar',
  NULL,
  '$2y$12$kg8YdfHCOqJ7kk2Zvah0ouYMD3WxATwCn6tfpPPbF6gRQdBBjSurG'
)
ON DUPLICATE KEY UPDATE
  `password` = VALUES(`password`),
  `rol`      = 'admin';


-- ============================================================
--  2) IMPORTANTE — las 5 cuentas de ejemplo del dump
-- ============================================================
--  maria.gonzalez@…, carlos.rodriguez@…, laura.perez@…,
--  martin.lopez@… e instagram@… comparten un hash de ejemplo
--  muy conocido, cuya contraseña es literalmente:  secret
--
--  Dos de ellas (maria.gonzalez e instagram) son rol 'admin'.
--  Cualquiera que pruebe ese usuario con "secret" entra como
--  administrador. Descomentá UNA de las dos opciones:

-- Opción A — dejarlas sin poder iniciar sesión (recomendado
-- mientras siga siendo data de prueba). El hash '!' no coincide
-- con ninguna contraseña posible, así que quedan bloqueadas
-- pero no se rompe la relación con novedades/agenda.
--
-- UPDATE `usuarios` SET `password` = '!'
--  WHERE `email` IN (
--    'maria.gonzalez@juanxxiii.edu.ar',
--    'carlos.rodriguez@juanxxiii.edu.ar',
--    'laura.perez@juanxxiii.edu.ar',
--    'martin.lopez@juanxxiii.edu.ar',
--    'instagram@juanxxiii.edu.ar'
--  );

-- Opción B — bajarles el rol para que al menos no sean admin:
--
-- UPDATE `usuarios` SET `rol` = 'docente'
--  WHERE `email` IN ('maria.gonzalez@juanxxiii.edu.ar', 'instagram@juanxxiii.edu.ar');


-- ── 3) Comprobar que quedó bien ─────────────────────────────
SELECT `id_usuario`, `nombre`, `apellido`, `rol`, `email`
  FROM `usuarios`
 ORDER BY `id_usuario`;
