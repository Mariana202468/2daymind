<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/openai.php';

$mensaje = trim($_POST['mensaje'] ?? '');
$pais    = trim($_POST['pais'] ?? '');
$ciudad  = trim($_POST['ciudad'] ?? '');

if ($mensaje === '') {
    echo json_encode([
        'ok'        => false,
        'respuesta' => 'Por favor escribe una pregunta o contexto para que pueda ayudarte 🙂',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$_POST['pais']   = $pais   ?: 'global';
$_POST['ciudad'] = $ciudad ?: '';

$respuesta = consultarOpenAI($mensaje);

echo json_encode([
    'ok'        => true,
    'respuesta' => $respuesta,
], JSON_UNESCAPED_UNICODE);
