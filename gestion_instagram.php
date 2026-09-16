<?php
// ============================================================
//  gestion_instagram.php
//  Panel de Control — Cuentas de Instagram
//  Alta/edición de las cuentas que alimentan las novedades,
//  estado de cada una y sincronización manual.
//  Requiere migracion_instagram.sql.
// ============================================================
require_once __DIR__ . '/panel_config.php';
exigir_admin();

$pdo = db();

// ── ¿Está hecha la migración? ───────────────────────────────
$tabla_ok = true;
try { $pdo->query('SELECT 1 FROM ig_cuentas LIMIT 1'); }
catch (Throwable $ex) { $tabla_ok = false; }

// ── Clave de la tarea programada: se genera sola la 1ª vez ──
//  cfg() cachea los valores al principio de la petición, así que si la
//  generamos ahora hay que quedarnos con el valor en una variable: leerla
//  de nuevo con cfg() devolvería el caché vacío y la URL saldría sin clave.
$clave_tarea = trim(cfg('ig_clave_tarea', ''));
if ($tabla_ok && $clave_tarea === '') {
    try {
        $clave_tarea = bin2hex(random_bytes(16));
        $pdo->prepare('UPDATE configuracion SET valor = ? WHERE clave = ?')
            ->execute([$clave_tarea, 'ig_clave_tarea']);
    } catch (Throwable $ex) {
        $clave_tarea = '';   // la tabla configuracion todavía no existe
    }
}

// ============================================================
//  ACCIONES
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tabla_ok) {
    csrf_check();
    $accion = (string) ($_POST['accion'] ?? '');

    // ── Guardar (alta o edición) ──
    if ($accion === 'guardar') {
        $id        = (int) ($_POST['id_cuenta'] ?? 0);
        $nombre    = trim((string) ($_POST['nombre'] ?? ''));
        $usuario   = trim((string) ($_POST['usuario_ig'] ?? ''));
        $etiqueta  = (string) ($_POST['etiqueta'] ?? 'Global');
        $autor     = (int) ($_POST['id_usuario'] ?? 0);
        $limite    = max(1, min(50, (int) ($_POST['limite_posts'] ?? 10)));
        $desde     = trim((string) ($_POST['importar_desde'] ?? ''));
        $activa    = isset($_POST['activa']) ? 1 : 0;
        $token     = trim((string) ($_POST['access_token'] ?? ''));

        $errores = [];
        if ($nombre === '')                                    $errores[] = 'Poné un nombre para la cuenta.';
        if ($usuario === '')                                   $errores[] = 'Poné el usuario de Instagram (el @).';
        if (!in_array($etiqueta, ETIQUETAS_VALIDAS, true))     $errores[] = 'Etiqueta inválida.';
        if ($autor <= 0)                                       $errores[] = 'Elegí qué usuario figura como autor.';
        if ($desde !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) $errores[] = 'Fecha de corte inválida.';

        if ($usuario !== '' && $usuario[0] !== '@') $usuario = '@' . $usuario;
        $desde_v = $desde !== '' ? $desde : null;

        if ($errores) {
            flash('error', implode(' ', $errores));
        } elseif ($id > 0) {
            // El token solo se pisa si escribieron uno nuevo
            if ($token !== '') {
                $pdo->prepare(
                    'UPDATE ig_cuentas SET nombre=?, usuario_ig=?, etiqueta=?, id_usuario=?,
                            limite_posts=?, importar_desde=?, activa=?, access_token=?, token_expira=?
                      WHERE id_cuenta=?'
                )->execute([$nombre, $usuario, $etiqueta, $autor, $limite, $desde_v, $activa,
                            $token, date('Y-m-d', strtotime('+60 days')), $id]);
            } else {
                $pdo->prepare(
                    'UPDATE ig_cuentas SET nombre=?, usuario_ig=?, etiqueta=?, id_usuario=?,
                            limite_posts=?, importar_desde=?, activa=?
                      WHERE id_cuenta=?'
                )->execute([$nombre, $usuario, $etiqueta, $autor, $limite, $desde_v, $activa, $id]);
            }
            flash('ok', 'Cuenta actualizada.');
        } else {
            $pdo->prepare(
                'INSERT INTO ig_cuentas (nombre, usuario_ig, etiqueta, id_usuario, limite_posts,
                                         importar_desde, activa, access_token, token_expira)
                 VALUES (?,?,?,?,?,?,?,?,?)'
            )->execute([$nombre, $usuario, $etiqueta, $autor, $limite, $desde_v, $activa,
                        $token, $token !== '' ? date('Y-m-d', strtotime('+60 days')) : null]);
            flash('ok', 'Cuenta creada.');
        }
        header('Location: gestion_instagram.php');
        exit;
    }

    // ── Borrar ──
    if ($accion === 'borrar') {
        $id = (int) ($_POST['id_cuenta'] ?? 0);
        $pdo->prepare('UPDATE novedades SET id_cuenta_ig = NULL WHERE id_cuenta_ig = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM ig_cuentas WHERE id_cuenta = ?')->execute([$id]);
        flash('ok', 'Cuenta eliminada. Las novedades que ya había importado se conservan.');
        header('Location: gestion_instagram.php');
        exit;
    }

    // ── Sincronizar ahora ──
    if ($accion === 'sincronizar') {
        require_once __DIR__ . '/ig_sync.php';
        @set_time_limit(300);
        $log = ig_sincronizar_todo('manual');
        flash($log->errores > 0 ? 'error' : 'ok',
              "Sincronización terminada: {$log->importadas} nueva/s, {$log->omitidas} omitida/s, {$log->errores} error/es.");
        $_SESSION['ig_ultimo_log'] = $log->texto();
        header('Location: gestion_instagram.php');
        exit;
    }
}

