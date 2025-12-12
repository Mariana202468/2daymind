<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/modelo_cognitivo.php';
require_once __DIR__ . '/busqueda_web.php';

use Dotenv\Dotenv;
use OpenAI\Factory;

// 🔧 Cargar .env
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
 * No hace echo ni header, para poder reutilizarla en API y en test_openai.php.
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

    // Contexto de usuario (POST opcional)
    $pais   = $_POST['pais']   ?? 'Global';
    $ciudad = $_POST['ciudad'] ?? '';

    try {
        // Cliente OpenAI
        $client = (new Factory())
            ->withApiKey($apiKey)
            ->make();

        // 🌐 Búsqueda web enriquecida
        $busquedaTexto = $mensaje;
        if ($sector !== '') {
            $busquedaTexto .= ' sector ' . $sector;
        }

        $contextoWeb = buscarEnWeb($busquedaTexto, $pais, $sector);
        $contextoWeb = trim($contextoWeb);

        // ¿Hay algo útil en web?
        $hayContextoWeb = $contextoWeb !== '';

        if ($hayContextoWeb) {
            // 🧠 Modo: evidencia + enlaces
            $prompt = <<<PROMPT
Eres 2DayMind, un asesor cognitivo crítico y riguroso.

Contexto del usuario:
- País: $pais
- Ciudad: {$ciudad}
- Sector: $sector

Tienes el siguiente CONTEXTO_WEB basado en búsquedas recientes
(incluye resúmenes y enlaces):

$contextoWeb

Instrucciones IMPORTANTES:
- Basa tu análisis PRINCIPALMENTE en este CONTEXTO_WEB, combinando con tu conocimiento general cuando sea útil.
- NO empieces la respuesta con frases como "No lo sé", "No lo sé con seguridad" o "No tengo información".
- Si la evidencia es limitada, explícalo en la sección 3) Riesgos / Incertidumbres y ajusta el nivel_de_confianza, pero siempre da tu mejor análisis.
- Cuando veas URLs, identifica el dominio como posible fuente (ej: who.int, banrep.gov.co, oecd.org, minsalud.gov.co) y puedes mencionarlo como referencia.
- No inventes enlaces que no aparezcan en el contexto, pero sí puedes sugerir tipos de fuentes (institutos oficiales, bancos centrales, etc.).

Devuelve SIEMPRE en este formato de 4 bloques:

1) Conclusión
2) Evidencia  (usa viñetas si es útil; referencia dominios de las fuentes)
3) Riesgos / Incertidumbres (explica qué datos faltan o son débiles)
4) Nivel_de_confianza (alto / medio / bajo)
PROMPT;
        } else {
            // 🧠 Modo: conocimiento general (sin web fiable)
            $prompt = <<<PROMPT
Eres 2DayMind, un asesor cognitivo crítico y riguroso.

Contexto del usuario:
- País: $pais
- Ciudad: {$ciudad}
- Sector: $sector

No tienes resultados web fiables para esta pregunta en este momento,
pero sí tu conocimiento general entrenado (hasta 2024).

Instrucciones IMPORTANTES:
- NO uses frases como "No lo sé con seguridad", "no puedo responder" o similares.
- Da SIEMPRE un análisis útil y estructurado, aunque aclares las limitaciones.
- Cuando la pregunta sea sobre futuro o proyecciones, presenta al menos dos escenarios
  (por ejemplo: escenario base y escenario de riesgo) y qué condiciones los disparan.
- Incluye SIEMPRE:
  * al menos 3 factores clave que influyen en el tema;
  * al menos 2 recomendaciones prácticas o preguntas que la persona debería hacerse.
- Sé honesto sobre las incertidumbres: explícales en la sección 3) Riesgos / Incertidumbres.
- Puedes sugerir qué tipos de fuentes revisar (banco central, ministerio, regulador, universidades),
  pero SIN inventar URLs concretas.

Devuelve SIEMPRE en este formato de 4 bloques:

1) Conclusión
2) Evidencia (basada en conocimiento general)
3) Riesgos / Incertidumbres
4) Nivel_de_confianza (alto / medio / bajo)
PROMPT;
        }

        // 🚀 Llamada a la API de responses
        $result = $client->responses()->create([
            'model'        => 'gpt-4o-mini',
            'input'        => $mensaje,
            'instructions' => $prompt,
        ]);

        $respuesta = $result->outputText ?? '⚠️ Sin respuesta.';

        // Guardar en tu modelo cognitivo
        guardarCognicion($mensaje, $respuesta);

        // Para web / HTML, convertimos saltos de línea
        return nl2br($respuesta);

    } catch (\Throwable $e) {
        error_log('Error OpenAI: ' . $e->getMessage());
        return '❌ Error al conectar con OpenAI: ' . $e->getMessage();
    }
}
