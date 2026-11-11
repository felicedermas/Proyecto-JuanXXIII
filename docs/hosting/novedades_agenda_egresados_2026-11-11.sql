-- ============================================================
--  Primeras publicaciones del sitio — 11/11/2026
--
--  Carga 7 novedades, 2 fechas de agenda y 2 historias de egresados
--  de ejemplo, firmadas por el usuario "Felipe Admin".
--
--  Cómo correrlo (una sola vez; si se corre dos veces, se duplica todo):
--    InfinityFree → MySQL Databases → phpMyAdmin → base del colegio
--    → pestaña "SQL" → pegar todo el archivo → Continuar.
--
--  Las fotos están en img/imgs/ y se suben solas con el código.
--  Todo se puede editar o borrar después desde el panel.
-- ============================================================

SET NAMES utf8mb4;

-- Autor: Felipe Admin; si no existe en esta base, el primer administrador.
SET @autor := COALESCE(
  (SELECT id_usuario FROM usuarios WHERE nombre = 'Felipe' AND apellido = 'Admin' LIMIT 1),
  (SELECT MIN(id_usuario) FROM usuarios WHERE rol = 'admin')
);

START TRANSACTION;

-- ------------------------------------------------------------
--  NOVEDADES
-- ------------------------------------------------------------

-- 1. Feria de Ciencias (aviso)
INSERT INTO novedades (titulo, descripcion, etiqueta, origen, id_usuario, created_at) VALUES
('Este viernes 20 de noviembre: Feria de Ciencias',
 'Te esperamos el viernes 20 de noviembre en el patio del colegio para la Feria de Ciencias. Los alumnos de Secundaria presentan sus proyectos y la entrada es libre: pueden venir las familias, los alumnos de todos los niveles y cualquier persona que quiera acercarse.',
 'Global', 'manual', @autor, '2026-11-11 10:00:00');

-- 2. Karting de Electrónica
INSERT INTO novedades (titulo, descripcion, etiqueta, origen, id_usuario, created_at) VALUES
('Electrónica construye un karting',
 'Los alumnos de la especialidad Electrónica de la Secundaria Técnica están construyendo un karting en el taller. Pronto vamos a compartir cómo avanza el proyecto.',
 'Técnica', 'manual', @autor, '2026-11-06 12:00:00');

-- 3. Convivencia del Jardín con los más grandes
INSERT INTO novedades (titulo, descripcion, etiqueta, origen, id_usuario, created_at) VALUES
('Convivencia del Jardín con los más grandes',
 'Las salas del Jardín compartieron una mañana con alumnos más grandes del colegio: jugaron, armaron juegos en el piso de la sala y se conocieron un poco más. Una linda forma de sentirnos parte de la misma comunidad.',
 'Inicial', 'manual', @autor, '2026-10-29 11:00:00');
SET @n := LAST_INSERT_ID();
INSERT INTO imagenes_novedades (id_novedad, url_imagen, orden) VALUES (@n, 'img/imgs/Colegio-Jardin.png', 0);

-- 4. Salida a Luján
INSERT INTO novedades (titulo, descripcion, etiqueta, origen, id_usuario, created_at) VALUES
('Todos los cursos viajamos a Luján',
 'En octubre, todos los cursos del colegio viajamos juntos a Luján. Visitamos la Basílica y compartimos una jornada de encuentro y oración.',
 'Global', 'manual', @autor, '2026-10-20 18:00:00');
SET @n := LAST_INSERT_ID();
INSERT INTO imagenes_novedades (id_novedad, url_imagen, orden) VALUES
(@n, 'img/imgs/Lujan-Basilica-Exterior.jpg', 0),
(@n, 'img/imgs/Lujan-Basilica-Interior.jpg', 1);

-- 5. Día de San Juan XXIII (Pastoral)
INSERT INTO novedades (titulo, descripcion, etiqueta, origen, id_usuario, created_at) VALUES
('Celebramos a San Juan XXIII',
 'El 11 de octubre, día de San Juan XXIII, celebramos la misa con alumnos de Secundaria. Los cursos prepararon carteles con frases de su Decálogo de la serenidad, como "No tendré miedo de gozar de lo que es bello".',
 'Global', 'manual', @autor, '2026-10-13 09:30:00');
SET @n := LAST_INSERT_ID();
INSERT INTO imagenes_novedades (id_novedad, url_imagen, orden) VALUES (@n, 'img/imgs/Comunidad-Pastoral.png', 0);

