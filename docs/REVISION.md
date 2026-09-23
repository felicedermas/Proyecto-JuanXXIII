# Revisión del proyecto — Sitio Colegio Parroquial Juan XXIII

Fecha: 14/09/2026 · Revisor: Claude · Carpeta: `C:\xampp\htdocs\JUAN23CLAUDE`

---

## Resumen

El proyecto está bastante mejor de lo típico en un sitio PHP de este tamaño: usás PDO con
prepared statements, `password_hash`/`password_verify`, CSRF token en todos los formularios,
`session_regenerate_id` al loguear, cookie `httponly` + `samesite`, validación de MIME real
con `finfo` en las subidas y nombres de archivo generados con `random_bytes`. Eso ya te pone
por encima de la media.

Los problemas reales no son de "código feo": son **de alcance**. Hay un sistema de permisos
completo construido que **nunca se usa**, formularios públicos que no envían nada, y páginas
públicas que no tienen backoffice.

---

## 🔴 Crítico — arreglar antes de publicar

### 1. El sistema de permisos existe pero no se aplica en ningún lado

`panel_config.php` define `puede()`, `categorias_permitidas()`, `nivel_actual()`, y
`gestion_permisos.php` permite crear niveles con flags (`pub_novedades`, `pub_agenda`,
`edita_tour`, `alta_usuarios`, `inscripciones`) y categorías por nivel.

Pero el grep dice todo:

```
gestion_agenda.php:3     exigir_login();
gestion_novedades.php:3  exigir_login();
gestion_tour.php:9       exigir_login();
```

**`puede()` no se llama ni una sola vez fuera de su propia definición.** Consecuencia: cualquier
usuario logueado — sin importar su nivel — puede publicar novedades y eventos en **cualquier**
categoría (incluso las que su nivel tiene apagadas) y editar el recorrido 360°. Toda la pantalla
de permisos es decorativa.

Qué agregar:

- En `gestion_novedades.php` / `gestion_agenda.php`: guardia al entrar
  (`if (!puede('pub_novedades')) { http_response_code(403); … }`) y, en el POST de crear/editar,
  validar que la etiqueta elegida esté en `categorias_permitidas('pub_novedades')`.
- Poblar los `<select>` de etiqueta con `categorias_permitidas()` en vez de `ETIQUETAS_VALIDAS`.
- En `gestion_tour.php`: `if (!puede('edita_tour')) { … }`.
- En `panel.php`: ocultar las tarjetas que el usuario no puede usar (hoy solo se ocultan las de admin).

> Regla: validar en el POST, no solo en la UI. Ocultar el botón no es un permiso.

### 2. `generar_hash.php` es accesible por GET desde el navegador

El propio archivo dice "borralo en producción" — pero sigue ahí. Con XAMPP expuesto, cualquiera
puede llamar `generar_hash.php?pass=x`. No filtra credenciales, pero es un archivo de desarrollo
publicado. **Borralo** o movelo a un `/tools/` protegido por `.htaccess`.

### 3. `sync_instagram.php` no tiene control de acceso

Se puede ejecutar desde el navegador sin login (`require __DIR__ . '/config.php'` y nada más).
Cualquiera puede dispararlo indefinidamente: escritura en la base, descarga de imágenes al disco.

Qué agregar: `require_once panel_config.php; if (php_sapi_name() !== 'cli') { exigir_admin(); }`.

### 4. Falta `migracion_permisos.sql` en el repo

`gestion_usuarios.php` muestra este error al usuario:

> "Falta ejecutar las migraciones de permisos (migracion_permisos.sql,
> migracion_permisos_usuarios.sql y migracion_unificar_roles.sql)."

Ninguno de esos tres archivos está en la carpeta. Están `migracion_panel.sql` y `migracion_tour.sql`
solamente, y la tabla `niveles_permiso` no existe en el dump `colegio_juan_xxiii (6).sql`.
**Quien clone el proyecto no puede levantarlo.** Agregá los `.sql` faltantes (o un único
`instalar.sql` que arme todo el esquema desde cero).

### 5. Credenciales de base duplicadas en 4+ archivos

`config.php`, `panel_config.php`, `agenda.php`, `novedades.php`, `egresados.php`… cada uno
declara sus propias `DB_HOST/DB_USER/DB_PASS` con `root` y password vacía. Cuando pases a
hosting real vas a tener que cambiarlo en todos lados y te vas a olvidar de uno.

Qué agregar: un solo `conexion.php` (o que `config.php` sea la única fuente) que devuelva el PDO,
y que **todos** los demás hagan `require_once`. Las credenciales reales, en un archivo aparte
fuera del repo (`config.local.php`, listado en `.gitignore`).

