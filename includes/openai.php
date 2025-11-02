<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/modelo_cognitivo.php';
use OpenAI\Client;

function consultarOpenAI($mensaje) {
    $apiKey = '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';

    try {
        $client = OpenAI::client($apiKey);

        // === Variables contextuales ===
        $contexto = obtenerContextoCognitivo();
        $sector = $_GET['sector'] ?? 'general';
        $pais = $_POST['pais'] ?? 'global';
        $ciudad = $_POST['ciudad'] ?? '';
        
        // === Prompt globalizado definitivo ===
        $prompt = "Eres 2DayMind, un asesor cognitivo inteligente, analítico y global. 
        Te especializas en el análisis de mercados y sectores económicos en cualquier país o región del mundo.
        Tu conocimiento abarca sectores como salud, educación, turismo y finanzas, 
        pero puedes adaptarte a cualquier otro según el contexto del usuario.

       Ubicación del usuario detectada: país: $pais" . ($ciudad ? ", ciudad: $ciudad" : "") . ".
       Sector actual: $sector.

       Tu objetivo es ofrecer perspectivas actualizadas, sostenibles y comparativas entre regiones del mundo.
       Cuando el usuario pregunte por un país, responde con análisis equilibrados 
       que incluyan tanto el contexto local como la situación global.

       Si no tienes datos específicos de un país, ofrece un análisis basado en fuentes y tendencias globales verificadas 
      sin mencionar limitaciones ni disculpas. 
      Habla con seguridad y tono consultivo, como un asesor internacional de mercados.";


        // === Llamada al modelo GPT ===
        $result = $client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => $prompt],
                ['role' => 'user', 'content' => $mensaje]
            ],
            'temperature' => 0.7,
            'max_tokens' => 400,
        ]);

        $respuesta = $result['choices'][0]['message']['content'] ?? '⚠️ No se recibió respuesta de OpenAI.';

        // === Guarda en el modelo cognitivo (memoria interna) ===
        guardarCognicion($mensaje, $respuesta);

        return nl2br($respuesta);

    } catch (Exception $e) {
        return '❌ Error al conectar con OpenAI: ' . $e->getMessage();
    }
}
?>
