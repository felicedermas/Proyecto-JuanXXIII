<?php
// ============================================================
//  gestion_contacto.php
//  Panel de Control — Datos del colegio
//  Edita la tabla `configuracion`: todo lo que aparece en el
//  header, el pie, la página de contacto y la de ubicación.
//  Requiere haber ejecutado migracion_configuracion.sql.
// ============================================================
require_once __DIR__ . '/panel_config.php';
exigir_admin();

$pdo = db();

// ── ¿Está hecha la migración? ───────────────────────────────
$tabla_ok = true;
try {
    $pdo->query('SELECT 1 FROM configuracion LIMIT 1');
} catch (Throwable $ex) {
    $tabla_ok = false;
}

// ── Guardado ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tabla_ok) {
    csrf_check();

    $filas = $pdo->query('SELECT clave, tipo FROM configuracion')->fetchAll();
    $errores  = [];
    $cambios  = 0;
    $upd = $pdo->prepare('UPDATE configuracion SET valor = :v WHERE clave = :c');

    foreach ($filas as $fila) {
        $clave = $fila['clave'];
        if (!array_key_exists($clave, $_POST['cfg'] ?? [])) continue;

        $valor = trim((string) $_POST['cfg'][$clave]);

        // Validación según el tipo declarado en la tabla
        if ($valor !== '') {
            if ($fila['tipo'] === 'email' && !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                $errores[] = "«$clave»: no es un email válido.";
                continue;
            }
            if ($fila['tipo'] === 'url' && $valor !== '#' && !filter_var($valor, FILTER_VALIDATE_URL)) {
                $errores[] = "«$clave»: no es una URL válida (tiene que empezar con http:// o https://).";
                continue;
            }
            if ($fila['tipo'] === 'tel' && !preg_match('/^[0-9+\s()\-]{6,25}$/', $valor)) {
                $errores[] = "«$clave»: el teléfono solo admite números, +, espacios, guiones y paréntesis.";
                continue;
            }
        }

        $upd->execute([':v' => $valor, ':c' => $clave]);
        $cambios += $upd->rowCount();
    }

    if ($errores) {
        flash('error', 'No se guardó todo: ' . implode(' ', $errores));
    } else {
        flash('ok', $cambios > 0
            ? "Datos actualizados ($cambios campo" . ($cambios === 1 ? '' : 's') . ")."
            : 'No hubo cambios para guardar.');
    }
    header('Location: gestion_contacto.php');
    exit;
}

// ── Lectura agrupada ────────────────────────────────────────
$grupos = [];
if ($tabla_ok) {
    $filas = $pdo->query(
        'SELECT * FROM configuracion ORDER BY FIELD(grupo,"Identidad","Contacto","Ubicación","Horarios","Redes"), orden, clave'
    )->fetchAll();
    foreach ($filas as $f) {
        $grupos[$f['grupo']][] = $f;
    }
}

$panel_page_title = 'Datos del colegio';
$panel_heading    = 'Datos de <span>contacto</span>';
$panel_sub        = 'Lo que cambies acá se actualiza al instante en todo el sitio: header, pie, contacto y ubicación.';
require __DIR__ . '/panel_header.php';
?>

<?php if (!$tabla_ok): ?>
  <div class="alert alert-error">
    Todavía no está creada la tabla <code>configuracion</code>.
    Ejecutá <strong>migracion_configuracion.sql</strong> en phpMyAdmin (base
    <code>colegio_juan_xxiii</code>) y volvé a entrar a esta página.
  </div>
  <p style="margin-top:2rem;">
    <a href="panel.php" class="back-link">
      <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg> Volver al panel
    </a>
  </p>
<?php else: ?>

  <form method="post" action="gestion_contacto.php" class="cfg-form">
    <?= csrf_input() ?>

    <?php foreach ($grupos as $nombre_grupo => $campos): ?>
      <section class="cfg-grupo">
        <h2 class="cfg-grupo-titulo"><?= e($nombre_grupo) ?></h2>
        <div class="cfg-grid">
          <?php foreach ($campos as $c): ?>
            <div class="field<?= $c['tipo'] === 'textarea' ? ' field--ancho' : '' ?>">
              <label for="cfg_<?= e($c['clave']) ?>"><?= e($c['etiqueta']) ?></label>
              <?php if ($c['tipo'] === 'textarea'): ?>
                <textarea id="cfg_<?= e($c['clave']) ?>" name="cfg[<?= e($c['clave']) ?>]"
                          rows="3"><?= e($c['valor']) ?></textarea>
              <?php else: ?>
                <input type="<?= $c['tipo'] === 'email' ? 'email' : ($c['tipo'] === 'url' ? 'url' : 'text') ?>"
                       id="cfg_<?= e($c['clave']) ?>"
                       name="cfg[<?= e($c['clave']) ?>]"
                       value="<?= e($c['valor']) ?>"/>
              <?php endif; ?>
              <?php if ($c['ayuda'] !== ''): ?>
                <small class="field-ayuda"><?= e($c['ayuda']) ?></small>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endforeach; ?>

    <div class="form-actions">
      <a href="panel.php" class="btn btn-secundario">Cancelar</a>
      <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </div>
  </form>

  <p style="margin-top:2rem;">
    <a href="index.php" class="back-link" target="_blank" rel="noopener">
      <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
      Ver el sitio con los cambios aplicados
    </a>
  </p>

<?php endif; ?>

<style>
  .cfg-form { display: flex; flex-direction: column; gap: 2rem; }
  .cfg-grupo {
    background: #fff; border: 1px solid rgba(29,53,87,.1);
    border-radius: 14px; padding: 1.4rem 1.6rem 1.6rem;
    box-shadow: 0 2px 10px rgba(29,53,87,.05);
  }
  .cfg-grupo-titulo {
    font-family: var(--font-display); font-size: 1.05rem; color: var(--blue-dark);
    padding-bottom: .7rem; margin-bottom: 1.2rem;
    border-bottom: 2px solid var(--red);
    display: inline-block;
  }
  .cfg-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
    gap: 1.1rem 1.4rem;
  }
  .cfg-grid .field--ancho { grid-column: 1 / -1; }
  .field-ayuda {
    display: block; margin-top: .3rem; font-size: .74rem;
    color: var(--gray-500); line-height: 1.4;
  }
  @media (max-width: 640px) { .cfg-grid { grid-template-columns: 1fr; } }
</style>