---

## 🟠 Importante

### 6. Enlace roto en todo el sitio: `recorrido-virtual.html`

El menú de **todas** las páginas apunta a `recorrido-virtual.html`, que no existe. El archivo real
es `recorrido-360.php`. Son ~30 archivos HTML con el mismo 404. Buscar y reemplazar.

### 7. Los formularios de inscripción no envían nada

```html
<form class="insc-form" onsubmit="event.preventDefault();
  alert('La preinscripción en línea estará disponible próximamente.');">
```

Las cuatro páginas de inscripción (jardín, primaria, secundaria, hermanos) son maquetas. El usuario
completa 20 campos y recibe un `alert()`. Peor: la tarjeta "Inscripciones" del panel está marcada
como *Bloqueado / Próximamente*.

Qué agregar, mínimo viable:

- Tabla `preinscripciones` (datos del alumno, tutor, nivel, estado, `created_at`).
- `procesar_inscripcion.php` con validación server-side, CSRF y honeypot anti-spam.
- `gestion_inscripciones.php` en el panel: listado, filtro por nivel/estado, exportar a CSV.
- Email de confirmación a la familia + aviso a secretaría.

Si todavía no lo vas a hacer, al menos reemplazá el `alert()` por un mensaje en la página y sacá
el formulario de la vista — un form que no funciona daña más la confianza que no tenerlo.

### 8. `contacto.html` no tiene formulario de contacto

La página se llama "Formulario de contacto" en el footer, pero solo muestra datos. No hay `<form>`.
Es lo primero que busca una familia interesada. Agregá form + `procesar_contacto.php` (mismo
patrón que el punto anterior).

### 9. No hay gestión de Egresados

Existe la tabla `egresados` + `imagenes_egresados`, y las páginas públicas `egresados.php` /
`egresado.php`. **No existe `gestion_egresados.php`.** Hoy solo se pueden cargar por phpMyAdmin.
Copiá la estructura de `gestion_novedades.php` (ya tiene toda la lógica de imágenes múltiples).

### 10. El recorrido 360° no tiene autoría ni control de borrado

`tour_escenas` no tiene columna `id_usuario`, y `gestion_tour.php` no llama a `puede_gestionar()`
en ningún lado. Cualquier usuario logueado puede borrar escenas de otro. Agregá `id_usuario` a
la tabla y aplicá la misma regla que en novedades/agenda.

### 11. Sin protección contra fuerza bruta en el login

`login.php` acepta intentos ilimitados. Agregá: contador de intentos fallidos por email + IP
(tabla `intentos_login`), bloqueo temporal a los 5 intentos, y `usleep(300000)` en el fallo.

### 12. Sin recuperación de contraseña

No hay "olvidé mi contraseña" ni forma de que un usuario cambie la propia. Hoy la única vía es
que el admin la resetee a mano. Agregá `mi_cuenta.php` (cambiar contraseña con la actual) y,
si podés mandar mails, recuperación con token expirable.

---

## 🟡 Calidad y mantenimiento

### 13. Header y footer duplicados en ~30 archivos HTML

Cada cambio de menú son 30 ediciones. Es la razón por la que el link roto del punto 6 está en
todos lados. La solución no es un framework: renombrá los `.html` a `.php` y usá
`include 'partials/header.php'` / `include 'partials/footer.php'`. Es medio día de trabajo y te
ahorra el resto del proyecto.

> Ojo con el SEO: si cambiás extensiones, agregá redirects 301 en `.htaccess`.

### 14. Cero SEO

Ninguna de las páginas tiene `<meta name="description">`. Tampoco hay Open Graph (cuando alguien
comparte el sitio por WhatsApp no aparece ni imagen ni descripción), ni `sitemap.xml`, ni
`robots.txt`, ni datos estructurados `Schema.org/School`. Para un colegio que compite por
matrícula en Zona Oeste, esto vale plata.

Qué agregar por página: `description`, `og:title`, `og:description`, `og:image`, `canonical`.
En la raíz: `sitemap.xml`, `robots.txt`, JSON-LD `EducationalOrganization` con dirección y teléfono.

### 15. Imágenes 360 sin optimizar

`img/tour/` tiene PNG de **1,8 MB** cada uno. Son cuatro: casi 7 MB para cargar el recorrido en
el celular de una familia con datos móviles. Convertilos a JPG calidad 82 o WebP — bajan a
~250 KB sin diferencia visible en un panorama.

