-- ============================================================
--  migracion_egresados.sql
--  Colegio Parroquial Juan XXIII
--  Historias de egresados desde el panel (Novedades → Historias de egresados).
--
--  El dump original creó `egresados` e `imagenes_egresados` sin clave
--  primaria ni AUTO_INCREMENT, así que no se podían cargar nuevas.
--
--  NOTA: el panel lo corrige solo la primera vez que se abre la pestaña
--  (partials/gestion_egresados.php). Este script queda para producción,
--  si el usuario de MySQL no tiene permiso de ALTER.
--  Ejecutalo UNA sola vez (MariaDB).
-- ============================================================

ALTER TABLE egresados
  MODIFY id_egresado INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  ADD PRIMARY KEY (id_egresado);

ALTER TABLE imagenes_egresados
  MODIFY id_imagen INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  ADD PRIMARY KEY (id_imagen),
  ADD KEY idx_egresado (id_egresado, orden);

-- Quién cargó cada historia (NULL = carga inicial: solo la edita un admin)
ALTER TABLE egresados
  ADD COLUMN IF NOT EXISTS id_usuario INT NULL COMMENT 'Quién la cargó desde el panel' AFTER publicado;
