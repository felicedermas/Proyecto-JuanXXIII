-- ============================================================
--  arreglo_imagenes_novedades.sql
--  Recrea la tabla `imagenes_novedades`, que quedó dañada
--  (el motor InnoDB perdió su tablespace: "Table ... doesn't
--  exist in engine"). No había datos accesibles que perder;
--  se restauran los mismos registros que traía el dump original.
--  Ejecutar UNA vez en phpMyAdmin, base colegio_juan_xxiii.
-- ============================================================

USE `colegio_juan_xxiii`;

DROP TABLE IF EXISTS `imagenes_novedades`;

CREATE TABLE `imagenes_novedades` (
  `id_imagen`   INT(11)      NOT NULL AUTO_INCREMENT,
  `id_novedad`  INT(11)      NOT NULL,
  `url_imagen`  VARCHAR(500) NOT NULL,
  `orden`       TINYINT(4)   NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_imagen`),
  KEY `idx_imagenes_novedad` (`id_novedad`, `orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=12;

INSERT INTO `imagenes_novedades` (`id_imagen`, `id_novedad`, `url_imagen`, `orden`, `created_at`) VALUES
(1, 1, 'img/novedades/ciclo-lectivo-2026-01.png', 1, '2026-05-27 12:37:10'),
(2, 1, 'img/novedades/ciclo-lectivo-2026-02.png', 2, '2026-05-27 12:37:10'),
(3, 2, 'img/novedades/acto-bandera-01.png', 1, '2026-05-27 12:37:10'),
(4, 3, 'img/novedades/inscripcion-inicial-01.png', 1, '2026-05-27 12:37:10'),
(5, 3, 'img/novedades/inscripcion-inicial-02.png', 2, '2026-05-27 12:37:10'),
(6, 3, 'img/novedades/inscripcion-inicial-03.png', 3, '2026-05-27 12:37:10'),
(7, 6, 'img/novedades/instagram/ig_17900000000000001_1.jpg', 1, '2026-06-19 22:32:47'),
(8, 7, 'img/novedades/instagram/ig_17900000000000002_1.jpg', 1, '2026-06-19 22:32:50'),
(9, 7, 'img/novedades/instagram/ig_17900000000000002_2.jpg', 2, '2026-06-19 22:32:52'),
(10, 7, 'img/novedades/instagram/ig_17900000000000002_3.jpg', 3, '2026-06-19 22:32:54'),
(11, 8, 'img/novedades/instagram/ig_17900000000000003_1.jpg', 1, '2026-06-19 22:32:56');

ALTER TABLE `imagenes_novedades`
  ADD CONSTRAINT `fk_imagen_novedad` FOREIGN KEY (`id_novedad`)
  REFERENCES `novedades` (`id_novedad`) ON DELETE CASCADE ON UPDATE CASCADE;

-- ── Comprobación ──
CHECK TABLE `imagenes_novedades`;
SELECT COUNT(*) AS filas FROM `imagenes_novedades`;