También falta `loading="lazy"` en varias imágenes y no hay `width`/`height` declarados (causa
saltos de layout / mal CLS).

### 16. Falta `.htaccess`

No hay ninguno. Agregá al menos:

- Bloquear acceso directo a `*.sql`, `*.md`, `config*.php` (hoy `colegio_juan_xxiii (6).sql`
  se descarga tecleando la URL — incluye la tabla `usuarios`).
- Páginas de error 404/403 propias.
- Compresión gzip y cache headers para `styles.css` (40 KB) e imágenes.

### 17. Sin control de versiones

La carpeta no es un repo git. Un archivo llamado `colegio_juan_xxiii (6).sql` es exactamente el
síntoma. `git init` + `.gitignore` (credenciales, `img/novedades/subidas/`, `img/tour/`).

### 18. Detalles menores

- Las carpetas `Comunidad/`, `Contacto/`, `Inscripciones/`, `Institucional/`, `Niveles/` están
  vacías — o las usás para organizar, o las borrás.
- `nivel-inicial.html` y `nivel-inicial.php` coexisten (52 KB y 66 KB). Decidí cuál queda.
- `gestion_usuarios.php:71` concatena `$id` en la consulta:
  `'SELECT COUNT(*) … WHERE id_usuario=' . $id`. Está casteado a `int` así que no es explotable,
  pero es el único lugar del proyecto que rompe el patrón — pasalo a prepared statement por
  consistencia.
- `agenda.php:60` concatena `$cond_count` (valor controlado por vos, no por el usuario — seguro,
  pero mismo criterio).
- No hay favicon.
- El error de conexión imprime `$e->getMessage()` en pantalla: en producción eso expone rutas y
  nombres de base. Logueá el error y mostrá un mensaje genérico.
- `styles.css` son 40 KB en un solo archivo sin minificar.

---

## Lo que falta como producto (no como código)

Ordenado por lo que más mueve la aguja para un colegio:

| # | Funcionalidad | Por qué |
|---|---|---|
| 1 | **Preinscripción online real** | Es la razón por la que una familia entra al sitio |
| 2 | **Formulario de contacto** | Lo segundo que buscan |
| 3 | **Gestión de egresados** | Ya está el 80% hecho, falta el ABM |
| 4 | **Buscador en novedades** | Con 50+ publicaciones se vuelve inusable sin él |
| 5 | **Galería de fotos institucional** | Actos, feria de ciencias, deportes |
| 6 | **Descarga de documentos** (listas de materiales, reglamento, calendario PDF) | Ahorra llamados a secretaría |
| 7 | **Exportar agenda a Google Calendar / .ics** | Una línea de código, mucho valor |
| 8 | **Newsletter / suscripción a novedades** | Recontacto con las familias |
| 9 | **Panel: registro de auditoría** (quién publicó/borró qué) | En una institución con varios usuarios, es necesario |
| 10 | **Modo borrador en novedades** | Hoy se publica al instante, sin revisión |

---

## Plan sugerido

**Semana 1 — seguridad y consistencia**
1. Borrar `generar_hash.php`, proteger `sync_instagram.php`
2. Aplicar `puede()` en novedades, agenda y tour
3. Unificar credenciales en un solo archivo + `.gitignore`
4. Agregar `.htaccess` (bloquear `.sql` y `.md`)
5. Agregar las migraciones faltantes / `instalar.sql`
6. `git init`

**Semana 2 — estructura**
7. Pasar HTML a PHP con `include` de header/footer
8. Arreglar el link `recorrido-virtual.html`
9. Meta descriptions + Open Graph + sitemap + robots
10. Optimizar las imágenes del tour

**Semana 3-4 — funcionalidad**
11. Formulario de contacto funcional
12. Preinscripción + `gestion_inscripciones.php`
13. `gestion_egresados.php`
14. Rate limiting en login + cambio de contraseña propia

---

## Lo que está bien y conviene no romper

- PDO con prepared statements en el 99% del código, `EMULATE_PREPARES => false`
- CSRF en todos los POST, verificado con `hash_equals`
- bcrypt vía `password_hash` / `password_verify`
- `session_regenerate_id(true)` al loguear, cookie httponly + samesite Lax
- Validación de imágenes por MIME real (`finfo`), no por extensión
- Nombres de archivo con `random_bytes` — no hay colisión ni path traversal
- Borrado de archivo físico verificando que esté dentro de la carpeta de subidas (`str_starts_with`)
- Escape con `htmlspecialchars` / `e()` en toda la salida
- El helper `puede_gestionar()` (autor o admin) es la regla correcta — solo falta usarla en el tour
