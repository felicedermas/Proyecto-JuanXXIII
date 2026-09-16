<?php
// ============================================================
//  ig_sync.php — Motor de sincronización con Instagram
//  Colegio Parroquial Juan XXIII
// ------------------------------------------------------------
//  Este archivo NO se abre directo: solo define funciones.
//  Lo usan:
//    · sync_instagram.php     (la corrida real: tarea o manual)
//    · gestion_instagram.php  (el panel)
//
//  Cómo funciona, en una línea: por cada cuenta activa pide a
//  Instagram sus últimas publicaciones, descarta las que ya
//  importó o las que llevan la palabra de exclusión, y crea una
//  novedad con el texto y las fotos, etiquetada según la cuenta.
// ============================================================

declare(strict_types=1);
require_once __DIR__ . '/conexion.php';

const IG_CARPETA_IMAGENES = 'img/novedades/instagram';
const IG_MAX_BYTES_IMAGEN = 8 * 1024 * 1024;   // 8 MB por foto
const IG_TIMEOUT          = 25;                 // segundos por request

// ============================================================
//  Registro de la corrida
// ============================================================
final class IgLog {
    public array $lineas = [];
    public int $importadas = 0;
    public int $omitidas   = 0;
    public int $errores    = 0;
    private bool $cli;

    public function __construct() {
        $this->cli = (PHP_SAPI === 'cli');
    }
    public function di(string $msg): void {
        $this->lineas[] = $msg;
        if ($this->cli) { echo $msg . PHP_EOL; }
    }
    public function texto(): string {
        return implode("\n", $this->lineas);
    }
}

// ============================================================
//  Llamadas HTTP a la API de Instagram
// ============================================================

/** GET a la Graph API. Devuelve el array decodificado o tira RuntimeException. */
function ig_get(string $url): array {
    if (!function_exists('curl_init')) {
        throw new RuntimeException('La extensión cURL de PHP no está activa (descomentá extension=curl en php.ini).');
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => IG_TIMEOUT,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT      => 'ColegioJuanXXIII/1.0',
    ]);
    $resp = curl_exec($ch);
    $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($resp === false) {
        throw new RuntimeException('Error de red: ' . $err);
    }
    $data = json_decode((string) $resp, true);
    if (!is_array($data)) {
        throw new RuntimeException('Instagram respondió algo que no es JSON (HTTP ' . $http . ').');
    }
    if ($http !== 200) {
        $msg = $data['error']['message'] ?? ('HTTP ' . $http);
        // Mensajes más claros para los errores típicos
        if (str_contains($msg, 'expired') || str_contains($msg, 'Session has expired')) {
            $msg = 'El token venció. Generá uno nuevo y cargalo en el panel.';
        } elseif (str_contains($msg, 'Invalid OAuth') || $http === 400) {
            $msg = 'Token inválido o cuenta sin permisos. Revisá que la cuenta sea Profesional. (' . $msg . ')';
        }
        throw new RuntimeException($msg);
    }
    return $data;
}

/**
 * Publicaciones de una cuenta.
 * En modo simulado lee posts_simulados.json, así se puede probar
 * todo el circuito sin token ni cuenta de empresa.
 */
function ig_traer_posts(array $cuenta, bool $simular, IgLog $log): array {
    if ($simular) {
        $ruta = __DIR__ . '/posts_simulados.json';
        if (!is_file($ruta)) {
            throw new RuntimeException('Modo de prueba activo pero falta posts_simulados.json');
        }
        $json = json_decode((string) file_get_contents($ruta), true);
        $posts = $json['data'] ?? [];
        // Para que cada cuenta simulada no choque con las otras,
        // le damos un id distinto por cuenta.
        foreach ($posts as $i => $p) {
            $posts[$i]['id'] = 'sim' . $cuenta['id_cuenta'] . '_' . ($p['id'] ?? $i);
        }
        $log->di('   (modo de prueba: leyendo posts_simulados.json)');
        return $posts;
    }

    if (trim((string) $cuenta['access_token']) === '') {
        throw new RuntimeException('La cuenta no tiene token cargado.');
    }

    $campos = 'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp,'
            . 'children{media_type,media_url,thumbnail_url}';
    $url = 'https://graph.instagram.com/me/media'
         . '?fields=' . urlencode($campos)
         . '&limit=' . max(1, (int) $cuenta['limite_posts'])
         . '&access_token=' . urlencode((string) $cuenta['access_token']);

    $data = ig_get($url);
    return $data['data'] ?? [];
}

