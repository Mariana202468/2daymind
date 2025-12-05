<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/modelo_cognitivo.php';
require_once __DIR__ . '/busqueda_web.php';

use Dotenv\Dotenv;
use OpenAI\Factory;

// Cargar .env
$dotenvPath = __DIR__ . '/..';
if (file_exists($dotenvPath . '/.env')) {
    $dotenv = Dotenv::createMutable($dotenvPath);
    $dotenv->load();
}

// 👇 OJO: aquí NO hay header('Content-Type'), NI echo json_encode

/**
 * Llama a OpenAI y devuelve SOLO el texto de respuesta (string),
 * sin imprimir nada.
 */
function consultarOpenAI(string $mensaje, string $sector = 'general', string $pais = 'global', string $ciudad = ''): string
{
    $apiKey = $_ENV['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY');

    if (!$apiKey) {
        return "❌ No se encontró la API key (OPENAI_API_KEY) en .env";
    }

    try {
        $client = (new Factory())
            ->withApiKey($apiKey)
            ->make();

        // contexto externo
        $contextoWeb = buscarEnWeb($mensaje);

        $prompt = "Eres 2DayMind, un asesor cognitivo crítico y riguroso.
País: $pais" . ($ciudad ? ", Ciudad: $ciudad" : "") . ".
Sector: $sector.

Usa SOLO la información del siguiente contexto web y sé explícito:
$contextoWeb

Instrucciones:
- Si la evidencia es débil, responde: 'No lo sé con seguridad' y explica por qué.
- Indica SIEMPRE nivel_de_confianza: alto / medio / bajo.
- Cita las fuentes por dominio (ej: who.int, un.org).
- Devuelve SIEMPRE en este formato:

1) Conclusión
2) Evidencia
3) Riesgos / Incertidumbres
4) Nivel_de_confianza.
";

        $result = $client->responses()->create([
            'model'        => 'gpt-4o-mini',
            'input'        => $mensaje,
            'instructions' => $prompt,
        ]);

        $respuesta = $result->outputText ?? '⚠️ Sin respuesta.';

        guardarCognicion($mensaje, $respuesta);

        return $respuesta;
    } catch (\Throwable $e) {
        error_log('Error OpenAI: ' . $e->getMessage());
        return '❌ Error al conectar con OpenAI: ' . $e->getMessage();
    }
}
