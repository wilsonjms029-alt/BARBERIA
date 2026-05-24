<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
echo json_encode([
    'ok' => true,
    'mensaje' => 'Android conectado al PC correctamente',
    'hora_servidor' => date('Y-m-d H:i:s'),
    'tu_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
], JSON_UNESCAPED_UNICODE);