/**
 * Renueva el token si le quedan menos de 10 días.
 * Los tokens de larga duración de Instagram duran 60 días y se
 * pueden renovar por otros 60 mientras no estén vencidos. Como la
 * tarea corre todos los días, nunca se deberían vencer solos.
 */
function ig_renovar_token(PDO $pdo, array $cuenta, IgLog $log): void {
    $token = trim((string) $cuenta['access_token']);
    if ($token === '') return;

    $expira = $cuenta['token_expira'] ?? null;
    if ($expira !== null) {
        $dias = (int) floor((strtotime($expira) - time()) / 86400);
        if ($dias > 10) return;             // todavía falta, no gastamos la llamada
        if ($dias < 0) {
            $log->di('   ⚠ El token ya venció: hay que generar uno nuevo a mano.');
            return;
        }
    }

    try {
        $data = ig_get('https://graph.instagram.com/refresh_access_token'
                     . '?grant_type=ig_refresh_token&access_token=' . urlencode($token));
        $nuevo = $data['access_token'] ?? '';
        $segs  = (int) ($data['expires_in'] ?? 0);
        if ($nuevo !== '') {
            $pdo->prepare('UPDATE ig_cuentas SET access_token = ?, token_expira = ? WHERE id_cuenta = ?')
                ->execute([$nuevo, date('Y-m-d', time() + $segs), (int) $cuenta['id_cuenta']]);
            $log->di('   ↻ Token renovado por ' . (int) round($segs / 86400) . ' días más.');
        }
    } catch (Throwable $ex) {
        $log->di('   ⚠ No se pudo renovar el token: ' . $ex->getMessage());
    }
}

// ============================================================
//  Armado de la novedad
// ============================================================

/** Título corto a partir del texto del post (primera frase, 65 caracteres). */
function ig_titulo(string $caption): string {
    $caption = trim($caption);
    if ($caption === '') return 'Publicación de Instagram';

    // Sacamos emojis y hashtags del arranque para que el título quede limpio
    $linea = preg_split('/[\r\n]|(?<=\.)\s/u', $caption)[0] ?? $caption;
    $linea = trim(preg_replace('/#\S+/u', '', $linea) ?? $linea);
    $linea = trim($linea, " \t\n\r\0\x0B-–—·|");

    if ($linea === '') return 'Publicación de Instagram';
    if (mb_strlen($linea) > 65) {
        $corte = mb_substr($linea, 0, 62);
        $esp   = mb_strrpos($corte, ' ');
        $linea = ($esp !== false ? mb_substr($corte, 0, $esp) : $corte) . '…';
    }
    return $linea;
}

/** Todas las URLs de imagen de un post (simple, carrusel o video con portada). */
function ig_urls_imagen(array $post): array {
    $urls = [];
    $tipo = $post['media_type'] ?? 'IMAGE';

    if ($tipo === 'CAROUSEL_ALBUM' && !empty($post['children']['data'])) {
        foreach ($post['children']['data'] as $hijo) {
            // De los videos del carrusel usamos la portada
            $u = ($hijo['media_type'] ?? '') === 'VIDEO'
               ? ($hijo['thumbnail_url'] ?? null)
               : ($hijo['media_url'] ?? null);
            if ($u) $urls[] = $u;
        }
    } elseif ($tipo === 'VIDEO') {
        // Reels y videos: se guarda la portada; el link al video queda en el permalink
        if (!empty($post['thumbnail_url'])) $urls[] = $post['thumbnail_url'];
    } elseif (!empty($post['media_url'])) {
        $urls[] = $post['media_url'];
    }
    return $urls;
}

/**
 * Baja una imagen de Instagram al servidor.
 * Es necesario: las URLs que devuelve la API caducan a las pocas
 * horas, así que enlazarlas directo dejaría las novedades sin foto.
 */
