<?php
// ============================================================
//  conexion.php — ÚNICA fuente de conexión a la base
//  Colegio Parroquial Juan XXIII
// ------------------------------------------------------------
//  Todo el sitio (público y panel) incluye este archivo.
//  Antes las credenciales estaban repetidas en config.php,
//  panel_config.php, agenda.php, novedades.php y egresados.php.
//
//  Para cambiar las credenciales en producción NO se toca este
//  archivo: se crea `config.local.php` al lado, con la forma
//
//      <?php return ['host'=>'...','name'=>'...','user'=>'...','pass'=>'...'];
//
//  y queda fuera del control de versiones (.gitignore).
// ============================================================

declare(strict_types=1);

$__db = is_file(__DIR__ . '/config.local.php')
    ? (array) require __DIR__ . '/config.local.php'
    : [];

define('DB_HOST',    $__db['host'] ?? 'localhost');
define('DB_NAME',    $__db['name'] ?? 'colegio_juan_xxiii');
define('DB_USER',    $__db['user'] ?? 'root');
define('DB_PASS',    $__db['pass'] ?? '');
define('DB_CHARSET', 'utf8mb4');

// true en el XAMPP local, false en producción (muestra errores genéricos)
define('SITIO_DEBUG', ($__db['debug'] ?? null) !== null
    ? (bool) $__db['debug']
    : in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', 'localhost:80'], true));

unset($__db);

/** Conexión PDO compartida (se abre una sola vez por petición). */
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
                DB_USER, DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $ex) {
            error_log('[juan23] Error de conexión: ' . $ex->getMessage());
            $detalle = SITIO_DEBUG ? htmlspecialchars($ex->getMessage()) : '';
            http_response_code(503);
            die('<p style="font-family:sans-serif;padding:2rem;color:#c1121f;">'
                . 'No se pudo conectar con la base de datos. Probá de nuevo en unos minutos.'
                . ($detalle ? '<br><small>' . $detalle . '</small>' : '')
                . '</p>');
        }
    }
    return $pdo;
}

/**
 * Igual que db(), pero devuelve null en vez de cortar la página si la base
 * no está disponible. Lo usa cfg(): así, si MySQL se cae, las páginas
 * informativas (historia, becas, contacto…) siguen viéndose con los datos
 * por defecto en vez de mostrar un 503 en todo el sitio.
 */
function db_opcional(): ?PDO {
    static $pdo = false;
    if ($pdo !== false) return $pdo;
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
            DB_USER, DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 3,
            ]
        );
    } catch (PDOException $ex) {
        error_log('[juan23] cfg() sin base: ' . $ex->getMessage());
        $pdo = null;
    }
    return $pdo;
}

/** Escape para HTML. */
if (!function_exists('e')) {
    function e(?string $s): string {
        return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// ============================================================
//  CONFIGURACIÓN DEL SITIO (tabla `configuracion`)
//  Editable desde el panel → gestion_contacto.php
// ============================================================

/** Valores por defecto si la tabla todavía no existe o la clave falta. */
const CFG_DEFAULTS = [
    'nombre_colegio'    => 'Colegio Parroquial Juan XXIII',
    'lema'              => 'Educando desde 1962',
    'pie_descripcion'   => 'Formando profesionales desde 1962.',
    'direccion'         => 'Av. Rivadavia 1234',
    'localidad'         => 'Haedo, Buenos Aires',
    'codigo_postal'     => 'B1706',
    'telefono'          => '(011) 1234-5678',
    'telefono_link'     => '+541112345678',
    'telefono_alt'      => '',
    'telefono_alt_link' => '',
    'whatsapp'          => '',
    'whatsapp_link'     => '',
    'email'             => 'info@juanxxiii.edu.ar',
    'email_admisiones'  => 'admisiones@juanxxiii.edu.ar',
    'horario_atencion'  => 'Lunes a viernes de 8:00 a 16:00 h',
    'horario_admision'  => 'Lunes a viernes de 9:00 a 13:00 h',
    'mapa_embed'        => '',
    'mapa_link'         => '',
    'facebook'          => '',
    'instagram'         => '',
    'youtube'           => '',
    'url_xhendra'       => '#',
    'anio_fundacion'    => '1962',
];

/**
 * Devuelve un valor de configuración del sitio.
 * Hace UNA sola consulta por petición y cachea todo en memoria.
 * Si la tabla no existe todavía, usa CFG_DEFAULTS (el sitio nunca se rompe).
 */
function cfg(string $clave, ?string $default = null): string {
    static $valores = null;
    if ($valores === null) {
        $valores = [];
        $pdo = db_opcional();
        if ($pdo !== null) {
            try {
                foreach ($pdo->query('SELECT clave, valor FROM configuracion') as $fila) {
                    $valores[$fila['clave']] = (string) $fila['valor'];
                }
            } catch (Throwable $ex) {
                // Tabla aún no migrada: seguimos con los valores por defecto.
            }
        }
    }
    $v = $valores[$clave] ?? null;
    if ($v === null || $v === '') {
        return $default ?? (CFG_DEFAULTS[$clave] ?? '');
    }
    return $v;
}

/** Igual que cfg() pero escapado para HTML. Es el que se usa en las vistas. */
function cfg_e(string $clave, ?string $default = null): string {
    return e(cfg($clave, $default));
}
