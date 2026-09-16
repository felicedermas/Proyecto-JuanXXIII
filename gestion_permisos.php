<?php
require_once __DIR__ . '/panel_config.php';
exigir_admin();   // ← solo administradores

$u   = usuario_actual();
$pdo = db();

// Categorías válidas para novedades y agenda (mismo enum del sitio)
$CATS = ETIQUETAS_VALIDAS;   // ['Inicial','Primario','Secundario','Técnica','Orientada','Global']

/** Convierte el CSV guardado en la base en un array limpio */
function cats_de_csv(string $csv): array {
    global $CATS;
    return array_values(array_intersect(
        array_map('trim', explode(',', $csv)),
        $CATS
    ));
}

// ¿Existe ya la columna es_admin? (migracion_unificar_roles.sql)
try {
    $tiene_es_admin = (bool)$pdo->query(
        "SELECT COUNT(*) FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'niveles_permiso' AND COLUMN_NAME = 'es_admin'"
    )->fetchColumn();
} catch (Throwable $e) {
    $tiene_es_admin = false;
}

// ── Borrado ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'borrar') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    // No borrar niveles que estén asignados a usuarios (los dejaría sin rol)
    $enUso = 0;
    try {
        $st = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE id_nivel = ?');
        $st->execute([$id]);
        $enUso = (int)$st->fetchColumn();
    } catch (Throwable $e) { /* columna id_nivel aún no migrada */ }
    if ($enUso > 0) {
        flash('error', "No se puede eliminar: hay $enUso usuario" . ($enUso === 1 ? '' : 's')
                     . " con este nivel asignado. Cambiales el nivel primero desde Usuarios.");
    } else {
        $pdo->prepare('DELETE FROM niveles_permiso WHERE id_nivel = ?')->execute([$id]);
        flash('ok', 'Nivel de permiso eliminado correctamente.');
    }
    header('Location: gestion_permisos.php');
    exit;
}