// ============================================================
//  DATOS PARA LA VISTA
// ============================================================
$cuentas = $tabla_ok
    ? $pdo->query('SELECT c.*, CONCAT(u.nombre," ",u.apellido) AS autor
                     FROM ig_cuentas c LEFT JOIN usuarios u ON u.id_usuario = c.id_usuario
                    ORDER BY c.nombre')->fetchAll()
    : [];

$usuarios = $pdo->query('SELECT id_usuario, nombre, apellido FROM usuarios ORDER BY apellido')->fetchAll();

$historial = [];
if ($tabla_ok) {
    try {
        $historial = $pdo->query(
            'SELECT * FROM ig_sync_log ORDER BY ejecutado_en DESC LIMIT 8'
        )->fetchAll();
    } catch (Throwable $ex) { /* sin historial todavía */ }
}

// Cuenta en edición
$edit = null;
if (isset($_GET['editar'])) {
    $st = $pdo->prepare('SELECT * FROM ig_cuentas WHERE id_cuenta = ?');
    $st->execute([(int) $_GET['editar']]);
    $edit = $st->fetch() ?: null;
}

$ultimo_log = $_SESSION['ig_ultimo_log'] ?? null;
unset($_SESSION['ig_ultimo_log']);

$simulando   = cfg('ig_simular', '1') === '1';
$url_tarea   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
             . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
             . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\')
             . '/sync_instagram.php?clave=' . $clave_tarea;

$panel_page_title = 'Instagram';
$panel_heading    = 'Cuentas de <span>Instagram</span>';
$panel_sub        = 'Cada cuenta publica automáticamente en su etiqueta de novedades. Los directivos suben a Instagram y el sitio se actualiza solo.';
require __DIR__ . '/panel_header.php';
?>

<?php if (!$tabla_ok): ?>
  <div class="alert alert-error">
    Falta crear las tablas. Ejecutá <strong>migracion_instagram.sql</strong> en phpMyAdmin
    (base <code>colegio_juan_xxiii</code>) y volvé a entrar.
  </div>
  <p style="margin-top:2rem;"><a href="panel.php" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg> Volver al panel</a></p>

<?php else: ?>

  <?php if ($simulando): ?>
    <div class="alert alert-info">
      <strong>Modo de prueba activo.</strong> Todavía no se consulta Instagram de verdad: se usan
      las publicaciones de ejemplo de <code>posts_simulados.json</code>, para que puedas ver cómo
      quedan las novedades. Cuando tengas los tokens cargados, poné <em>Modo de prueba</em> en
      <code>0</code> en <a href="gestion_contacto.php">Datos de contacto</a>.
      Los pasos para conseguirlos están en <code>GUIA_INSTAGRAM.md</code>.
    </div>
  <?php endif; ?>

  <!-- ── Estado de las cuentas ────────────────────────────── -->
  <div class="ig-barra">
    <h2 class="ig-h2">Cuentas configuradas</h2>
    <div class="ig-acciones">
      <form method="post" style="display:inline;">
        <?= csrf_input() ?>
        <input type="hidden" name="accion" value="sincronizar"/>
        <button type="submit" class="btn btn-primary">Sincronizar ahora</button>
      </form>
      <a href="gestion_instagram.php?editar=0" class="btn btn-secundario">Agregar cuenta</a>
    </div>
  </div>

  <?php if (!$cuentas): ?>
    <p class="ig-vacio">Todavía no hay cuentas cargadas. Agregá una para empezar.</p>
  <?php else: ?>
    <div class="ig-grid">
      <?php foreach ($cuentas as $c):
        $vence = $c['token_expira'] ? (int) floor((strtotime($c['token_expira']) - time()) / 86400) : null; ?>
        <article class="ig-card<?= $c['activa'] ? '' : ' is-off' ?>">
          <header class="ig-card-top">
            <div>
              <h3><?= e($c['nombre']) ?></h3>
              <p class="ig-user"><?= e($c['usuario_ig']) ?></p>
            </div>
            <span class="ig-etiqueta" style="background:<?= e(color_etiqueta_ig($c['etiqueta'])) ?>">
              <?= e($c['etiqueta']) ?>
            </span>
          </header>

          <dl class="ig-datos">
            <div><dt>Autor</dt><dd><?= e($c['autor'] ?? '—') ?></dd></div>
            <div><dt>Última sincronización</dt>
              <dd><?= $c['ultima_sync'] ? e(date('d/m/Y H:i', strtotime($c['ultima_sync']))) : 'nunca' ?></dd></div>
            <div><dt>Token</dt>
              <dd>
                <?php if (trim((string) $c['access_token']) === ''): ?>
                  <span class="ig-mal">sin cargar</span>
                <?php elseif ($vence === null): ?>
                  cargado
                <?php elseif ($vence < 0): ?>
                  <span class="ig-mal">vencido</span>
                <?php elseif ($vence < 10): ?>
                  <span class="ig-warn">vence en <?= $vence ?> días</span>
                <?php else: ?>
                  <span class="ig-bien">vence en <?= $vence ?> días</span>
                <?php endif; ?>
              </dd>
            </div>
          </dl>

          <?php if ($c['ultimo_estado'] === 'error'): ?>
            <p class="ig-error-msg"><?= e($c['ultimo_mensaje']) ?></p>
          <?php elseif ($c['ultimo_estado'] === 'ok'): ?>
            <p class="ig-ok-msg">Última corrida sin problemas.</p>
          <?php endif; ?>

          <footer class="ig-card-pie">
            <a href="gestion_instagram.php?editar=<?= (int) $c['id_cuenta'] ?>" class="ig-link">Editar</a>
            <a href="novedades.php?etiqueta=<?= urlencode($c['etiqueta']) ?>" target="_blank" rel="noopener" class="ig-link">Ver sus novedades</a>
            <form method="post" onsubmit="return confirm('¿Eliminar la cuenta «<?= e($c['nombre']) ?>»? Las novedades ya importadas se conservan.');">
              <?= csrf_input() ?>
              <input type="hidden" name="accion" value="borrar"/>
              <input type="hidden" name="id_cuenta" value="<?= (int) $c['id_cuenta'] ?>"/>
              <button type="submit" class="ig-link ig-link--del">Eliminar</button>
            </form>
          </footer>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- ── Formulario de alta / edición ─────────────────────── -->
  <?php if ($edit !== null || isset($_GET['editar'])): ?>
    <section class="ig-form-wrap" id="form">
      <h2 class="ig-h2"><?= $edit ? 'Editar: ' . e($edit['nombre']) : 'Agregar una cuenta' ?></h2>
      <form method="post" action="gestion_instagram.php#form">
        <?= csrf_input() ?>
        <input type="hidden" name="accion" value="guardar"/>
        <input type="hidden" name="id_cuenta" value="<?= (int) ($edit['id_cuenta'] ?? 0) ?>"/>

        <div class="ig-form-grid">
          <div class="field">
            <label for="f-nombre">Nombre</label>
            <input id="f-nombre" name="nombre" type="text" required
                   value="<?= e($edit['nombre'] ?? '') ?>" placeholder="Nivel Inicial"/>
            <small class="field-ayuda">Cómo la vas a reconocer en este panel.</small>
          </div>

          <div class="field">
            <label for="f-user">Usuario de Instagram</label>
            <input id="f-user" name="usuario_ig" type="text" required
                   value="<?= e($edit['usuario_ig'] ?? '') ?>" placeholder="@juanxxiii.inicial"/>
            <small class="field-ayuda">Solo informativo, para saber cuál es cuál.</small>
          </div>

          <div class="field">
            <label for="f-etiqueta">Etiqueta de las novedades</label>
            <select id="f-etiqueta" name="etiqueta">
              <?php foreach (ETIQUETAS_VALIDAS as $et): ?>
                <option value="<?= e($et) ?>" <?= ($edit['etiqueta'] ?? '') === $et ? 'selected' : '' ?>><?= e($et) ?></option>
              <?php endforeach; ?>
            </select>
            <small class="field-ayuda">Todo lo que publique esta cuenta entra con esta etiqueta.</small>
          </div>

          <div class="field">
            <label for="f-autor">Figura como autor</label>
            <select id="f-autor" name="id_usuario">
              <?php foreach ($usuarios as $u): ?>
                <option value="<?= (int) $u['id_usuario'] ?>" <?= (int) ($edit['id_usuario'] ?? 0) === (int) $u['id_usuario'] ? 'selected' : '' ?>>
                  <?= e($u['nombre'] . ' ' . $u['apellido']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <small class="field-ayuda">Usuario del panel al que se le atribuyen estas novedades.</small>
          </div>

          <div class="field">
            <label for="f-limite">Publicaciones a revisar</label>
            <input id="f-limite" name="limite_posts" type="number" min="1" max="50"
                   value="<?= (int) ($edit['limite_posts'] ?? 10) ?>"/>
            <small class="field-ayuda">Cuántas mira en cada corrida. Con 10 alcanza de sobra para una sincronización diaria.</small>
          </div>

          <div class="field">
            <label for="f-desde">No importar anteriores a</label>
            <input id="f-desde" name="importar_desde" type="date"
                   value="<?= e($edit['importar_desde'] ?? date('Y-m-d')) ?>"/>
            <small class="field-ayuda">Evita que la primera corrida traiga años de publicaciones viejas.</small>
          </div>

          <div class="field field--ancho">
            <label for="f-token">Token de acceso</label>
            <input id="f-token" name="access_token" type="text" autocomplete="off"
                   placeholder="<?= !empty($edit['access_token']) ? '•••••••• (ya cargado — dejalo vacío para no cambiarlo)' : 'IGQVJ...' ?>"/>
            <small class="field-ayuda">
              El token de larga duración que genera Meta. Se renueva solo mientras la tarea
              diaria siga corriendo. Los pasos para obtenerlo están en <code>GUIA_INSTAGRAM.md</code>.
            </small>
          </div>

          <div class="field field--ancho">
            <label class="ig-check">
              <input type="checkbox" name="activa" value="1" <?= ($edit === null || $edit['activa']) ? 'checked' : '' ?>/>
              Cuenta activa (se sincroniza automáticamente)
            </label>
          </div>
        </div>

        <div class="form-actions">
          <a href="gestion_instagram.php" class="btn btn-secundario">Cancelar</a>
          <button type="submit" class="btn btn-primary">Guardar cuenta</button>
        </div>
      </form>
    </section>
  <?php endif; ?>

  <!-- ── Tarea programada ─────────────────────────────────── -->
  <section class="ig-tarea">
    <h2 class="ig-h2">Sincronización automática</h2>
    <p>
      Para que las publicaciones aparezcan solas hay que programar una tarea diaria.
      En la PC del colegio con XAMPP, el Programador de tareas de Windows a las
      <strong>19:00</strong> ejecutando:
    </p>
    <pre class="ig-code">C:\xampp\php\php.exe -f "<?= e(str_replace('/', '\\', __DIR__)) ?>\sync_instagram.php"</pre>
    <p>Si el sitio está en un hosting con cron, la línea equivalente es:</p>
    <pre class="ig-code">0 19 * * *  curl -s "<?= e($url_tarea) ?>"</pre>
    <p class="ig-nota">
      Esa clave es lo único que permite correr la sincronización sin estar logueado.
      No la publiques. Si se filtra, borrala en Datos de contacto y entrá acá de nuevo:
      se genera una nueva.
    </p>
  </section>

  <!-- ── Historial ────────────────────────────────────────── -->
  <?php if ($ultimo_log): ?>
    <section class="ig-tarea">
      <h2 class="ig-h2">Detalle de la última corrida</h2>
      <pre class="ig-code ig-log"><?= e($ultimo_log) ?></pre>
    </section>
  <?php endif; ?>

  <?php if ($historial): ?>
    <section class="ig-tarea">
      <h2 class="ig-h2">Últimas sincronizaciones</h2>
      <table class="ig-tabla">
        <thead><tr><th>Cuándo</th><th>Origen</th><th>Nuevas</th><th>Omitidas</th><th>Errores</th></tr></thead>
        <tbody>
        <?php foreach ($historial as $h): ?>
          <tr class="<?= $h['errores'] > 0 ? 'fila-err' : '' ?>">
            <td><?= e(date('d/m/Y H:i', strtotime($h['ejecutado_en']))) ?></td>
            <td><?= e($h['origen']) ?></td>
            <td><?= (int) $h['importadas'] ?></td>
            <td><?= (int) $h['omitidas'] ?></td>
            <td><?= (int) $h['errores'] ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </section>
  <?php endif; ?>

<?php endif; ?>

<p style="margin-top:2.5rem;">
  <a href="panel.php" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg> Volver al panel
  </a>
</p>

<?php
function color_etiqueta_ig(string $e): string {
    return match ($e) {
        'Inicial'    => '#E63946',
        'Primario'   => '#1D3557',
        'Secundario' => '#2a9d8f',
        'Técnica'    => '#0d1b2a',
        'Orientada'  => '#457B9D',
        default      => '#6d6875',
    };
}
?>

<style>
  .ig-h2 { font-family: var(--font-display); font-size: 1.1rem; color: var(--blue-dark); margin: 0; }
  .ig-barra { display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin: 2rem 0 1.2rem; }
  .ig-acciones { display:flex; gap:.6rem; align-items:center; flex-wrap:wrap; }
  .ig-vacio { color: var(--gray-500); padding: 2rem; text-align:center; background:#fff; border-radius:12px; border:1px dashed rgba(29,53,87,.2); }

  .ig-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(310px,1fr)); gap:1.1rem; }
  .ig-card { background:#fff; border:1px solid rgba(29,53,87,.1); border-radius:14px;
             padding:1.2rem 1.3rem; box-shadow:0 2px 10px rgba(29,53,87,.05);
             display:flex; flex-direction:column; gap:.9rem; }
  .ig-card.is-off { opacity:.55; }
  .ig-card-top { display:flex; justify-content:space-between; gap:.8rem; align-items:flex-start; }
  .ig-card-top h3 { font-family:var(--font-display); font-size:1.05rem; color:var(--blue-dark); margin:0; }
  .ig-user { font-size:.82rem; color:var(--gray-500); margin:.15rem 0 0; }
  .ig-etiqueta { color:#fff; font-size:.68rem; font-weight:800; letter-spacing:.06em;
                 text-transform:uppercase; padding:.25rem .65rem; border-radius:999px; white-space:nowrap; }

  .ig-datos { display:flex; flex-direction:column; gap:.45rem; margin:0; font-size:.84rem; }
  .ig-datos > div { display:flex; justify-content:space-between; gap:1rem; }
  .ig-datos dt { color:var(--gray-500); margin:0; }
  .ig-datos dd { margin:0; font-weight:600; color:var(--blue-dark); text-align:right; }
  .ig-bien { color:#2a9d8f; } .ig-warn { color:#e07a00; } .ig-mal { color:#c1121f; }

  .ig-error-msg { font-size:.8rem; color:#c1121f; background:rgba(193,18,31,.07);
                  border-left:3px solid #c1121f; padding:.5rem .7rem; border-radius:0 6px 6px 0; margin:0; }
  .ig-ok-msg { font-size:.8rem; color:#2a9d8f; margin:0; }

  .ig-card-pie { display:flex; gap:1rem; align-items:center; flex-wrap:wrap;
                 border-top:1px solid rgba(29,53,87,.08); padding-top:.8rem; margin-top:auto; }
  .ig-card-pie form { margin:0; }
  .ig-link { font-size:.82rem; font-weight:700; color:var(--blue-mid);
             background:none; border:none; padding:0; cursor:pointer; font-family:inherit; }
  .ig-link:hover { text-decoration:underline; }
  .ig-link--del { color:#c1121f; }

  .ig-form-wrap, .ig-tarea { background:#fff; border:1px solid rgba(29,53,87,.1); border-radius:14px;
                             padding:1.4rem 1.6rem 1.6rem; margin-top:2rem; }
  .ig-form-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
                  gap:1.1rem 1.4rem; margin-top:1.2rem; }
  .ig-form-grid .field--ancho { grid-column:1 / -1; }
  .field-ayuda { display:block; margin-top:.3rem; font-size:.74rem; color:var(--gray-500); line-height:1.45; }
  .ig-check { display:flex; align-items:center; gap:.5rem; font-weight:600; cursor:pointer; }
  .ig-check input { width:auto; }

  .ig-tarea p { font-size:.88rem; line-height:1.65; color:#555; margin:.8rem 0 .5rem; }
  .ig-code { background:#0d1b2a; color:#8ecae6; padding:.9rem 1.1rem; border-radius:8px;
             font-size:.8rem; overflow-x:auto; white-space:pre-wrap; word-break:break-all; }
  .ig-log { color:#e6e6e6; max-height:340px; overflow-y:auto; }
  .ig-nota { font-size:.78rem !important; color:var(--gray-500) !important; }

  .ig-tabla { width:100%; border-collapse:collapse; margin-top:1rem; font-size:.84rem; }
  .ig-tabla th { text-align:left; color:var(--gray-500); font-weight:700; font-size:.72rem;
                 text-transform:uppercase; letter-spacing:.05em; padding:.5rem .6rem;
                 border-bottom:2px solid rgba(29,53,87,.1); }
  .ig-tabla td { padding:.55rem .6rem; border-bottom:1px solid rgba(29,53,87,.06); }
  .ig-tabla .fila-err td { background:rgba(193,18,31,.05); }

  @media (max-width:640px) {
    .ig-form-grid { grid-template-columns:1fr; }
    .ig-barra { flex-direction:column; align-items:stretch; }
  }
</style>
