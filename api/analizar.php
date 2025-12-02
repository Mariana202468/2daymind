<?php
// api/analizar.php

require_once __DIR__ . '/../includes/openai.php';

// CORS (ajusta origen si hace falta)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Leer mensaje (JSON o POST)
$input   = json_decode(file_get_contents('php://input'), true);
$mensaje = $input['mensaje'] ?? ($_POST['mensaje'] ?? '');

if (!$mensaje) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'Falta el campo "mensaje"']);
    exit;
}

$respuesta = consultarOpenAI($mensaje);

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'ok'        => true,
    'respuesta' => $respuesta,
]);
