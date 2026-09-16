-- ============================================================
--  migracion_instagram.sql
--  Colegio Parroquial Juan XXIII
--  Sincronización automática con varias cuentas de Instagram:
--  cada cuenta publica en su propia etiqueta de novedades.
--  Ejecutar UNA vez sobre la base colegio_juan_xxiii.
-- ============================================================

USE `colegio_juan_xxiii`;

-- ============================================================
--  1) Nueva etiqueta "Secundario"
-- ============================================================
-- Antes las etiquetas eran: Inicial, Primario, Técnica, Orientada, Global.
-- Se agrega Secundario para la cuenta de Instagram de la secundaria.
-- Técnica y Orientada se mantienen para lo que se carga a mano.

ALTER TABLE `novedades`
  MODIFY `etiqueta` ENUM('Inicial','Primario','Secundario','Técnica','Orientada','Global')
  NOT NULL DEFAULT 'Global';

ALTER TABLE `agenda`
  MODIFY `etiqueta` ENUM('Inicial','Primario','Secundario','Técnica','Orientada','Global')
  NOT NULL DEFAULT 'Global';


-- ============================================================
--  2) Cuentas de Instagram
-- ============================================================
CREATE TABLE IF NOT EXISTS `ig_cuentas` (
  `id_cuenta`      INT AUTO_INCREMENT PRIMARY KEY,
  `nombre`         VARCHAR(80)  NOT NULL COMMENT 'Cómo la llamamos: "Nivel Inicial"',
  `usuario_ig`     VARCHAR(60)  NOT NULL DEFAULT '' COMMENT 'Arroba, solo informativo: @juanxxiii.inicial',
  `ig_user_id`     VARCHAR(40)  NOT NULL DEFAULT '' COMMENT 'ID numérico que devuelve Meta',
  `access_token`   TEXT         NOT NULL COMMENT 'Token de larga duración',
  `token_expira`   DATE         NULL     COMMENT 'Los tokens duran 60 días; se renuevan solos',
  `etiqueta`       ENUM('Inicial','Primario','Secundario','Técnica','Orientada','Global')
                                NOT NULL DEFAULT 'Global'
                                COMMENT 'Etiqueta con la que entran las novedades de esta cuenta',
  `id_usuario`     INT          NOT NULL COMMENT 'Usuario del panel que figura como autor',
  `activa`         TINYINT(1)   NOT NULL DEFAULT 1,
  `limite_posts`   INT          NOT NULL DEFAULT 10 COMMENT 'Cuántos posts mirar en cada sincronización',
  `importar_desde` DATE         NULL COMMENT 'No importar publicaciones anteriores a esta fecha',
  `ultima_sync`    DATETIME     NULL,
  `ultimo_estado`  ENUM('','ok','error') NOT NULL DEFAULT '',
  `ultimo_mensaje` VARCHAR(400) NOT NULL DEFAULT '',
  `creado_en`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_ig_usuario` (`usuario_ig`),
  KEY `idx_ig_activa` (`activa`),
  CONSTRAINT `fk_ig_autor` FOREIGN KEY (`id_usuario`)
    REFERENCES `usuarios`(`id_usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Cuentas de Instagram que alimentan automáticamente las novedades';


-- ============================================================
--  3) Saber de qué cuenta vino cada novedad
-- ============================================================
ALTER TABLE `novedades`
  ADD COLUMN IF NOT EXISTS `id_cuenta_ig` INT NULL AFTER `ig_permalink`;

-- El ig_post_id tiene que ser único: es lo que evita importar dos veces
-- el mismo post. (Si el ALTER falla porque ya hay duplicados, limpialos
-- primero con el SELECT del final de este archivo.)
ALTER TABLE `novedades`
  ADD UNIQUE KEY IF NOT EXISTS `uk_ig_post` (`ig_post_id`);


-- ============================================================
--  4) Historial de sincronizaciones
-- ============================================================
CREATE TABLE IF NOT EXISTS `ig_sync_log` (
  `id_log`       INT AUTO_INCREMENT PRIMARY KEY,
  `id_cuenta`    INT          NULL,
  `ejecutado_en` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `origen`       ENUM('tarea','manual','cli') NOT NULL DEFAULT 'tarea',
  `importadas`   INT          NOT NULL DEFAULT 0,
  `omitidas`     INT          NOT NULL DEFAULT 0,
  `errores`      INT          NOT NULL DEFAULT 0,
  `detalle`      TEXT         NULL,
  KEY `idx_log_fecha` (`ejecutado_en`),
  KEY `idx_log_cuenta` (`id_cuenta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Qué pasó en cada corrida de la sincronización';


-- ============================================================
--  5) Opciones de la sincronización (van a `configuracion`)
-- ============================================================
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `etiqueta`, `ayuda`, `tipo`, `grupo`, `orden`) VALUES
('ig_excluir',    '#nolaweb', 'Palabra para NO publicar en la web',
 'Si una publicación de Instagram contiene este texto, no se importa al sitio. Poné algo que no uses nunca por error.',
 'texto', 'Instagram', 10),
('ig_simular',    '1',        'Modo de prueba',
 '1 = usa posts_simulados.json para probar sin cuentas reales. 0 = consulta Instagram de verdad. Pasalo a 0 cuando tengas los tokens cargados.',
 'texto', 'Instagram', 20),
('ig_clave_tarea', '',        'Clave de la tarea programada',
 'Se genera sola la primera vez. Es lo que permite que la tarea automática de las 19:00 corra sin estar logueado. No la compartas.',
 'texto', 'Instagram', 30);


-- ============================================================
--  6) Cuentas de ejemplo — EDITALAS con tus datos reales
-- ============================================================
-- id_usuario 5 es el usuario "Instagram Colegio" que ya trae el dump.
-- Verificá el número con:
--    SELECT id_usuario, nombre, apellido FROM usuarios;
-- El access_token se carga después desde el panel (Instagram → Editar).

INSERT IGNORE INTO `ig_cuentas`
  (`nombre`, `usuario_ig`, `access_token`, `etiqueta`, `id_usuario`, `activa`, `importar_desde`) VALUES
  ('Nivel Inicial',    '@juanxxiii.inicial',    '', 'Inicial',    5, 1, CURDATE()),
  ('Nivel Primario',   '@juanxxiii.primaria',   '', 'Primario',   5, 1, CURDATE()),
  ('Nivel Secundario', '@juanxxiii.secundaria', '', 'Secundario', 5, 1, CURDATE());


-- ============================================================
--  Comprobaciones
-- ============================================================
SELECT `id_cuenta`, `nombre`, `usuario_ig`, `etiqueta`, `activa` FROM `ig_cuentas`;

-- Si el índice único de ig_post_id falló, estos son los duplicados:
-- SELECT ig_post_id, COUNT(*) FROM novedades
--  WHERE ig_post_id IS NOT NULL GROUP BY ig_post_id HAVING COUNT(*) > 1;
