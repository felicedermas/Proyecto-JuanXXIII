-- ============================================================
--  migracion_permisos_secundario.sql
--  Colegio Parroquial Juan XXIII
--
--  OPCIONAL. Corrige un dato viejo, no cambia el esquema.
--
--  Los niveles Directivo, Docente y Secretaría se crearon cuando la
--  lista de categorías todavía no incluía "Secundario": quedaron con
--  Inicial, Primario, Técnica, Orientada y Global. Mientras las
--  páginas del panel no verificaban la categoría, eso no se notaba
--  (el desplegable mostraba las seis y se podía publicar en
--  cualquiera). Ahora que Novedades y Agenda validan la categoría al
--  guardar, esos niveles NO pueden publicar en "Secundario".
--
--  Este script agrega "Secundario" SOLO a los niveles que ya tienen
--  habilitadas las otras cinco categorías, es decir, a los que
--  claramente estaban pensados como "todas". No toca ningún nivel
--  creado a propósito con un subconjunto de categorías.
--
--  Lo mismo se puede hacer a mano desde el panel → Permisos,
--  tildando "Secundario" en el nivel que corresponda: es el camino
--  recomendado si los niveles se armaron con criterios distintos.
--
--  Se puede correr más de una vez sin problema.
-- ============================================================

-- Novedades
UPDATE niveles_permiso
   SET cat_novedades = CONCAT(cat_novedades, ',Secundario')
 WHERE pub_novedades = 1
   AND FIND_IN_SET('Secundario', cat_novedades) = 0
   AND FIND_IN_SET('Inicial',    cat_novedades) > 0
   AND FIND_IN_SET('Primario',   cat_novedades) > 0
   AND FIND_IN_SET('Técnica',    cat_novedades) > 0
   AND FIND_IN_SET('Orientada',  cat_novedades) > 0
   AND FIND_IN_SET('Global',     cat_novedades) > 0;

-- Agenda
UPDATE niveles_permiso
   SET cat_agenda = CONCAT(cat_agenda, ',Secundario')
 WHERE pub_agenda = 1
   AND FIND_IN_SET('Secundario', cat_agenda) = 0
   AND FIND_IN_SET('Inicial',    cat_agenda) > 0
   AND FIND_IN_SET('Primario',   cat_agenda) > 0
   AND FIND_IN_SET('Técnica',    cat_agenda) > 0
   AND FIND_IN_SET('Orientada',  cat_agenda) > 0
   AND FIND_IN_SET('Global',     cat_agenda) > 0;

-- Para verificar cómo quedó:
-- SELECT id_nivel, nombre, cat_novedades, cat_agenda FROM niveles_permiso;
