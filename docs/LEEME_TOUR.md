# Módulo Recorrido Virtual 360° — Colegio Parroquial Juan XXIII

Tour estilo Street View administrado desde el Panel de Control:
subís las fotos 360 y colocás los puntos de transición haciendo
doble click sobre la propia imagen.

## Contenido

- `migracion_tour.sql` .......... crea las tablas `tour_escenas` y `tour_hotspots`
- `gestion_tour.php` ............ página del panel (requiere login, usa panel_config.php)
- `recorrido-360.php` ........... página pública del tour (lee de la base)
- `pannellum/` .................. librería del visor 360 (local, sin CDN — licencia MIT)
- `img/tour/` ................... carpeta donde se guardan las fotos subidas

## Instalación (XAMPP y Donweb, mismos pasos)

1. Copiar `gestion_tour.php`, `recorrido-360.php`, la carpeta `pannellum/`
   y la carpeta `img/tour/` a la raíz del sitio (junto a panel.php).

2. Ejecutar `migracion_tour.sql` en phpMyAdmin sobre la base
   `colegio_juan_xxiii` (una sola vez).

3. Dar permisos de escritura a `img/tour/` (en Donweb: 775).

4. Agregar el acceso en el panel (panel.php), junto a las otras tarjetas:
   enlace a `gestion_tour.php`.

5. En el sitio público, apuntar el menú "Recorrido Virtual" a
   `recorrido-360.php` (reemplaza a recorrido-virtual.html).

## Uso

1. Panel → Recorrido Virtual 360° → "Agregar escena": nombre, zona
   (agrupa el menú del tour: Acceso, Nivel Primario, etc.) y la foto
   esférica sacada con la app Google Street View (formato equirectangular,
   proporción 2:1 — si no la tiene, el sistema avisa).

2. Con la escena abierta en el editor: **doble click** sobre la foto en el
   lugar exacto de la puerta/pasillo. Aparece el marcador rojo y las
   coordenadas se cargan solas en el formulario.

3. Elegir tipo de punto:
   - **Transición** → seleccionar la escena destino (la flecha lleva ahí).
   - **Información** → escribir el texto del globo.

4. "Fijar la vista actual como inicial": girá la foto hasta la orientación
   con la que querés que se entre a la escena y tocá el botón.

5. Consejo: cada transición conviene hacerla en ambos sentidos
   (patio → pasillo y pasillo → patio) para poder "volver" caminando.

6. La casilla "Visible en la web pública" permite cargar escenas y
   publicarlas recién cuando el recorrido esté completo.