function ig_bajar_imagen(string $url, string $id_post, int $indice, IgLog $log): ?string {
    $dir = __DIR__ . '/' . IG_CARPETA_IMAGENES;
    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
        $log->di("   ⚠ No se pudo crear la carpeta " . IG_CARPETA_IMAGENES);
        return null;
    }
    if (!is_writable($dir)) {
        $log->di("   ⚠ Sin permiso de escritura en " . IG_CARPETA_IMAGENES);
        return null;
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => IG_TIMEOUT,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $bin  = curl_exec($ch);
    $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($bin === false || $http !== 200) {
        $log->di("   ⚠ No se pudo descargar una imagen (HTTP $http).");
        return null;
    }
    if (strlen($bin) > IG_MAX_BYTES_IMAGEN) {
        $log->di('   ⚠ Imagen demasiado grande, se omite.');
        return null;
    }

    // Validar que sea realmente una imagen antes de guardarla
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->buffer($bin);
    $exts  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($exts[$mime])) {
        $log->di("   ⚠ El archivo descargado no es una imagen ($mime).");
        return null;
    }

    $nombre = 'ig_' . preg_replace('/[^0-9a-zA-Z]/', '', $id_post)
            . '_' . $indice . '_' . bin2hex(random_bytes(3)) . '.' . $exts[$mime];

    if (file_put_contents($dir . '/' . $nombre, $bin) === false) {
        $log->di("   ⚠ No se pudo guardar $nombre");
        return null;
    }
    return IG_CARPETA_IMAGENES . '/' . $nombre;
}

// ============================================================
//  Sincronizar UNA cuenta
// ============================================================
function ig_sincronizar_cuenta(PDO $pdo, array $cuenta, IgLog $log): void {
    $simular  = cfg('ig_simular', '1') === '1';
    $excluir  = trim(cfg('ig_excluir', '#nolaweb'));
    $desde    = $cuenta['importar_desde'] ? strtotime($cuenta['importar_desde'] . ' 00:00:00') : 0;

    $log->di('');
    $log->di('▸ ' . $cuenta['nombre'] . ' (' . $cuenta['usuario_ig'] . ') → etiqueta «' . $cuenta['etiqueta'] . '»');

    if (!$simular) {
        ig_renovar_token($pdo, $cuenta, $log);
        // releer por si el token cambió
        $st = $pdo->prepare('SELECT * FROM ig_cuentas WHERE id_cuenta = ?');
        $st->execute([(int) $cuenta['id_cuenta']]);
        $cuenta = $st->fetch() ?: $cuenta;
    }

    $posts = ig_traer_posts($cuenta, $simular, $log);
    $log->di('   ' . count($posts) . ' publicación/es recibidas.');

    $existe = $pdo->prepare('SELECT id_novedad FROM novedades WHERE ig_post_id = ?');
    $insNov = $pdo->prepare(
        'INSERT INTO novedades
            (titulo, descripcion, etiqueta, origen, ig_post_id, ig_permalink, id_cuenta_ig, id_usuario, created_at)
         VALUES (:titulo, :desc, :etiqueta, "instagram", :post_id, :permalink, :cuenta, :usuario, :fecha)'
    );
    $insImg = $pdo->prepare(
        'INSERT INTO imagenes_novedades (id_novedad, url_imagen, orden) VALUES (?, ?, ?)'
    );

    foreach ($posts as $post) {
        $ig_id = (string) ($post['id'] ?? '');
        if ($ig_id === '') { $log->omitidas++; continue; }

        $caption = trim((string) ($post['caption'] ?? ''));
        $fecha_ts = isset($post['timestamp']) ? strtotime($post['timestamp']) : time();

        // ── Filtros ──
        if ($desde && $fecha_ts < $desde) {
            $log->omitidas++;
            continue;                                   // anterior a la fecha de corte
        }
        if ($excluir !== '' && mb_stripos($caption, $excluir) !== false) {
            $log->di('   – Se saltea una publicación por llevar «' . $excluir . '».');
            $log->omitidas++;
            continue;
        }
        $existe->execute([$ig_id]);
        if ($existe->fetch()) { $log->omitidas++; continue; }   // ya importada

        $titulo = ig_titulo($caption);
        $urls   = ig_urls_imagen($post);
        if (!$urls) {
            $log->di('   – «' . $titulo . '» no tiene imagen utilizable, se saltea.');
            $log->omitidas++;
            continue;
        }

        // ── Alta ──
        try {
            $pdo->beginTransaction();
            $insNov->execute([
                ':titulo'    => $titulo,
                ':desc'      => $caption !== '' ? $caption : $titulo,
                ':etiqueta'  => $cuenta['etiqueta'],
                ':post_id'   => $ig_id,
                ':permalink' => $post['permalink'] ?? null,
                ':cuenta'    => (int) $cuenta['id_cuenta'],
                ':usuario'   => (int) $cuenta['id_usuario'],
                ':fecha'     => date('Y-m-d H:i:s', $fecha_ts),
            ]);
            $id_novedad = (int) $pdo->lastInsertId();

            $orden = 0;
            foreach ($urls as $u) {
                $ruta = ig_bajar_imagen($u, $ig_id, $orden + 1, $log);
                if ($ruta) {
                    $orden++;
                    $insImg->execute([$id_novedad, $ruta, $orden]);
                }
            }

            if ($orden === 0) {
                // Sin ninguna foto la novedad queda pobre: mejor no dejarla a medias
                $pdo->rollBack();
                $log->di('   ✖ «' . $titulo . '»: no se pudo bajar ninguna imagen, se descarta.');
                $log->errores++;
                continue;
            }

            $pdo->commit();
            $log->di('   ✔ «' . $titulo . '» → novedad #' . $id_novedad . ' (' . $orden . ' foto/s)');
            $log->importadas++;

        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $log->di('   ✖ Error en el post ' . $ig_id . ': ' . $ex->getMessage());
            $log->errores++;
        }
    }
}