-- 6. Interbandos
INSERT INTO novedades (titulo, descripcion, etiqueta, origen, id_usuario, created_at) VALUES
('Se jugaron los interbandos',
 'Los cursos de Secundaria participaron de los interbandos: una jornada de juegos por curso en la que cada equipo iba sumando puntos. ¡Gracias a todos por la buena onda y el compañerismo!',
 'Secundario', 'manual', @autor, '2026-09-23 17:00:00');

-- 7. Abanderados del Jardín
INSERT INTO novedades (titulo, descripcion, etiqueta, origen, id_usuario, created_at) VALUES
('Nuestros abanderados del Jardín',
 'Los abanderados y escoltas del Nivel Inicial acompañaron el acto junto a sus compañeros, familias y docentes. ¡Felicitaciones a todos!',
 'Inicial', 'manual', @autor, '2026-09-10 12:00:00');
SET @n := LAST_INSERT_ID();
INSERT INTO imagenes_novedades (id_novedad, url_imagen, orden) VALUES (@n, 'img/imgs/Actividad-Primaria.png', 0);


-- ------------------------------------------------------------
--  AGENDA
-- ------------------------------------------------------------
INSERT INTO agenda (titulo, descripcion, etiqueta, tipo, fecha_evento, hora_inicio, hora_fin, lugar, enlace, id_usuario) VALUES
('Feria de Ciencias',
 'Los alumnos de Secundaria presentan sus proyectos. Abierta a las familias, a todos los niveles y a quien quiera acercarse.',
 'Global', 'Acto', '2026-11-20', NULL, NULL, 'Patio del colegio', 'feria-ciencias', @autor),
('Acto de colación — Secundaria Técnica',
 'Ceremonia de egreso de los alumnos de 7° año de la Secundaria Técnica.',
 'Técnica', 'Acto', '2026-12-12', NULL, NULL, NULL, NULL, @autor);


-- ------------------------------------------------------------
--  EGRESADOS (historias de ejemplo, personas ficticias)
-- ------------------------------------------------------------
INSERT INTO egresados (nombre, apellido, anio_egreso, orientacion, subtitulo, resumen, cuerpo, foto_perfil, publicado, id_usuario) VALUES
('Lucas', 'Medina', 2019, 'Técnica',
 'Técnico en Informática · Desarrollo de software (historia de ejemplo)',
 'Lucas terminó la técnica con la especialidad Informática y hoy trabaja como desarrollador de software. Cuenta qué le sirvió del taller y del proyecto integrador de 7° año.',
 '<p><em>Esta es una historia de ejemplo, con una persona ficticia, para mostrar cómo se ve esta sección.</em></p>
<p>Lucas entró al colegio en 1° año sin saber bien qué especialidad elegir. En el ciclo básico descubrió que le gustaba entender cómo funcionan las cosas por dentro, y al llegar a 4° año eligió Informática.</p>
<p>Del taller rescata, sobre todo, haber trabajado en equipo con plazos reales: "En el proyecto integrador de 7° año tuvimos que armar un sistema completo, desde la base de datos hasta la pantalla que usaba la gente. Fue la primera vez que sentí que estaba haciendo algo que servía".</p>
<p>Hoy trabaja como desarrollador de software y sigue estudiando. A los chicos que están eligiendo especialidad les recomienda animarse a probar: "Las horas de taller son las que después más se notan".</p>',
 NULL, 1, @autor),
('Sofía', 'Herrera', 2017, 'Bachillerato Orientado',
 'Bachiller en Ciencias Naturales · Carrera de salud (historia de ejemplo)',
 'Sofía egresó de la orientación en Ciencias Naturales y siguió una carrera de salud. Recuerda las prácticas de laboratorio y su paso por la Feria de Ciencias.',
 '<p><em>Esta es una historia de ejemplo, con una persona ficticia, para mostrar cómo se ve esta sección.</em></p>
<p>Sofía hizo toda la secundaria en el colegio y en 4° año eligió la orientación en Ciencias Naturales. Las horas de laboratorio fueron lo que más disfrutó: "Llevar la libreta, hacer los informes y repetir un experimento hasta que salía bien me enseñó a ser ordenada".</p>
<p>En 5° año presentó un proyecto en la Feria de Ciencias junto a sus compañeros, y esa experiencia la ayudó a perderle el miedo a hablar en público.</p>
<p>Después de egresar empezó una carrera de salud. "Cuando llegué a la facultad, muchas cosas del laboratorio ya me resultaban conocidas", cuenta.</p>',
 NULL, 1, @autor);

COMMIT;
