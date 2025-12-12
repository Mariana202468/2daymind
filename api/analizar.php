<?php
declare(strict_types=1);

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

/**
 * Llama a OpenAI y devuelve SOLO el texto listo para mostrar en el chat.
 * No hace echo ni header, para que puedas reutilizarla desde otros scripts.
 */
function consultarOpenAI(string $mensaje, string $sector = 'general'): string
{
    $apiKey = $_ENV['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY');

    if (!$apiKey) {
        return "❌ No se encontró la API key (OPENAI_API_KEY) en .env";
    }

    $mensaje = trim($mensaje);
    if ($mensaje === '') {
        return "Por favor escribe una pregunta o contexto para que pueda ayudarte 🙂";
    }

    // Contexto de usuario (si vienen en POST; si no, valores por defecto)
    $pais   = $_POST['pais']   ?? 'Global';
    $ciudad = $_POST['ciudad'] ?? '';

    try {
        // Cliente OpenAI
        $client = (new Factory())
            ->withApiKey($apiKey)
            ->make();

        // 🔎 Contexto externo (web filtrada)
        $contextoWeb = buscarEnWeb($mensaje, $sector, $pais, $ciudad);

        // ¿El contexto trae algo mínimamente útil?
        $tieneContextoWeb =
            $contextoWeb &&
            stripos($contextoWeb, 'No se encontraron resultados') === false &&
            stripos($contextoWeb, 'No se pudo obtener información') === false;

        if ($tieneContextoWeb) {
            // 🧠 Modo "evidencia fuerte": usar sobre todo la web filtrada
            $prompt = "Eres 2DayMind, un asesor cognitivo crítico y riguroso.
País: $pais" . ($ciudad ? ", Ciudad: $ciudad" : "") . ".
Sector: $sector.

Tienes el siguiente contexto web (reciente y de fuentes filtradas):
$contextoWeb

Instrucciones:
- Basa tu respuesta PRINCIPALMENTE en este contexto web.
- Si la evidencia es débil o contradictoria, dilo explícitamente.
- Indica SIEMPRE nivel_de_confianza: alto / medio / bajo.
- Cita las fuentes por dominio (ej: who.int, un.org).
- Devuelve SIEMPRE en este formato:

1) Conclusión
2) Evidencia
3) Riesgos / Incertidumbres
4) Nivel_de_confianza.
";
        } else {
            // 🧠 Modo "conocimiento general": no hay buena evidencia online
            $prompt = "Eres 2DayMind, un asesor cognitivo crítico y riguroso.
País: $pais" . ($ciudad ? ", Ciudad: $ciudad" : "") . ".
Sector: $sector.

No tienes contexto web fiable para esta pregunta.
Responde usando SOLO tu conocimiento general entrenado (hasta 2024),
dejando claro que puede no estar completamente actualizado.

Instrucciones:
- Da una respuesta útil y razonada, no digas solo 'no lo sé'.
- Si la pregunta depende de datos muy recientes o cifras exactas,
  indica que son estimaciones y sugiere consultar fuentes oficiales
  (ej: ministerio de salud, OMS, Banco Mundial, etc.).
- Indica SIEMPRE nivel_de_confianza: alto / medio / bajo.
- Usa el mismo formato:

1) Conclusión
2) Evidencia (basada en conocimiento general)
3) Riesgos / Incertidumbres
4) Nivel_de_confianza.
";
        }

        // 🚀 API nueva de responses
        $result = $client->responses()->create([
            'model'        => 'gpt-4o-mini',
            'input'        => $mensaje,
            'instructions' => $prompt,
        ]);

        $respuesta = $result->outputText ?? '⚠️ Sin respuesta.';

        // Guardar en tu modelo cognitivo
        guardarCognicion($mensaje, $respuesta);

        // Para web, devolvemos con saltos de línea HTML
        return nl2br($respuesta);

    } catch (\Throwable $e) {
        error_log('Error OpenAI: ' . $e->getMessage());
        return '❌ Error al conectar con OpenAI: ' . $e->getMessage();
    }
}

// ===============================
//  Manejador HTTP de la API
// ===============================

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'ok'        => false,
        'respuesta' => 'Método no permitido. Usa POST.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Reset de memoria (si tu JS manda reset=true al cargar)
if (!empty($_POST['reset'])) {
    // Si tienes alguna función para resetear tu modelo, la llamas aquí.
    // resetModeloCognitivo();  // solo si la tienes implementada
    echo json_encode([
        'ok'        => true,
        'respuesta' => 'Memoria reiniciada.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$mensaje = $_POST['mensaje'] ?? '';
$sector  = $_GET['sector'] ?? 'general';

// 👉 AQUÍ van las dos líneas que preguntabas:
$respuesta = consultarOpenAI($mensaje, $sector);
echo json_encode(['ok' => true, 'respuesta' => $respuesta], JSON_UNESCAPED_UNICODE);
exit;
