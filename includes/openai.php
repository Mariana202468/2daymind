<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/modelo_cognitivo.php';
require_once __DIR__ . '/busqueda_web.php';

use Dotenv\Dotenv;
use OpenAI\Factory;

// 🔧 Cargar .env (pisando variables previas si las hubiera)
$dotenvPath = __DIR__ . '/..';
if (file_exists($dotenvPath . '/.env')) {
    $dotenv = Dotenv::createMutable($dotenvPath);
    $dotenv->load();
} else {
    error_log("⚠️ No se encontró .env en $dotenvPath");
}

// Debug opcional: comprobar que la key está cargada
if (!isset($_ENV['OPENAI_API_KEY']) && !getenv('OPENAI_API_KEY')) {
    error_log("⚠️ No se cargó OPENAI_API_KEY desde .env");
}

function consultarOpenAI(string $mensaje): string
{
    $apiKey = $_ENV['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY');

    if (!$apiKey) {
        return "❌ No se encontró la API key (OPENAI_API_KEY) en .env";
    }

    try {
        // Cliente OpenAI
        $client = (new Factory())
            ->withApiKey($apiKey)
            ->make();

        // Contexto de usuario
        $sector = $_GET['sector'] ?? 'general';
        $pais   = $_POST['pais']   ?? 'global';
        $ciudad = $_POST['ciudad'] ?? '';

        // 🔎 Contexto externo (web filtrada)
        $contextoWeb = buscarEnWeb($mensaje);

        // Prompt “crítico” y estructurado
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

Devuelve SOLO ese esquema numerado, sin texto extra antes ni después.
";

        // 🚀 API nueva de responses
        $result = $client->responses()->create([
            'model'        => 'gpt-4o-mini',
            'input'        => $mensaje,
            'instructions' => $prompt,
        ]);

        $respuesta = $result->outputText ?? '⚠️ Sin respuesta.';

        // Guardar en tu modelo cognitivo
        guardarCognicion($mensaje, $respuesta);

        return nl2br($respuesta);

    } catch (\Throwable $e) {
        error_log('Error OpenAI: ' . $e->getMessage());
        return '❌ Error al conectar con OpenAI: ' . $e->getMessage();
    }
}
