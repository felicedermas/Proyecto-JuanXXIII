-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 24, 2026 at 04:48 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `colegio_juan_xxiii`
--

-- --------------------------------------------------------

--
-- Table structure for table `agenda`
--

CREATE TABLE `agenda` (
  `id_evento` int(11) NOT NULL,
  `titulo` varchar(180) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `etiqueta` enum('Inicial','Primario','Técnica','Orientada','Global') NOT NULL DEFAULT 'Global',
  `tipo` enum('Acto','Formulario','Reunión','Examen','Feriado','Inscripción','Otro') NOT NULL DEFAULT 'Otro',
  `fecha_evento` date NOT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `lugar` varchar(180) DEFAULT NULL,
  `enlace` varchar(255) DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `agenda`
--

INSERT INTO `agenda` (`id_evento`, `titulo`, `descripcion`, `etiqueta`, `tipo`, `fecha_evento`, `hora_inicio`, `hora_fin`, `lugar`, `enlace`, `id_usuario`, `created_at`) VALUES
(1, 'Acto del Día de la Bandera', 'Acto conmemorativo abierto a toda la comunidad educativa. Se solicita puntualidad.', 'Global', 'Acto', '2026-06-20', '09:00:00', '10:30:00', 'Patio central', NULL, 1, '2026-06-03 13:57:31'),
(2, 'Inscripción Sala de 4 — 2027', 'Apertura del formulario de preinscripción para Nivel Inicial, ciclo lectivo 2027.', 'Inicial', 'Formulario', '2026-06-25', NULL, NULL, NULL, 'https://forms.gle/ejemplo', 1, '2026-06-03 13:57:31'),
(3, 'Reunión de padres — 6° grado', 'Reunión informativa sobre el cierre del ciclo primario y el pase a secundaria.', 'Primario', 'Reunión', '2026-07-02', '18:30:00', '20:00:00', 'Salón de actos', NULL, 1, '2026-06-03 13:57:31'),
(4, 'Examen de ingreso — Sec. Técnica', 'Evaluación diagnóstica para aspirantes a 1° año de la modalidad técnica.', 'Técnica', 'Examen', '2026-07-10', '08:00:00', '12:00:00', 'Aulas 1° piso', NULL, 1, '2026-06-03 13:57:31');

-- --------------------------------------------------------

--
-- Table structure for table `egresados`
--

CREATE TABLE `egresados` (
  `id_egresado` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `apellido` varchar(80) NOT NULL,
  `anio_egreso` year(4) NOT NULL,
  `orientacion` enum('Bachillerato Orientado','Técnica') NOT NULL DEFAULT 'Bachillerato Orientado',
  `subtitulo` varchar(220) NOT NULL COMMENT 'Ej.: Médica · Universidad Austral',
  `resumen` text NOT NULL COMMENT 'Párrafo breve para la grilla (aprox. 300 caracteres)',
  `cuerpo` longtext NOT NULL COMMENT 'Texto completo de la publicación (HTML o plain text)',
  `foto_perfil` varchar(300) DEFAULT NULL COMMENT 'Ruta relativa, ej.: uploads/egresados/lucia-gomez.jpg',
  `publicado` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = visible, 0 = borrador',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Publicaciones del estilo "Conocemos a …" sobre ex-alumnos';

--
-- Dumping data for table `egresados`
--

INSERT INTO `egresados` (`id_egresado`, `nombre`, `apellido`, `anio_egreso`, `orientacion`, `subtitulo`, `resumen`, `cuerpo`, `foto_perfil`, `publicado`, `created_at`, `updated_at`) VALUES
(1, 'Lucía', 'Gómez', '2010', 'Bachillerato Orientado', 'Médica cirujana · Hospital Universitario Austral', 'Lucía egresó en 2010 con el promedio más alto de su promoción. Hoy es cirujana en el Hospital Austral y docente de la UBA, donde impulsa programas de salud comunitaria en barrios vulnerables.', '<p>Cuando Lucía Gómez cruzó el portón del Juan XXIII por última vez como alumna, no imaginaba que diez años después estaría operando en uno de los quirófanos más modernos del país. \"El colegio me enseñó a no rendirme\", dice desde su guardia en el Hospital Austral.</p><p>Estudió Medicina en la UBA, se especializó en Cirugía General y hoy combina la práctica clínica con la docencia universitaria. En 2023 lanzó un programa de salud preventiva en la Villa 21-24 que ya atendió a más de 800 familias.</p><p>Cada tanto vuelve al colegio a charlar con los chicos de quinto año: \"Quiero que sepan que de acá se puede llegar a cualquier lado\".</p>', NULL, 1, '2026-06-03 13:40:20', '2026-06-03 13:40:20'),
(2, 'Matías', 'Ferreyra', '2015', 'Técnica', 'Ingeniero en Sistemas · Fundador de StartupAR', 'Matías terminó la técnica con orientación en informática y cuatro años después ya había fundado su propia empresa de software con clientes en tres países.', '<p>Matías Ferreyra recuerda las horas de taller en el Juan XXIII como el momento en que todo hizo clic. \"Acá aprendí a depurar un programa a las dos de la mañana y a no desesperarme\", ríe.</p><p>Estudió Ingeniería en Sistemas en la UTNBA y en 2019, con 22 años, co-fundó StartupAR, una plataforma de logística inteligente para pymes. La empresa factura más de un millón de dólares anuales y emplea a 18 personas, varios de ellos también ex-alumnos del colegio.</p><p>\"El título técnico me abrió puertas que un bachillerato solo no hubiera podido\", asegura.</p>', NULL, 1, '2026-06-03 13:40:20', '2026-06-03 13:40:20'),
(3, 'Valentina', 'Ríos', '2018', 'Bachillerato Orientado', 'Arquitecta · Estudio VR Arquitectura', 'Val egresó con un proyecto de arte que ganó el primer premio municipal. Hoy dirige su propio estudio de arquitectura y ganó un concurso internacional de diseño sustentable.', '<p>Valentina Ríos siempre dibujó en los márgenes de los cuadernos. Sus profesores del Juan XXIII no lo consideraban una distracción; lo consideraban un talento. \"Me animaron a presentar mis bocetos en el concurso municipal de arte. Gané. Desde ese día supe que iba a ser arquitecta\".</p><p>Egresada de la FADU-UBA con medalla de honor, fundó <em>Estudio VR Arquitectura</em> en 2023. Su proyecto de viviendas modulares de bajo costo fue seleccionado en el concurso internacional Holcim Awards for Sustainable Construction.</p><p>Hoy trabaja en un conjunto habitacional en La Matanza que será construido íntegramente con materiales reciclados.</p>', NULL, 1, '2026-06-03 13:40:20', '2026-06-03 13:40:20');

-- --------------------------------------------------------

--
-- Table structure for table `imagenes_egresados`
--

CREATE TABLE `imagenes_egresados` (
  `id_imagen` int(10) UNSIGNED NOT NULL,
  `id_egresado` int(10) UNSIGNED NOT NULL,
  `url_imagen` varchar(300) NOT NULL COMMENT 'Ruta relativa al archivo',
  `alt_text` varchar(200) DEFAULT NULL COMMENT 'Texto alternativo accesible',
  `orden` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Orden de aparición (0 = primero)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Fotos adicionales por egresado (para el detalle)';

--
-- Dumping data for table `imagenes_egresados`
--

INSERT INTO `imagenes_egresados` (`id_imagen`, `id_egresado`, `url_imagen`, `alt_text`, `orden`) VALUES
(1, 1, 'uploads/egresados/lucia-gomez-1.jpg', 'Lucía Gómez en quirófano', 0),
(2, 1, 'uploads/egresados/lucia-gomez-2.jpg', 'Lucía con alumnos del colegio', 1),
(3, 2, 'uploads/egresados/matias-ferreyra-1.jpg', 'Matías en su oficina', 0),
(4, 3, 'uploads/egresados/valentina-rios-1.jpg', 'Valentina con maqueta del proyecto', 0),
(5, 3, 'uploads/egresados/valentina-rios-2.jpg', 'Proyecto Holcim Awards', 1);

-- --------------------------------------------------------

--
-- Table structure for table `imagenes_novedades`
--

CREATE TABLE `imagenes_novedades` (
  `id_imagen` int(11) NOT NULL,
  `id_novedad` int(11) NOT NULL,
  `url_imagen` varchar(500) NOT NULL,
  `orden` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `imagenes_novedades`
--

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

-- --------------------------------------------------------

--
-- Table structure for table `novedades`
--

CREATE TABLE `novedades` (
  `id_novedad` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `etiqueta` enum('Inicial','Primario','Técnica','Orientada','Global') NOT NULL DEFAULT 'Global',
  `origen` enum('manual','instagram') NOT NULL DEFAULT 'manual',
  `ig_post_id` varchar(64) DEFAULT NULL,
  `ig_permalink` varchar(500) DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `novedades`
--

INSERT INTO `novedades` (`id_novedad`, `titulo`, `descripcion`, `etiqueta`, `origen`, `ig_post_id`, `ig_permalink`, `id_usuario`, `created_at`, `updated_at`) VALUES
(1, 'Inicio del ciclo lectivo 2026', 'El lunes 2 de marzo comenzaron las clases para todos los niveles. Bienvenidos a un nuevo año escolar.', 'Global', 'manual', NULL, NULL, 1, '2026-05-27 12:20:56', '2026-05-27 12:20:56'),
(2, 'Acto del Día de la Bandera — Nivel Primario', 'El viernes 20 de junio se realizará el acto en el patio principal. Se solicita presencia de los padres a partir de las 9:00 hs.', 'Primario', 'manual', NULL, NULL, 3, '2026-05-27 12:20:56', '2026-05-27 12:20:56'),
(3, 'Inscripción Sala de 3 — Nivel Inicial', 'Se encuentran abiertas las inscripciones para Sala de 3 años. Dirigirse a Secretaría con DNI del niño y libreta sanitaria.', 'Inicial', 'manual', NULL, NULL, 4, '2026-05-27 12:20:56', '2026-05-27 12:20:56'),
(4, 'Proyecto final de Informática — Sec. Técnica', 'Los alumnos de 7° año presentarán sus proyectos finales el 10 de noviembre. Más info con el profesor a cargo.', 'Técnica', 'manual', NULL, NULL, 3, '2026-05-27 12:20:56', '2026-05-27 12:20:56'),
(5, 'Jornada de orientación vocacional — Sec. Orientada', 'El 15 de agosto se realizará una jornada con representantes de universidades nacionales. Obligatoria para 6° año.', 'Orientada', 'manual', NULL, NULL, 2, '2026-05-27 12:20:56', '2026-05-27 12:20:56'),
(6, '🎉 ¡Comenzó la Feria de Ciencias 2026! Los alumnos de Secundaria presentaron p…', '🎉 ¡Comenzó la Feria de Ciencias 2026! Los alumnos de Secundaria presentaron proyectos increíbles sobre energías renovables. Gracias a las familias que nos acompañaron. #JuanXXIII #FeriaDeCiencias', 'Global', 'instagram', '17900000000000001', 'https://www.instagram.com/p/EJEMPLO001/', 5, '2026-06-15 19:30:00', '2026-06-19 22:32:44'),
(7, 'Acto del Día de la Bandera en el patio principal.', 'Acto del Día de la Bandera en el patio principal. ¡Gracias a todos por la hermosa jornada! 🇦🇷', 'Global', 'instagram', '17900000000000002', 'https://www.instagram.com/p/EJEMPLO002/', 5, '2026-06-12 16:00:00', '2026-06-19 22:32:47'),
(8, 'Inscripciones abiertas para Sala de 3 🧒.', 'Inscripciones abiertas para Sala de 3 🧒. Acercate a Secretaría con el DNI del niño y la libreta sanitaria. ¡Los esperamos!', 'Global', 'instagram', '17900000000000003', 'https://www.instagram.com/p/EJEMPLO003/', 5, '2026-06-08 14:15:00', '2026-06-19 22:32:54');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `rol` enum('admin','directivo','docente','secretaria') NOT NULL DEFAULT 'docente',
  `email` varchar(180) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `rol`, `email`, `telefono`, `password`, `created_at`) VALUES
(1, 'María', 'González', 'admin', 'maria.gonzalez@juanxxiii.edu.ar', '+54 9 11 2345-6789', '$2y$12$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW', '2026-05-27 12:20:56'),
(2, 'Carlos', 'Rodríguez', 'directivo', 'carlos.rodriguez@juanxxiii.edu.ar', '+54 9 11 3456-7890', '$2y$12$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW', '2026-05-27 12:20:56'),
(3, 'Laura', 'Pérez', 'docente', 'laura.perez@juanxxiii.edu.ar', '+54 9 11 4567-8901', '$2y$12$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW', '2026-05-27 12:20:56'),
(4, 'Martín', 'López', 'secretaria', 'martin.lopez@juanxxiii.edu.ar', NULL, '$2y$12$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW', '2026-05-27 12:20:56'),
(5, 'Instagram', 'Colegio', 'admin', 'instagram@juanxxiii.edu.ar', NULL, '$2y$12$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW', '2026-06-19 22:31:34');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_novedades_completas`
-- (See below for the actual view)
--
CREATE TABLE `v_novedades_completas` (
`id_novedad` int(11)
,`titulo` varchar(255)
,`descripcion` text
,`etiqueta` enum('Inicial','Primario','Técnica','Orientada','Global')
,`created_at` timestamp
,`updated_at` timestamp
,`autor` varchar(201)
,`email_autor` varchar(180)
,`rol_autor` enum('admin','directivo','docente','secretaria')
,`id_imagen` int(11)
,`url_imagen` varchar(500)
,`orden_imagen` tinyint(4)
);

-- --------------------------------------------------------

--
-- Structure for view `v_novedades_completas`
--
DROP TABLE IF EXISTS `v_novedades_completas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_novedades_completas`  AS SELECT `n`.`id_novedad` AS `id_novedad`, `n`.`titulo` AS `titulo`, `n`.`descripcion` AS `descripcion`, `n`.`etiqueta` AS `etiqueta`, `n`.`created_at` AS `created_at`, `n`.`updated_at` AS `updated_at`, concat(`u`.`nombre`,' ',`u`.`apellido`) AS `autor`, `u`.`email` AS `email_autor`, `u`.`rol` AS `rol_autor`, `i`.`id_imagen` AS `id_imagen`, `i`.`url_imagen` AS `url_imagen`, `i`.`orden` AS `orden_imagen` FROM ((`novedades` `n` join `usuarios` `u` on(`u`.`id_usuario` = `n`.`id_usuario`)) left join `imagenes_novedades` `i` on(`i`.`id_novedad` = `n`.`id_novedad`)) ORDER BY `n`.`created_at` DESC, `i`.`orden` ASC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `imagenes_novedades`
--
ALTER TABLE `imagenes_novedades`
  ADD PRIMARY KEY (`id_imagen`),
  ADD KEY `idx_imagenes_novedad` (`id_novedad`,`orden`);

--
-- Indexes for table `novedades`
--
ALTER TABLE `novedades`
  ADD PRIMARY KEY (`id_novedad`),
  ADD UNIQUE KEY `uniq_ig_post` (`ig_post_id`),
  ADD KEY `idx_novedades_etiqueta` (`etiqueta`),
  ADD KEY `idx_novedades_usuario` (`id_usuario`),
  ADD KEY `idx_novedades_fecha` (`created_at`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `uq_usuarios_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `imagenes_novedades`
--
ALTER TABLE `imagenes_novedades`
  MODIFY `id_imagen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `novedades`
--
ALTER TABLE `novedades`
  MODIFY `id_novedad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `imagenes_novedades`
--
ALTER TABLE `imagenes_novedades`
  ADD CONSTRAINT `fk_imagen_novedad` FOREIGN KEY (`id_novedad`) REFERENCES `novedades` (`id_novedad`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `novedades`
--
ALTER TABLE `novedades`
  ADD CONSTRAINT `fk_novedad_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
