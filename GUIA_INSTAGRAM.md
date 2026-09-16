# Instagram → Novedades del sitio

Cómo hacer que lo que publican las cuentas de Instagram aparezca solo en el sitio.

---

## Lo que hay que saber antes de empezar

Instagram **no deja leer publicaciones de una cuenta personal**. La API vieja que sí lo
permitía (Basic Display) la cerró Meta el 4 de diciembre de 2024 y no tiene reemplazo para
cuentas personales. Así que cada una de las tres cuentas tiene que ser **Profesional**
(Creador o Empresa). Es gratis, se hace desde la app del celular en dos minutos, se puede
volver atrás cuando quieras y no cambia nada de cómo se ve la cuenta para los seguidores.

**No hace falta página de Facebook.** Vamos por el camino "Instagram API con inicio de
sesión de Instagram", que autentica directo contra Instagram. (El otro camino, el que sí
exige vincular una página de Facebook, es para funciones que no necesitamos.)

Necesitás además **una app en Meta for Developers**: una sola sirve para las tres cuentas.
Eso lo hacés vos una vez y no lo toca nadie más.

Los tokens duran 60 días, pero **el sistema los renueva solo** mientras la tarea diaria
siga corriendo. Ojo con esto: un token vencido **no se puede renovar**, hay que generar
uno nuevo a mano. Por eso conviene que la tarea de las 19:00 no se caiga por meses.

---

## Paso 1 — Convertir las cuentas a Profesional

Esto lo tiene que hacer quien administra cada cuenta, desde el celular:

1. Instagram → tu perfil → menú ☰ → **Configuración y privacidad**
2. **Tipo de cuenta y herramientas** → **Cambiar a cuenta profesional**
3. Elegí la categoría **Educación**
4. Elegí **Creador** o **Empresa** (para esto da igual; *Empresa* es lo habitual en una institución)
5. Si te ofrece vincular una página de Facebook, podés **saltear** ese paso

Repetí para las tres cuentas: inicial, primaria y secundaria.

> Sin este paso nada de lo que sigue funciona. Es donde más se traba la gente.

---

## Paso 2 — Crear la app en Meta (una sola vez)

1. Entrá a <https://developers.facebook.com/> con tu cuenta de Facebook y registrate
   como desarrollador si te lo pide.
2. **Mis aplicaciones** → **Crear aplicación**.
3. Caso de uso: **Otro** → tipo de app: **Empresa**.
4. Nombre: algo como `Web Juan XXIII`.
5. Ya dentro de la app: **Agregar producto** → **Instagram** → **Configurar**.
6. Entrá a **Instagram → Configuración de la API con inicio de sesión de Instagram**
   (en inglés aparece como *API setup with Instagram business login*).

---

## Paso 3 — Dar de alta cada cuenta y generar su token

Para cada una de las tres cuentas, dentro de esa misma pantalla:

1. **Paso 1 de la pantalla de Meta — "Generar token de acceso"**: agregá la cuenta de
   Instagram. Meta le manda una invitación.
2. Desde el celular, con esa cuenta iniciada, entrá a
   <https://www.instagram.com/accounts/manage_access/> y **aceptá la invitación** de la app.
   (Si no aceptás, el token no se genera.)
3. Volvé a Meta y hacé clic en **Generar token**. Aceptá los permisos que pide:
   alcanza con **`instagram_business_basic`**, que es de solo lectura.
4. Copiá el token largo que aparece — empieza con `IGQ...`. Es **un token por cuenta**.

> Guardalos en algún lado seguro mientras hacés las tres. El token es como una llave:
> con eso se puede leer la cuenta.

---

## Paso 4 — Cargarlos en el sitio

1. Entrá al panel: **Panel → Instagram**.
2. En cada cuenta, **Editar**:
   - Pegá el token en **Token de acceso**
   - Revisá que la **etiqueta** sea la correcta
     (Inicial → Inicial, Primaria → Primario, Secundaria → Secundario)
   - **No importar anteriores a**: dejá la fecha de hoy, así la primera corrida
     no trae años de publicaciones viejas
3. Guardá.
4. Andá a **Panel → Datos de contacto** y poné **Modo de prueba** en `0`.
5. Volvé a **Panel → Instagram** y apretá **Sincronizar ahora**.