// ── Crear / editar nivel ──
$errores = [];
$val = [
    'id_nivel'      => 0,
    'nombre'        => '',
    'es_admin'      => 0,
    'pub_novedades' => 0, 'cat_novedades' => [],
    'pub_agenda'    => 0, 'cat_agenda'    => [],
    'edita_tour'    => 0,
    'alta_usuarios' => 0,
    'inscripciones' => 0,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'guardar') {
    csrf_check();
    $val['id_nivel']      = (int)($_POST['id_nivel'] ?? 0);
    $val['nombre']        = trim((string)($_POST['nombre'] ?? ''));
    $val['es_admin']      = ($tiene_es_admin && isset($_POST['es_admin'])) ? 1 : 0;
    $val['pub_novedades'] = isset($_POST['pub_novedades']) ? 1 : 0;
    $val['pub_agenda']    = isset($_POST['pub_agenda'])    ? 1 : 0;
    $val['edita_tour']    = isset($_POST['edita_tour'])    ? 1 : 0;
    $val['alta_usuarios'] = isset($_POST['alta_usuarios']) ? 1 : 0;
    $val['inscripciones'] = isset($_POST['inscripciones']) ? 1 : 0;

    // Acceso total: habilita todo automáticamente
    if ($val['es_admin'] === 1) {
        $val['pub_novedades'] = $val['pub_agenda'] = $val['edita_tour'] = 1;
        $val['alta_usuarios'] = $val['inscripciones'] = 1;
        $_POST['cat_novedades'] = $CATS;
        $_POST['cat_agenda']    = $CATS;
    }

    // Categorías: solo valen si el permiso está activado
    $val['cat_novedades'] = $val['pub_novedades']
        ? array_values(array_intersect((array)($_POST['cat_novedades'] ?? []), $CATS)) : [];
    $val['cat_agenda'] = $val['pub_agenda']
        ? array_values(array_intersect((array)($_POST['cat_agenda'] ?? []), $CATS)) : [];

    if ($val['nombre'] === '') $errores[] = 'El nombre del nivel es obligatorio.';
    if ($val['pub_novedades'] && !$val['cat_novedades'])
        $errores[] = 'Seleccioná al menos una categoría para la publicación de novedades.';
    if ($val['pub_agenda'] && !$val['cat_agenda'])
        $errores[] = 'Seleccioná al menos una categoría para la publicación de agenda.';

    // Nombre único (excluyendo el propio registro si es edición)
    if (!$errores) {
        $ex = $pdo->prepare('SELECT COUNT(*) FROM niveles_permiso WHERE nombre = ? AND id_nivel <> ?');
        $ex->execute([$val['nombre'], $val['id_nivel']]);
        if ((int)$ex->fetchColumn() > 0) $errores[] = 'Ya existe un nivel con ese nombre.';
    }

    // Anti-bloqueo: no podés quitarle el acceso total al nivel que tenés vos
    if (!$errores && $tiene_es_admin && $val['id_nivel'] > 0 && $val['es_admin'] === 0) {
        $miNivel = (int)(nivel_actual()['id_nivel'] ?? 0);
        if ($miNivel === $val['id_nivel']) {
            $errores[] = 'No podés quitarle el acceso total a tu propio nivel: te quedarías sin acceso al panel.';
        }
    }

    if (!$errores) {
        $params = [
            ':n'  => $val['nombre'],
            ':pn' => $val['pub_novedades'], ':cn' => implode(',', $val['cat_novedades']),
            ':pa' => $val['pub_agenda'],    ':ca' => implode(',', $val['cat_agenda']),
            ':t'  => $val['edita_tour'],
            ':au' => $val['alta_usuarios'],
            ':i'  => $val['inscripciones'],
        ];
        $setAdmin = $tiene_es_admin ? ', es_admin=:adm'  : '';
        $colAdmin = $tiene_es_admin ? ', es_admin'       : '';
        $valAdmin = $tiene_es_admin ? ', :adm'           : '';
        if ($tiene_es_admin) $params[':adm'] = $val['es_admin'];

        if ($val['id_nivel'] > 0) {
            $params[':id'] = $val['id_nivel'];
            $pdo->prepare(
                "UPDATE niveles_permiso
                    SET nombre=:n, pub_novedades=:pn, cat_novedades=:cn,
                        pub_agenda=:pa, cat_agenda=:ca, edita_tour=:t,
                        alta_usuarios=:au, inscripciones=:i$setAdmin
                  WHERE id_nivel=:id"
            )->execute($params);
            // Sincronizar el rol legado de los usuarios que tienen este nivel
            if ($tiene_es_admin) {
                $pdo->prepare('UPDATE usuarios SET rol = ? WHERE id_nivel = ?')
                    ->execute([$val['es_admin'] ? 'admin' : 'docente', $val['id_nivel']]);
            }
            flash('ok', 'Nivel de permiso actualizado correctamente.');
        } else {
            $pdo->prepare(
                "INSERT INTO niveles_permiso
                   (nombre, pub_novedades, cat_novedades, pub_agenda, cat_agenda,
                    edita_tour, alta_usuarios, inscripciones$colAdmin)
                 VALUES (:n, :pn, :cn, :pa, :ca, :t, :au, :i$valAdmin)"
            )->execute($params);
            flash('ok', 'Nivel de permiso creado correctamente.');
        }
        header('Location: gestion_permisos.php');
        exit;
    }
}

// ── Modo edición: carga un nivel existente en el formulario ──
if (!$errores && isset($_GET['editar'])) {
    $st = $pdo->prepare('SELECT * FROM niveles_permiso WHERE id_nivel = ?');
    $st->execute([(int)$_GET['editar']]);
    if ($row = $st->fetch()) {
        $val = [
            'id_nivel'      => (int)$row['id_nivel'],
            'nombre'        => $row['nombre'],
            'es_admin'      => (int)($row['es_admin'] ?? 0),
            'pub_novedades' => (int)$row['pub_novedades'],
            'cat_novedades' => cats_de_csv($row['cat_novedades']),
            'pub_agenda'    => (int)$row['pub_agenda'],
            'cat_agenda'    => cats_de_csv($row['cat_agenda']),
            'edita_tour'    => (int)$row['edita_tour'],
            'alta_usuarios' => (int)$row['alta_usuarios'],
            'inscripciones' => (int)$row['inscripciones'],
        ];
    }
}
$editando = $val['id_nivel'] > 0;

