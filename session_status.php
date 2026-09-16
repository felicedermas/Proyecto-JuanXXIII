<?php
// ============================================================
//  session_status.php
//  Devuelve en JSON si hay un usuario logueado, para que el
//  header del index (HTML estático) muestre la "personita".
// ============================================================
require_once __DIR__ . '/panel_config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (esta_logueado()) {
    $u = usuario_actual();
    echo json_encode([
        'logueado' => true,
        'nombre'   => $u['nombre'],
        'apellido' => $u['apellido'],
        'rol'      => rol_label($u['rol']),
        'iniciales'=> mb_strtoupper(mb_substr($u['nombre'], 0, 1) . mb_substr($u['apellido'], 0, 1)),
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['logueado' => false]);
}
