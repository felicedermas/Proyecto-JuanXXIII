-- ============================================================
--  migracion_inscripciones.sql
--  Colegio Parroquial Juan XXIII
--  Módulo de inscripciones en línea.
--
--  NOTA: las tablas se crean solas la primera vez que se abre un
--  formulario o el panel (partials/inscripciones.php). Este script
--  queda para producción, si el usuario de MySQL no tiene permiso
--  de CREATE. Se puede correr más de una vez sin problema.
-- ============================================================

-- Qué formularios están habilitados en el sitio
CREATE TABLE IF NOT EXISTS inscripcion_formularios (
  clave        VARCHAR(20)  NOT NULL PRIMARY KEY,
  habilitado   TINYINT(1)   NOT NULL DEFAULT 0,
  id_usuario   INT UNSIGNED NULL COMMENT 'Último usuario que lo cambió',
  actualizado  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO inscripcion_formularios (clave, habilitado) VALUES
  ('jardin', 0), ('primaria', 0), ('sec', 0), ('hermanos', 0);

-- Un registro por alumno/a. En "hermanos" cada hijo/a es un registro
-- distinto (con su nivel, modalidad y año) y comparten el mismo `grupo`.
CREATE TABLE IF NOT EXISTS inscripciones (
  id_inscripcion  INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  formulario      VARCHAR(20)  NOT NULL,
  grupo           CHAR(10)     NOT NULL COMMENT 'Código de envío; lo comparten los hermanos',
  nivel           ENUM('Inicial','Primario','Secundario') NOT NULL,
  modalidad       VARCHAR(20)  NOT NULL DEFAULT 'General',
  anio            VARCHAR(20)  NOT NULL,
  anio_orden      TINYINT UNSIGNED NOT NULL,
  alumno_nombre   VARCHAR(120) NOT NULL,
  alumno_apellido VARCHAR(120) NOT NULL,
  alumno_dni      VARCHAR(12)  NOT NULL,
  alumno_fnac     DATE         NULL,
  tutor_nombre    VARCHAR(160) NOT NULL,
  tutor_email     VARCHAR(160) NOT NULL,
  tutor_tel       VARCHAR(40)  NOT NULL,
  datos           LONGTEXT     NOT NULL COMMENT 'JSON con todo lo enviado, por sección',
  estado          ENUM('Nueva','En revisión','Contactada','Aceptada','Descartada') NOT NULL DEFAULT 'Nueva',
  notas           TEXT         NULL,
  ip              VARCHAR(45)  NOT NULL DEFAULT '',
  creado_en       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_sector (nivel, modalidad, anio_orden),
  KEY idx_estado (estado),
  KEY idx_grupo  (grupo),
  KEY idx_fecha  (creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
