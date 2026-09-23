-- ============================================================
--  MIGRACIÓN — Sistema de Panel de Control
--  Colegio Parroquial Juan XXIII
-- ============================================================
--  Ejecutá este script UNA sola vez en phpMyAdmin
--  (base de datos: colegio_juan_xxiii).
--
--  Corrige la tabla `agenda` (le faltaba PRIMARY KEY y
--  AUTO_INCREMENT en el dump original) para que se puedan
--  insertar eventos desde el panel.
-- ============================================================

USE `colegio_juan_xxiii`;

-- ── 1) Clave primaria + auto-incremento en agenda ──
-- (Se envuelve en comprobaciones para que no falle si ya existe)

-- Agregar PRIMARY KEY solo si la tabla aún no la tiene
SET @pk_existe := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = 'colegio_juan_xxiii'
    AND TABLE_NAME   = 'agenda'
    AND CONSTRAINT_TYPE = 'PRIMARY KEY'
);
SET @sql := IF(@pk_existe = 0,
  'ALTER TABLE `agenda` ADD PRIMARY KEY (`id_evento`)',
  'SELECT "PK ya existe en agenda" AS info');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- Convertir id_evento en AUTO_INCREMENT (idempotente)
ALTER TABLE `agenda`
  MODIFY `id_evento` int(11) NOT NULL AUTO_INCREMENT;

-- Índices útiles (IF NOT EXISTS evita error si se corre dos veces)
ALTER TABLE `agenda` ADD KEY IF NOT EXISTS `idx_agenda_fecha` (`fecha_evento`);
ALTER TABLE `agenda` ADD KEY IF NOT EXISTS `idx_agenda_etiqueta` (`etiqueta`);
ALTER TABLE `agenda` ADD KEY IF NOT EXISTS `idx_agenda_usuario` (`id_usuario`);

-- ── 2) FK de agenda hacia usuarios (opcional pero recomendado) ──
SET @fk_existe := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = 'colegio_juan_xxiii'
    AND TABLE_NAME   = 'agenda'
    AND CONSTRAINT_NAME = 'fk_agenda_usuario'
);
SET @sql := IF(@fk_existe = 0,
  'ALTER TABLE `agenda` ADD CONSTRAINT `fk_agenda_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`) ON UPDATE CASCADE',
  'SELECT "FK agenda->usuarios ya existe" AS info');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ============================================================
--  Listo. La tabla `usuarios` ya existe con los campos:
--  id_usuario, nombre, apellido, rol(admin/directivo/docente/
--  secretaria), email, telefono, password (bcrypt), created_at.
--  No hace falta crearla de nuevo.
-- ============================================================
