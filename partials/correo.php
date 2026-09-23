<?php
// ============================================================
//  partials/correo.php
//  Envío de correo del sitio (avisos automáticos).
//
//  Lo usa hoy el módulo de inscripciones: cuando una familia
//  termina un formulario recibe la confirmación por correo y la
//  Secretaría recibe el aviso de que entró una inscripción nueva.
//
//  Se apoya en mail() de PHP, que en el hosting sale por el
//  servidor de correo del dominio. En el XAMPP de desarrollo no
//  hay servidor de correo: ahí el envío se REGISTRA en el log de
//  PHP en vez de intentarse, así una inscripción de prueba no
//  queda esperando un timeout ni llena el log de errores.
//
//  Regla de oro: un problema con el correo NUNCA puede voltear el
//  envío del formulario. Todo lo de acá devuelve true/false y deja
//  rastro en error_log(); quien llama no tiene que atrapar nada.
// ============================================================

require_once __DIR__ . '/../conexion.php';

/** Remitente de los avisos: [correo, nombre visible]. */
function correo_remitente(): array {
    $mail = cfg('email_admisiones');
    if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) $mail = cfg('email');
    if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) $mail = 'no-reply@' . (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    return [$mail, cfg('nombre_colegio')];
}

/** Casilla interna que recibe los avisos del panel (Secretaría / Admisiones). */
function correo_casilla_interna(): string {
    $mail = cfg('email_admisiones');
    if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) $mail = cfg('email');
    return filter_var($mail, FILTER_VALIDATE_EMAIL) ? $mail : '';
}

/**
 * Codifica el asunto y el nombre del remitente (RFC 2047): sin esto,
 * los acentos y la «ñ» llegan rotos a algunos clientes de correo.
 */
function correo_codificar(string $txt): string {
    return preg_match('/[\x80-\xFF]/', $txt)
        ? '=?UTF-8?B?' . base64_encode($txt) . '?='
        : $txt;
}

/**
 * Manda un correo de texto plano.
 *   $responder_a : dirección para el botón "Responder" (opcional).
 * Devuelve true si se entregó al servidor de correo (o si se simuló
 * en el entorno local). Nunca lanza excepciones.
 */
function correo_enviar(string $para, string $asunto, string $cuerpo, string $responder_a = ''): bool {
    if (!filter_var($para, FILTER_VALIDATE_EMAIL)) {
        error_log('[juan23] correo no enviado: destinatario inválido (' . $para . ')');
        return false;
    }

    [$de_mail, $de_nombre] = correo_remitente();

    // Los saltos de línea del cuerpo van en CRLF y las líneas se cortan
    // a 70 caracteres: es lo que espera el protocolo SMTP.
    $cuerpo = str_replace(["\r\n", "\r", "\n"], "\r\n", $cuerpo);
    $cuerpo = wordwrap($cuerpo, 70, "\r\n", false);

    $cabeceras = [
        'MIME-Version'              => '1.0',
        'Content-Type'              => 'text/plain; charset=UTF-8',
        'Content-Transfer-Encoding' => '8bit',
        'From'                      => correo_codificar($de_nombre) . ' <' . $de_mail . '>',
        'X-Mailer'                  => 'Sitio Juan XXIII',
    ];
    if (filter_var($responder_a, FILTER_VALIDATE_EMAIL)) {
        $cabeceras['Reply-To'] = $responder_a;
    }

    // Entorno local (XAMPP): no hay servidor de correo, se deja registro.
    if (SITIO_DEBUG) {
        error_log('[juan23] correo SIMULADO (entorno local) → ' . $para . ' · ' . $asunto);
        return true;
    }

    try {
        // Desde PHP 7.2 mail() acepta las cabeceras como array y se
        // encarga de armarlas con el salto de línea que corresponda.
        $ok = @mail($para, correo_codificar($asunto), $cuerpo, $cabeceras);
    } catch (Throwable $ex) {
        error_log('[juan23] correo con error: ' . $ex->getMessage());
        return false;
    }
    if (!$ok) {
        error_log('[juan23] correo rechazado por el servidor → ' . $para . ' · ' . $asunto);
    }
    return (bool) $ok;
}

/** Pie común de los avisos automáticos (datos de contacto del colegio). */
function correo_pie(): string {
    $pie = "\n\n--\n" . cfg('nombre_colegio');
    if (cfg('direccion'))        $pie .= "\n" . cfg('direccion') . ', ' . cfg('localidad');
    if (cfg('telefono'))         $pie .= "\nTel.: " . cfg('telefono');
    if (correo_casilla_interna()) $pie .= "\n" . correo_casilla_interna();
    $pie .= "\n\nEste es un aviso automático del sitio: no hace falta responderlo.";
    return $pie;
}
