<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db.php';     // 🆕 Agregamos la conexión a la base de datos
require_once __DIR__ . '/../includes/openai.php'; 

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'respuesta' => 'Método no permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 📥 Captura de datos original
$mensaje = $_POST['mensaje'] ?? '';
$pais    = $_POST['pais']    ?? 'Global';
$ciudad  = $_POST['ciudad']  ?? '';
$sector  = $_GET['sector']   ?? 'general';
$idioma  = $_POST['idioma_preferido'] ?? 'auto'; 
$user_id = $_POST['user_id'] ?? 'invitado_local'; // 🆕 Capturamos el ID del usuario

try {
    // 🧠 1. RECUPERAR MEMORIA (Lo nuevo)
    // Buscamos los últimos 6 mensajes para que Xavier sepa de qué estaban hablando.
    $stmt = $pdo->prepare("SELECT rol, contenido FROM memoria_xavier WHERE user_id = ? ORDER BY fecha DESC LIMIT 6");
    $stmt->execute([$user_id]);
    $recuerdos = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));

    $historialTexto = "";
    foreach($recuerdos as $r) {
        $historialTexto .= ($r['rol'] === 'user' ? "Usuario: " : "Xavier: ") . $r['contenido'] . "\n";
    }

    // 🌍 2. TU LÓGICA DE IDIOMA (Intacta)
    if ($idioma === 'auto' || empty($idioma)) {
        $reglaIdioma = "IMPORTANTE: Detecta el idioma del usuario y responde EXCLUSIVAMENTE en ese mismo idioma. ";
    } else {
        $nombres = ['en' => 'Inglés (English)', 'es' => 'Español', 'fr' => 'Francés', 'pt' => 'Portugués'];
        $idiomaTarget = $nombres[$idioma] ?? 'el idioma del usuario';
        $reglaIdioma = "IMPORTANTE: Responde estrictamente en $idiomaTarget. ";
    }

    $mensajeParaAI = $reglaIdioma . "\n\nMensaje del usuario: " . $mensaje;

    // 🚀 3. LLAMADA A LA IA (Añadimos el $historialTexto al final)
    $respuesta = consultarOpenAI($mensajeParaAI, $sector, $pais, $ciudad, $historialTexto);

    // 💾 4. GUARDAR NUEVOS RECUERDOS (Lo nuevo)
    // Guardamos lo que dijo el usuario y lo que respondió Xavier para la próxima vez.
    $insert = $pdo->prepare("INSERT INTO memoria_xavier (user_id, rol, contenido) VALUES (?, 'user', ?), (?, 'assistant', ?)");
    $insert->execute([$user_id, $mensaje, $user_id, $respuesta]);

    echo json_encode([
        'ok'        => true,
        'respuesta' => $respuesta,
    ], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'respuesta' => 'Error: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}