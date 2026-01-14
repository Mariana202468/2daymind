<?php
// api/tts.php - MOTOR DE VOZ PROFESIONAL (ElevenLabs)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

// ⚠️ CONSIGUE TU CLAVE EN: elevenlabs.io -> Profile -> API Key
$apiKey = "AIzaSyB4RdvCgALGxqgY2ml2TpYjv9gFjNYBAWY"; 
$voiceId = "pNInz6ovhh93XeyvR9PN"; // Voz clara y global

// Recibir el texto desde app.php
$input = json_decode(file_get_contents("php://input"), true);
$text = $input['text'] ?? '';

if (empty($text)) {
    http_response_code(400);
    echo json_encode(["error" => "No text provided"]);
    exit;
}

// Llamada a ElevenLabs (Soporta +29 idiomas automáticamente)
$url = "https://api.elevenlabs.io/v1/text-to-speech/" . $voiceId;
$data = [
    "text" => $text,
    "model_id" => "eleven_multilingual_v2", 
    "voice_settings" => ["stability" => 0.5, "similarity_boost" => 0.8]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "xi-api-key: " . $apiKey
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    header("Content-Type: audio/mpeg");
    echo $response; // El avatar recibe el audio y mueve los labios
} else {
    http_response_code($httpCode);
    echo $response; // Aquí verás el error real en la consola si falta crédito o clave
}