// ── Listado ──
try {
    $lista = $pdo->query('SELECT * FROM niveles_permiso ORDER BY nombre ASC')->fetchAll();
} catch (Throwable $e) {
    die('<p style="font-family:sans-serif;padding:2rem;color:#c1121f;">Falta la tabla
         <strong>niveles_permiso</strong>. Ejecutá <code>migracion_permisos.sql</code> en phpMyAdmin.</p>');
}

$panel_page_title = 'Gestión de Permisos';
$panel_heading    = 'Gestión de <span>Permisos</span>';
$panel_sub        = 'Creá niveles de permiso con acceso granular a cada sección del panel.';
require __DIR__ . '/panel_header.php';
?>

<style>
  /* ── Fila de permiso, estilo tarjeta con tirita de "código" abajo ── */
  .perm-card{border:1px solid #e6e9ed;border-radius:var(--radius);overflow:hidden;margin-bottom:1rem}
  .perm-main{display:flex;align-items:center;justify-content:space-between;gap:1rem;
    background:#f4f6f8;padding:.85rem 1.1rem}
  .perm-name{font-family:var(--font-display);font-weight:700;color:var(--blue-dark);font-size:1.02rem}
  .perm-cats{display:flex;flex-wrap:wrap;gap:.5rem;padding:.8rem 1.1rem;background:#fff;
    border-top:1px dashed #e3e6ea}
  .perm-cats[hidden]{display:none}
  .perm-code{background:#12233d;color:#8fa3ba;font-family:'Consolas','Courier New',monospace;
    font-size:.74rem;padding:.4rem 1.1rem;letter-spacing:.02em}
  .perm-code b{color:#d7e3f0;font-weight:700}

  /* ── Toggle deslizante ✗ / ✓ (como server.properties) ── */
  .switch{position:relative;display:inline-block;flex-shrink:0;cursor:pointer}
  .switch input{position:absolute;opacity:0;width:0;height:0}
  .sw-track{display:block;width:64px;height:32px;border-radius:8px;background:#1d3557;
    border:2px solid #12233d;transition:background .25s;position:relative}
  .sw-thumb{position:absolute;top:2px;left:2px;width:28px;height:24px;border-radius:6px;
    background:#eef1f4;display:grid;place-items:center;
    transition:transform .25s cubic-bezier(.4,0,.2,1);box-shadow:0 1px 3px rgba(0,0,0,.35)}
  .switch input:checked + .sw-track .sw-thumb{transform:translateX(30px)}
  .sw-thumb svg{width:15px;height:15px;stroke-width:3.2;fill:none;stroke-linecap:round;stroke-linejoin:round}
  .sw-thumb .ic-x{stroke:#ef2d56;display:block}
  .sw-thumb .ic-ok{stroke:#2fbf71;display:none}
  .switch input:checked + .sw-track .sw-thumb .ic-x{display:none}
  .switch input:checked + .sw-track .sw-thumb .ic-ok{display:block}
  .switch input:focus-visible + .sw-track{box-shadow:0 0 0 3px rgba(69,123,157,.35)}

  /* ── Chips de categorías ── */
  .cat-chip{position:relative}
  .cat-chip input{position:absolute;opacity:0;width:0;height:0}
  .cat-chip span{display:inline-block;padding:.35rem .8rem;border-radius:999px;cursor:pointer;
    font-size:.8rem;font-weight:700;color:var(--blue-dark);background:#fff;
    border:1.5px solid #dfe3e8;transition:var(--transition);user-select:none}
  .cat-chip input:checked + span{background:var(--blue-dark);border-color:var(--blue-dark);color:#fff}
  .cat-chip input:focus-visible + span{box-shadow:0 0 0 3px rgba(69,123,157,.25)}
  .cat-chip span:hover{border-color:var(--blue-mid)}

  /* ── Mini indicadores ✓/✗ en la tabla ── */
  .perm-flag{display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;
    border-radius:7px;font-weight:800;font-size:.8rem}
  .perm-flag.on {background:rgba(47,191,113,.15);color:#1b7a44}
  .perm-flag.off{background:rgba(239,45,86,.12);color:#c1121f}
  .perm-cats-mini{display:block;font-size:.68rem;color:var(--gray-500);margin-top:.2rem}
</style>

    <div class="panel-toolbar">
      <a href="panel.php" class="back-link">
        <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg> Volver al panel
      </a>
    </div>

    <div class="form-card" style="margin-bottom:2.5rem;">
      <h2 style="font-family:var(--font-display);color:var(--blue-dark);font-size:1.35rem;margin-bottom:.4rem;">
        <?= $editando ? 'Editar nivel: ' . e($val['nombre']) : 'Nuevo nivel de permisos' ?>
      </h2>
      <p style="color:#667;font-size:.86rem;margin-bottom:1.3rem;">
        Activá cada permiso con el interruptor. En novedades y agenda podés limitar
        las <strong>categorías</strong> sobre las que el nivel puede publicar.
      </p>

      <?php if ($errores): ?>
        <div class="alert alert-error"><?= implode('<br>', array_map('e', $errores)) ?></div>
      <?php endif; ?>

      <form method="post" action="gestion_permisos.php" autocomplete="off">
        <?= csrf_input() ?>
        <input type="hidden" name="accion" value="guardar">
        <input type="hidden" name="id_nivel" value="<?= (int)$val['id_nivel'] ?>">

        <div class="field">
          <label for="nombre">Nombre del nivel <span class="hint">(ej.: "Editor de Primaria", "Secretaría plena")</span></label>
          <input type="text" id="nombre" name="nombre" maxlength="100" required value="<?= e($val['nombre']) ?>">
        </div>

        <?php
        // Definición de cada fila de permiso: [name, etiqueta, ¿tiene categorías?, clave del código]
        $filas = [];
        if ($tiene_es_admin) {
            $filas[] = ['es_admin', 'Administrador (acceso total)', false, 'acceso-total'];
        }
        $filas = array_merge($filas, [
            ['pub_novedades', 'Publicación de novedades',      true,  'novedades'],
            ['pub_agenda',    'Publicación de agenda',         true,  'agenda'],
            ['edita_tour',    'Edición del recorrido virtual', false, 'recorrido-360'],
            ['alta_usuarios', 'Alta de usuarios',              false, 'alta-usuarios'],
            ['inscripciones', 'Inscripciones',                 false, 'inscripciones'],
        ]);
        foreach ($filas as [$name, $label, $tieneCats, $code]):
            $on   = (int)$val[$name] === 1;
            $sel  = $tieneCats ? $val['cat_' . explode('_', $name)[1]] : [];
        ?>
        <div class="perm-card">
          <div class="perm-main">
            <div class="perm-name"><?= e($label) ?></div>
            <label class="switch">
              <input type="checkbox" name="<?= e($name) ?>" value="1" data-code="<?= e($code) ?>"
                     <?= $tieneCats ? 'data-cats="cats-' . e($name) . '"' : '' ?> <?= $on ? 'checked' : '' ?>>
              <span class="sw-track"><span class="sw-thumb">
                <svg class="ic-x"  viewBox="0 0 24 24"><line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/></svg>
                <svg class="ic-ok" viewBox="0 0 24 24"><polyline points="4 13 9 18 20 6"/></svg>
              </span></span>
            </label>
          </div>

          <?php if ($tieneCats): $catName = 'cat_' . explode('_', $name)[1]; ?>
          <div class="perm-cats" id="cats-<?= e($name) ?>" <?= $on ? '' : 'hidden' ?>>
            <?php foreach ($CATS as $c): ?>
              <label class="cat-chip">
                <input type="checkbox" name="<?= e($catName) ?>[]" value="<?= e($c) ?>"
                       <?= in_array($c, $sel, true) ? 'checked' : '' ?>>
                <span><?= e($c) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <div class="perm-code" data-code-for="<?= e($name) ?>"><b><?= e($code) ?></b>=<?= $on ? 'true' : 'false' ?></div>
        </div>
        <?php endforeach; ?>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">
            <?= $editando ? 'Guardar cambios' : 'Crear nivel de permisos' ?>
          </button>
          <?php if ($editando): ?>
            <a href="gestion_permisos.php" class="btn btn-outline">Cancelar edición</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <h2 style="font-family:var(--font-display);color:var(--blue-dark);font-size:1.25rem;margin-bottom:1rem;">
      Niveles creados
    </h2>

    <?php if (!$lista): ?>
      <div class="empty-state">
        <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        <p>Todavía no hay niveles de permiso. Creá el primero con el formulario de arriba.</p>
      </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
    <table class="mng-table">
      <thead>
        <tr>
          <th>Nombre</th><th>Novedades</th><th>Agenda</th>
          <th>Recorrido 360°</th><th>Usuarios</th><th>Inscripciones</th>
          <th style="text-align:right;">Acciones</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($lista as $row): ?>
        <tr>
          <td style="font-weight:700;color:var(--blue-dark);">
            <?= e($row['nombre']) ?>
            <?php if (!empty($row['es_admin'])): ?>
              <span class="mng-badge" style="background:#E63946;margin-left:.4rem;">Acceso total</span>
            <?php endif; ?>
          </td>
          <td>
            <span class="perm-flag <?= $row['pub_novedades'] ? 'on">✓' : 'off">✗' ?></span>
            <?php if ($row['pub_novedades']): ?>
              <span class="perm-cats-mini"><?= e(implode(', ', cats_de_csv($row['cat_novedades']))) ?></span>
            <?php endif; ?>
          </td>
          <td>
            <span class="perm-flag <?= $row['pub_agenda'] ? 'on">✓' : 'off">✗' ?></span>
            <?php if ($row['pub_agenda']): ?>
              <span class="perm-cats-mini"><?= e(implode(', ', cats_de_csv($row['cat_agenda']))) ?></span>
            <?php endif; ?>
          </td>
          <td><span class="perm-flag <?= $row['edita_tour']    ? 'on">✓' : 'off">✗' ?></span></td>
          <td><span class="perm-flag <?= $row['alta_usuarios'] ? 'on">✓' : 'off">✗' ?></span></td>
          <td><span class="perm-flag <?= $row['inscripciones'] ? 'on">✓' : 'off">✗' ?></span></td>
          <td>
            <div class="mng-actions" style="justify-content:flex-end;">
              <a href="gestion_permisos.php?editar=<?= (int)$row['id_nivel'] ?>" class="icon-btn" title="Editar nivel">
                <svg viewBox="0 0 24 24"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
              </a>
              <form method="post" action="gestion_permisos.php" style="display:inline;"
                    onsubmit="return confirm('¿Eliminar el nivel <?= e(addslashes($row['nombre'])) ?>?');">
                <?= csrf_input() ?>
                <input type="hidden" name="accion" value="borrar">
                <input type="hidden" name="id" value="<?= (int)$row['id_nivel'] ?>">
                <button type="submit" class="icon-btn danger" title="Eliminar nivel">
                  <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php endif; ?>

<script>
// ── Interacción de los toggles: mostrar categorías y actualizar la "tirita" ──
document.querySelectorAll('.switch input[type=checkbox]').forEach(sw => {
  const actualizar = () => {
    // Mostrar/ocultar el bloque de categorías (solo novedades y agenda)
    if (sw.dataset.cats) {
      document.getElementById(sw.dataset.cats).hidden = !sw.checked;
    }
    // Tirita inferior estilo "clave=valor"
    const code = document.querySelector(`[data-code-for="${sw.name}"]`);
    if (code) {
      let extra = '';
      if (sw.checked && sw.dataset.cats) {
        const cats = [...document.querySelectorAll(`#${sw.dataset.cats} input:checked`)]
                       .map(i => i.value).join(',');
        extra = cats ? ' · categorias=' + cats : '';
      }
      code.innerHTML = '<b>' + sw.dataset.code + '</b>=' + (sw.checked ? 'true' : 'false') + extra;
    }
  };
  sw.addEventListener('change', actualizar);
  // Las categorías también refrescan la tirita
  if (sw.dataset.cats) {
    document.querySelectorAll(`#${sw.dataset.cats} input`).forEach(c =>
      c.addEventListener('change', actualizar));
  }
  actualizar();   // estado inicial (útil en modo edición)
});

// ── "Acceso total": enciende todo y bloquea los demás toggles/categorías ──
const admSw = document.querySelector('.switch input[name="es_admin"]');
if (admSw) {
  const resto = [...document.querySelectorAll('.switch input[type=checkbox]')].filter(s => s !== admSw);
  const aplicarAdmin = () => {
    resto.forEach(s => {
      if (admSw.checked) {
        s.checked = true;
        s.dispatchEvent(new Event('change'));
      }
      s.disabled = admSw.checked;
      s.closest('.perm-card').style.opacity = admSw.checked ? '.55' : '1';
    });
    if (admSw.checked) {
      document.querySelectorAll('.perm-cats input').forEach(c => {
        c.checked = true;
        c.disabled = true;
      });
    } else {
      document.querySelectorAll('.perm-cats input').forEach(c => { c.disabled = false; });
    }
  };
  admSw.addEventListener('change', aplicarAdmin);
  aplicarAdmin();   // estado inicial (modo edición de un nivel admin)
}
</script>

  </div><!-- /.panel-wrap -->
</body>
</html>
