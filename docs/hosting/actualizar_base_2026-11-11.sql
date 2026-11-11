-- ============================================================
--  Actualización de la base del hosting — 11/11/2026
--
--  Lleva a la base de InfinityFree los cambios que no viajan con
--  el código (porque están guardados en la base, no en archivos).
--
--  Cómo correrlo:
--    Panel de InfinityFree → MySQL Databases → phpMyAdmin
--    → elegir la base del colegio → pestaña "SQL"
--    → pegar la PARTE 1 → Continuar.
--    La PARTE 2 es aparte: leela antes de correrla.
--
--  Antes conviene exportar la base (pestaña "Exportar") como respaldo.
-- ============================================================

SET NAMES utf8mb4;

-- ------------------------------------------------------------
--  PARTE 1 — Datos de contacto, mapa y redes
--  (lo mismo que se edita en Panel → Datos de contacto)
-- ------------------------------------------------------------
UPDATE configuracion SET valor = '9 de Julio 151'              WHERE clave = 'direccion';
UPDATE configuracion SET valor = 'Ramos Mejía, Buenos Aires'   WHERE clave = 'localidad';
UPDATE configuracion SET valor = 'B1704'                       WHERE clave = 'codigo_postal';
UPDATE configuracion SET valor = '011 4658-6522'               WHERE clave = 'telefono';
UPDATE configuracion SET valor = '+541146586522'               WHERE clave = 'telefono_link';
UPDATE configuracion SET valor = ''                            WHERE clave IN ('email', 'email_admisiones');
UPDATE configuracion SET valor = 'A confirmar'                 WHERE clave IN ('horario_atencion', 'horario_admision');
UPDATE configuracion SET valor = 'https://maps.google.com/maps?q=9%20de%20Julio%20151%2C%20Ramos%20Mej%C3%ADa%2C%20Buenos%20Aires&z=16&output=embed'
                                                               WHERE clave = 'mapa_embed';
UPDATE configuracion SET valor = 'https://www.google.com/maps/search/?api=1&query=9%20de%20Julio%20151%2C%20Ramos%20Mej%C3%ADa%2C%20Buenos%20Aires'
                                                               WHERE clave = 'mapa_link';
UPDATE configuracion SET valor = 'https://www.instagram.com/cpjuan23/', etiqueta = 'Instagram · Secundaria'
                                                               WHERE clave = 'instagram';

-- Las dos cuentas nuevas de Instagram (Primaria y Jardín) para que
-- aparezcan y se puedan editar en Panel → Datos de contacto.
INSERT INTO configuracion (clave, valor, etiqueta, ayuda, tipo, grupo, orden) VALUES
  ('instagram_primario', 'https://www.instagram.com/parroquialjuan23',
   'Instagram · Primaria', 'URL completa del perfil. Vacío = no se muestra.', 'url', 'Redes', 22),
  ('instagram_inicial',  'https://www.instagram.com/jardinparroquialjuan23/',
   'Instagram · Jardín',   'URL completa del perfil. Vacío = no se muestra.', 'url', 'Redes', 24)
ON DUPLICATE KEY UPDATE valor = VALUES(valor);


-- ------------------------------------------------------------
--  PARTE 2 — Borrar el contenido de ejemplo cargado desde el panel
--
--  ATENCIÓN: borra TODAS las novedades, eventos de agenda, egresados
--  e inscripciones recibidas que haya en el hosting. Corrélo solo si
--  en el hosting no cargaron todavía contenido propio que quieran
--  conservar. No se puede deshacer (salvo con el respaldo).
--  El recorrido 360° y los usuarios NO se tocan.
-- ------------------------------------------------------------
-- START TRANSACTION;
-- DELETE FROM imagenes_novedades;
-- DELETE FROM novedades;
-- DELETE FROM agenda;
-- DELETE FROM imagenes_egresados;
-- DELETE FROM egresados;
-- DELETE FROM inscripciones;
-- COMMIT;
--
-- (Para correrla, borrá los "-- " del comienzo de esas líneas.)
