<?php
// ============================================================
//  partials/mensajes.php
//  Núcleo del módulo de mensajes de contacto, compartido por:
//    - la página pública contacto.php (guarda el mensaje)
//    - el panel: gestion_mensajes.php (bandeja de entrada)
//
//  Tabla: mensajes_contacto (se crea sola; ver también
//  migracion_mensajes.sql). También agrega la columna `mensajes`
//  a niveles_permiso para el permiso "Mensajes de contacto".
// ============================================================

require_once __DIR__ . '/../conexion.php';

// ── Clasificación ───────────────────────────────────────────
//  Asunto: lo elige quien escribe (el panel lo puede corregir).
const MSJ_ASUNTOS = ['Consulta general', 'Inscripciones', 'Becas', 'Administración', 'Otro'];

//  Nivel por el que consulta (opcional en el formulario).
const MSJ_NIVELES = ['Inicial', 'Primario', 'Secundario', 'General'];

//  Estado del seguimiento. Archivado = no aparece en la bandeja.
const MSJ_ESTADOS = ['Nuevo', 'Leído', 'Respondido', 'Archivado'];

const MSJ_COLOR_ESTADO = [
    'Nuevo'      => '#E63946',
    'Leído'      => '#457B9D',
    'Respondido' => '#1b7a44',
    'Archivado'  => '#9aa0a6',
];

const MSJ_COLOR_ASUNTO = [
    'Consulta general' => '#1D3557',
    'Inscripciones'    => '#E63946',
    'Becas'            => '#e09f3e',
    'Administración'   => '#457B9D',
    'Otro'             => '#6d6875',
];

/** Crea la tabla (y la columna de permiso) si faltan. Devuelve false si no pudo. */
function msj_asegurar_tabla(PDO $pdo): bool {
    static $ok = null;
    if ($ok !== null) return $ok;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS mensajes_contacto (
            id_mensaje  INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            nombre      VARCHAR(120) NOT NULL,
            email       VARCHAR(160) NOT NULL,
            telefono    VARCHAR(40)  NOT NULL DEFAULT '',
            asunto      ENUM('Consulta general','Inscripciones','Becas','Administración','Otro') NOT NULL DEFAULT 'Consulta general',
            nivel       ENUM('Inicial','Primario','Secundario','General') NOT NULL DEFAULT 'General',
            mensaje     TEXT         NOT NULL,
            estado      ENUM('Nuevo','Leído','Respondido','Archivado') NOT NULL DEFAULT 'Nuevo',
            destacado   TINYINT(1)   NOT NULL DEFAULT 0,
            notas       TEXT         NULL,
            id_usuario  INT UNSIGNED NULL COMMENT 'Último usuario que lo gestionó',
            ip          VARCHAR(45)  NOT NULL DEFAULT '',
            creado_en   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
            actualizado TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_estado (estado),
            KEY idx_asunto (asunto),
            KEY idx_fecha  (creado_en),
            KEY idx_ip     (ip, creado_en)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Permiso "Mensajes de contacto" en los niveles (si la tabla existe)
        try {
            if (!$pdo->query("SHOW COLUMNS FROM niveles_permiso LIKE 'mensajes'")->fetch()) {
                $pdo->exec("ALTER TABLE niveles_permiso ADD COLUMN mensajes TINYINT(1) NOT NULL DEFAULT 0");
            }
        } catch (Throwable $ex) {
            // niveles_permiso aún no migrada: solo el admin verá los mensajes
        }
        $ok = true;
    } catch (Throwable $ex) {
        error_log('[juan23] mensajes_contacto: ' . $ex->getMessage());
        $ok = false;
    }
    return $ok;
}

/**
 * Valida lo enviado desde contacto.php.
 * Devuelve ['valores' => [...], 'errores' => [campo => texto]].
 */
function msj_procesar(array $post): array {
    $t = fn(string $k, int $max): string => mb_substr(trim((string) ($post[$k] ?? '')), 0, $max);
    $v = [
        'nombre'   => $t('nombre', 120),
        'email'    => $t('email', 160),
        'telefono' => $t('telefono', 40),
        'asunto'   => $t('asunto', 40),
        'nivel'    => $t('nivel', 20),
        'mensaje'  => $t('mensaje', 4000),
    ];
    $err = [];
    if (mb_strlen($v['nombre']) < 3)                       $err['nombre']   = 'Escribí tu nombre y apellido.';
    if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL))   $err['email']    = 'Revisá el correo electrónico.';
    if ($v['telefono'] !== '' && !preg_match('/^[0-9+\s()\-]{6,25}$/', $v['telefono']))
                                                           $err['telefono'] = 'El teléfono solo admite números, +, espacios, guiones y paréntesis.';
    if (!in_array($v['asunto'], MSJ_ASUNTOS, true))        $err['asunto']   = 'Elegí un asunto.';
    if ($v['nivel'] === '') $v['nivel'] = 'General';
    if (!in_array($v['nivel'], MSJ_NIVELES, true))         $err['nivel']    = 'Elegí un nivel.';
    if (mb_strlen($v['mensaje']) < 10)                     $err['mensaje']  = 'Contanos un poco más sobre tu consulta.';
    return ['valores' => $v, 'errores' => $err];
}

function msj_guardar(PDO $pdo, array $v, string $ip): int {
    $pdo->prepare('INSERT INTO mensajes_contacto (nombre, email, telefono, asunto, nivel, mensaje, ip)
                   VALUES (?, ?, ?, ?, ?, ?, ?)')
        ->execute([$v['nombre'], $v['email'], $v['telefono'], $v['asunto'], $v['nivel'], $v['mensaje'], $ip]);
    return (int) $pdo->lastInsertId();
}

/** Anti-abuso: máximo 5 mensajes cada 15 minutos por conexión. */
function msj_limite_superado(PDO $pdo, string $ip): bool {
    $st = $pdo->prepare('SELECT COUNT(*) FROM mensajes_contacto WHERE ip = ? AND creado_en > (NOW() - INTERVAL 15 MINUTE)');
    $st->execute([$ip]);
    return (int) $st->fetchColumn() >= 5;
}

/** Columnas para msj_fecha_corta(): la antigüedad se calcula en MySQL,
 *  porque la zona horaria de PHP puede no coincidir con la de la base. */
const MSJ_SQL_ANTIGUEDAD = 'TIMESTAMPDIFF(SECOND, creado_en, NOW()) AS seg_hace, DATEDIFF(CURDATE(), DATE(creado_en)) AS dias_hace';

/** "hace 5 min", "hoy 14:30", "ayer 14:30", "12/03/2026". */
function msj_fecha_corta(array $fila): string {
    $t    = strtotime($fila['creado_en']);
    $seg  = (int) $fila['seg_hace'];
    $dias = (int) $fila['dias_hace'];
    if ($seg < 60)   return 'recién';
    if ($seg < 3600) return 'hace ' . intdiv($seg, 60) . ' min';
    if ($dias === 0) return 'hoy ' . date('H:i', $t);
    if ($dias === 1) return 'ayer ' . date('H:i', $t);
    return date('d/m/Y', $t);
}