Si algo falla, el error aparece en la tarjeta de la cuenta con el motivo concreto.

---

## Paso 5 — Que corra solo todos los días a las 19:00

### En la PC del colegio (XAMPP, Windows)

1. Copiá `sincronizar_instagram.bat` donde te quede cómodo (ya está en la carpeta del sitio).
2. Abrí el **Programador de tareas** de Windows (buscalo en el menú Inicio).
3. **Crear tarea básica…**
   - Nombre: `Sincronizar Instagram - Juan XXIII`
   - Desencadenador: **Diariamente**, hora **19:00**
   - Acción: **Iniciar un programa**
   - Programa: la ruta completa a `sincronizar_instagram.bat`
4. Terminada la tarea, abrí sus **Propiedades** y marcá
   **Ejecutar tanto si el usuario inició sesión como si no**.

> Ojo: si la PC está apagada a las 19:00, la tarea no corre. En **Propiedades →
> Condiciones** podés marcar *Ejecutar la tarea lo antes posible tras un inicio
> programado omitido*, y entonces se pone al día cuando se prenda.

### En un hosting con cron

```
0 19 * * *  curl -s "https://tudominio.com/sync_instagram.php?clave=LA_CLAVE"
```

La clave la ves en **Panel → Instagram**, en la sección "Sincronización automática".

---

## Cómo lo usan los directivos

No tienen que entrar al sitio. Publican en Instagram como siempre y a las 19:00
aparece en Novedades, con la foto y el texto del post.

**Si algo no tiene que ir al sitio** (un saludo de cumpleaños, un meme, algo interno),
le agregan `#nolaweb` al final del texto del post y el sistema lo saltea.
Esa palabra se puede cambiar en **Panel → Datos de contacto → Palabra para NO publicar**.

---

## Qué se importa y qué no

| Tipo de publicación | Qué pasa |
|---|---|
| Foto simple | Entra con esa foto |
| Carrusel | Entra con todas las fotos, en orden |
| Reel o video | Entra con la portada del video; el link al post queda guardado |
| Historia (story) | **No** entra: la API no las expone |
| Post con la palabra de exclusión | No entra |
| Post sin ninguna imagen | No entra |
| Post ya importado antes | No se duplica |

El texto del post se usa completo como descripción de la novedad, y la primera frase
(sin hashtags) como título.

---

## Si algo sale mal

**"Token inválido o cuenta sin permisos"**
La cuenta todavía es personal, o no aceptó la invitación de la app, o el token se generó
para otra cuenta. Rehacé los pasos 1 y 3.

**"El token venció"**
Pasaron más de 60 días sin que corriera la tarea. Un token vencido no se puede renovar:
hay que generar uno nuevo (paso 3) y cargarlo de nuevo en el panel.

**"La extensión cURL de PHP no está activa"**
En XAMPP: abrí `C:\xampp\php\php.ini`, buscá `;extension=curl`, sacale el `;` del
principio y reiniciá Apache.

**Entran las novedades pero sin foto**
Falta permiso de escritura en `img/novedades/instagram`. En Windows suele andar solo;
en un hosting Linux: `chmod 775`.

**No entra nada y no hay error**
Revisá la fecha de **"No importar anteriores a"**: si es posterior a las publicaciones,
las saltea a propósito.

---

## Dónde está cada cosa

| Archivo | Para qué |
|---|---|
| `ig_sync.php` | El motor. Define las funciones, no se abre directo. |
| `sync_instagram.php` | Lo que ejecuta la tarea o el botón del panel. |
| `gestion_instagram.php` | La pantalla del panel: cuentas, estado, historial. |
| `migracion_instagram.sql` | Crea las tablas. Se corre una sola vez. |
| `posts_simulados.json` | Publicaciones de ejemplo para el modo de prueba. |
| `sincronizar_instagram.bat` | El que llama el Programador de tareas de Windows. |

En la base:

- `ig_cuentas` — las cuentas, sus tokens y su etiqueta
- `ig_sync_log` — qué pasó en cada corrida
- `novedades.id_cuenta_ig` — de qué cuenta vino cada novedad
- `novedades.ig_post_id` — único: es lo que evita importar dos veces el mismo post
