<?php
// ============================================================
//  panel_config.php
//  Colegio Parroquial Juan XXIII — Núcleo del Panel de Control
//  Incluí este archivo AL PRINCIPIO de cada página del panel:
//      require_once __DIR__ . '/panel_config.php';
// ============================================================

declare(strict_types=1);

// ── Sesión (cookie httponly, más segura) ──
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ── Conexión y helpers: fuente única en conexion.php ──
require_once __DIR__ . '/conexion.php';

// ── Roles y permisos ──
const ROLES_VALIDOS = ['admin', 'directivo', 'docente', 'secretaria'];

/** Etiqueta legible del rol */
function rol_label(string $rol): string {
    return match ($rol) {
        'admin'      => 'Administrador',
        'directivo'  => 'Directivo',
        'docente'    => 'Profesor',
        'secretaria' => 'Secretaría',
        default      => ucfirst($rol),
    };
}

// ── Estado de sesión ──
function esta_logueado(): bool {
    return isset($_SESSION['usuario']['id_usuario']);
}

function usuario_actual(): ?array {
    return $_SESSION['usuario'] ?? null;
}

function es_admin(): bool {
    // El nivel de permisos ES el rol: manda el flag es_admin del nivel.
    $n = nivel_actual();
    if ($n !== null && array_key_exists('es_admin', $n)) {
        return (int)$n['es_admin'] === 1;
    }
    // Fallback legado (migraciones de permisos aún no ejecutadas)
    return (usuario_actual()['rol'] ?? '') === 'admin';
}

/** Nombre del nivel del usuario logueado (para mostrar en el header) */
function nivel_nombre_actual(): string {
    $n = nivel_actual();
    if ($n) return (string)$n['nombre'];
    if (es_admin()) return 'Administrador';
    return 'Sin nivel asignado';
}

/**
 * ¿El usuario actual puede editar/borrar un contenido creado por $id_autor?
 * Regla: el admin puede con todo; el resto solo con lo propio.
 */
function puede_gestionar(int $id_autor): bool {
    if (!esta_logueado()) return false;
    if (es_admin()) return true;
    return (int)(usuario_actual()['id_usuario']) === $id_autor;
}

// ── Guardias de acceso ──
/** Exige estar logueado; si no, redirige al login */
function exigir_login(): void {
    if (!esta_logueado()) {
        header('Location: login.php');
        exit;
    }
}

/** Exige rol admin; si no, corta con 403 */
function exigir_admin(): void {
    exigir_login();
    if (!es_admin()) {
        http_response_code(403);
        die('<p style="font-family:sans-serif;padding:2rem;color:#c1121f;">Acceso denegado: se requiere rol de administrador.</p>');
    }
}

// ── CSRF (token anti-falsificación de formularios) ──
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_input(): string {
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars(csrf_token()) . '">';
}

function csrf_check(): void {
    $ok = isset($_POST['csrf'])
        && is_string($_POST['csrf'])
        && hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf']);
    if (!$ok) {
        http_response_code(400);
        die('<p style="font-family:sans-serif;padding:2rem;color:#c1121f;">Solicitud inválida (token CSRF). Volvé atrás y reintentá.</p>');
    }
}

// ── Helpers varios ──  (e() vive en conexion.php)

/** Flash message entre redirecciones */
function flash(string $tipo, string $msg): void {
    $_SESSION['flash'] = ['tipo' => $tipo, 'msg' => $msg];
}
function flash_get(): ?array {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

// ── Constantes de contenido (coinciden con el esquema) ──
const ETIQUETAS_VALIDAS = ['Inicial', 'Primario', 'Secundario', 'Técnica', 'Orientada', 'Global'];
const TIPOS_EVENTO      = ['Acto', 'Formulario', 'Reunión', 'Examen', 'Feriado', 'Inscripción', 'Otro'];

// ── Niveles de permiso (tabla niveles_permiso) ──
/**
 * Devuelve el nivel de permisos del usuario logueado (fila completa
 * de niveles_permiso) o null si no tiene nivel asignado.
 * Cachea el resultado para no repetir la consulta en la misma petición.
 */
function nivel_actual(): ?array {
    static $cache = false;              // false = todavía no consultado
    if ($cache !== false) return $cache;
    $cache = null;
    if (!esta_logueado()) return null;
    try {
        $st = db()->prepare(
            'SELECT n.* FROM usuarios u
              JOIN niveles_permiso n ON n.id_nivel = u.id_nivel
             WHERE u.id_usuario = ?'
        );
        $st->execute([(int)usuario_actual()['id_usuario']]);
        $cache = $st->fetch() ?: null;
    } catch (Throwable $e) {
        $cache = null;                  // tabla/columna aún no migradas
    }
    return $cache;
}

/**
 * ¿El usuario actual tiene el permiso pedido?
 *   puede('pub_novedades')             → flag activado
 *   puede('pub_novedades', 'Primario') → flag + categoría habilitada
 * Permisos válidos: pub_novedades, pub_agenda, edita_tour,
 *                   alta_usuarios, inscripciones.
 * El admin siempre puede todo.
 */
function puede(string $permiso, ?string $categoria = null): bool {
    if (es_admin()) return true;
    $n = nivel_actual();
    if (!$n || empty($n[$permiso])) return false;
    if ($categoria === null) return true;
    $campoCats = $permiso === 'pub_novedades' ? 'cat_novedades'
               : ($permiso === 'pub_agenda'   ? 'cat_agenda' : null);
    if ($campoCats === null) return true;   // permiso sin categorías
    $cats = array_map('trim', explode(',', (string)($n[$campoCats] ?? '')));
    return in_array($categoria, $cats, true);
}

/**
 * Categorías habilitadas para un permiso ('pub_novedades' o 'pub_agenda').
 * Admin → todas. Sin nivel o permiso apagado → ninguna.
 * Útil para armar los <select> de etiqueta en novedades/agenda.
 */
function categorias_permitidas(string $permiso): array {
    if (es_admin()) return ETIQUETAS_VALIDAS;
    return array_values(array_filter(
        ETIQUETAS_VALIDAS,
        fn($c) => puede($permiso, $c)
    ));
}
