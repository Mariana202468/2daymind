<?php
// api/visionstory_talks.php
header('Content-Type: application/json');

// 1. Cargar configuración (Asumiendo que usas composer y vlucas/phpdotenv)
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$apiKey = $_ENV['DID_API_KEY'];
$sourceUrl = $_ENV['DID_SOURCE_URL']; // Tu URL de Imgur

// 2. Obtener el texto que debe decir el avatar
$input = json_decode(file_get_contents('php://input'), true);
$textToSpeak = $input['text'] ?? 'Hola, no recibí ningún texto.';

// 3. Configurar la petición a D-ID (Crear el video)
$payload = [
    "source_url" => $sourceUrl,
    "script" => [
        "type" => "text",
        "subtitles" => "false",
        "provider" => [
            "type" => "microsoft",
            "voice_id" => "es-MX-JorgeNeural" // Puedes cambiar la voz aquí
        ],
        "input" => $textToSpeak
    ],
    "config" => [
        "fluent" => "false",
        "pad_audio" => "0.0"
    ]
];

// 4. Enviar petición a D-ID
$ch = curl_init('https://api.d-id.com/talks');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Basic ' . $apiKey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);

if ($httpCode !== 201) {
    echo json_encode(['error' => 'Error creando video', 'details' => $data]);
    exit;
}

$talkId = $data['id'];

// 5. ESPERAR a que el video esté listo (Polling)
// D-ID tarda unos segundos, así que preguntamos cada 2 segundos si ya terminó.
$maxRetries = 10;
$videoUrl = null;

for ($i = 0; $i < $maxRetries; $i++) {
    sleep(2); // Esperar 2 segundos

    $chStatus = curl_init("https://api.d-id.com/talks/" . $talkId);
    curl_setopt($chStatus, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . $apiKey
    ]);
    curl_setopt($chStatus, CURLOPT_RETURNTRANSFER, true);
    
    $statusResponse = curl_exec($chStatus);
    curl_close($chStatus);
    
    $statusData = json_decode($statusResponse, true);
    
    if (isset($statusData['result_url'])) {
        $videoUrl = $statusData['result_url'];
        break; // ¡Listo! Salimos del bucle
    }
}

if ($videoUrl) {
    echo json_encode(['video_url' => $videoUrl]);
} else {
    echo json_encode(['error' => 'Tiempo de espera agotado o error en renderizado']);
}
?>