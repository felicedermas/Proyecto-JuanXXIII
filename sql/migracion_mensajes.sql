-- ============================================================
--  migracion_mensajes.sql
--  Colegio Parroquial Juan XXIII
--  Mensajes del formulario de Contacto.
--
--  NOTA: la tabla y la columna de permiso se crean solas la primera
--  vez que alguien envía el formulario o se abre el panel
--  (partials/mensajes.php). Este script queda para producción, si el
--  usuario de MySQL no tiene permiso de CREATE / ALTER.
--  Se puede correr más de una vez sin problema (sintaxis de MariaDB).
-- ============================================================

CREATE TABLE IF NOT EXISTS mensajes_contacto (
  id_mensaje  INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(120) NOT NULL,
  email       VARCHAR(160) NOT NULL,
  telefono    VARCHAR(40)  NOT NULL DEFAULT '',
  asunto      ENUM('Consulta general','Inscripciones','Becas','Administración','Otro') NOT NULL DEFAULT 'Consulta general',
  nivel       ENUM('Inicial','Primario','Secundario','General') NOT NULL DEFAULT 'General',
  mensaje     TEXT         NOT NULL,
  estado      ENUM('Nuevo','Leído','Respondido','Archivado') NOT NULL DEFAULT 'Nuevo',
  destacado   TINYINT(1)   NOT NULL DEFAULT 0,
  notas       TEXT         NULL,
  id_usuario  INT UNSIGNED NULL COMMENT 'Último usuario que lo gestionó',
  ip          VARCHAR(45)  NOT NULL DEFAULT '',
  creado_en   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_estado (estado),
  KEY idx_asunto (asunto),
  KEY idx_fecha  (creado_en),
  KEY idx_ip     (ip, creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permiso "Mensajes de contacto" en los niveles de permiso
ALTER TABLE niveles_permiso
  ADD COLUMN IF NOT EXISTS mensajes TINYINT(1) NOT NULL DEFAULT 0;