// ============================================================
//  Sincronizar TODAS las cuentas activas
// ============================================================
function ig_sincronizar_todo(string $origen = 'tarea'): IgLog {
    $pdo = db();
    $log = new IgLog();

    $log->di('Sincronización con Instagram — ' . date('d/m/Y H:i'));
    if (cfg('ig_simular', '1') === '1') {
        $log->di('⚠ MODO DE PRUEBA: no se consulta Instagram de verdad.');
        $log->di('  Cuando tengas los tokens, poné "Modo de prueba" en 0 (Panel → Datos de contacto).');
    }

    try {
        $cuentas = $pdo->query(
            'SELECT * FROM ig_cuentas WHERE activa = 1 ORDER BY nombre'
        )->fetchAll();
    } catch (Throwable $ex) {
        $log->di('✖ Falta ejecutar migracion_instagram.sql.');
        $log->errores++;
        return $log;
    }

    if (!$cuentas) {
        $log->di('No hay cuentas activas configuradas.');
        return $log;
    }

    foreach ($cuentas as $cuenta) {
        $antes_err = $log->errores;
        try {
            ig_sincronizar_cuenta($pdo, $cuenta, $log);
            $estado  = ($log->errores > $antes_err) ? 'error' : 'ok';
            $mensaje = ($estado === 'ok') ? 'Sincronizada correctamente.' : 'Terminó con errores.';
        } catch (Throwable $ex) {
            $log->di('   ✖ ' . $ex->getMessage());
            $log->errores++;
            $estado  = 'error';
            $mensaje = $ex->getMessage();
        }

        $pdo->prepare(
            'UPDATE ig_cuentas SET ultima_sync = NOW(), ultimo_estado = ?, ultimo_mensaje = ?
              WHERE id_cuenta = ?'
        )->execute([$estado, mb_substr($mensaje, 0, 400), (int) $cuenta['id_cuenta']]);
    }

    $log->di('');
    $log->di('─────────────────────────────────');
    $log->di("Resultado: {$log->importadas} novedad/es nueva/s · {$log->omitidas} omitida/s · {$log->errores} error/es");

    try {
        $pdo->prepare(
            'INSERT INTO ig_sync_log (origen, importadas, omitidas, errores, detalle)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([$origen, $log->importadas, $log->omitidas, $log->errores, $log->texto()]);
    } catch (Throwable $ex) {
        // el historial es accesorio: si falla, la sincronización igual sirvió
    }

    return $log;
}
