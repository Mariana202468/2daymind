<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/busqueda_web.php';

use Dotenv\Dotenv;
use OpenAI\Factory;

/**
 * Carga variables de entorno desde .env (si existe).
 */
(function () {
    $dotenvPath = __DIR__ . '/..';

    if (file_exists($dotenvPath . '/.env')) {
        $dotenv = Dotenv::createMutable($dotenvPath);
        $dotenv->load();
    } else {
        error_log("⚠️ No se encontró .env en $dotenvPath");
    }
})();

/**
 * Función principal con soporte Multilingüe y MEMORIA.
 */
function consultarOpenAI(
    string $mensaje,
    string $sector = 'general',
    string $pais   = 'Global',
    string $ciudad = '',
    string $historial = '' // 🆕 NUEVO PARÁMETRO: Recibe los recuerdos de la DB
): string {
    $apiKey = $_ENV['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY');

    if (!$apiKey) {
        return "❌ No se encontró la API key (OPENAI_API_KEY) en la configuración del servidor.";
    }

    $mensaje = trim($mensaje);
    if ($mensaje === '') {
        return "Por favor escribe una pregunta o contexto para que pueda ayudarte 🙂";
    }

    try {
        $client = (new Factory())
            ->withApiKey($apiKey)
            ->make();

        // 1️⃣ BÚSQUEDA WEB (Se mantiene igual)
        $contextoWeb = '';
        try {
            $contextoWeb = buscarEnWeb($mensaje, $sector, $pais, $ciudad);
        } catch (\Throwable $e) {
            error_log('Error en buscarEnWeb: ' . $e->getMessage());
            $contextoWeb = '';
        }

        // 2️⃣ SISTEMA / INSTRUCCIONES (INTEGRAMOS LA MEMORIA AQUÍ)
        $baseInstrucciones = <<<PROMPT
Eres 2DayMind, un asistente de análisis para personas no expertas. 

REGLA DE MEMORIA:
- Tienes acceso a una base de datos física donde guardas cada conversación (ver HISTORIAL abajo). 
- NUNCA digas que no tienes memoria a largo plazo. Al contrario, demuestra que recuerdas al usuario mencionando datos de charlas pasadas.
- Si el usuario te pregunta "¿Me recuerdas?", busca en el historial y responde con detalles específicos.

REGLA CRÍTICA DE IDIOMA: 
Debes detectar el idioma del usuario y responder SIEMPRE en ese mismo idioma. 
Si el mensaje incluye una instrucción específica de idioma al inicio, obedécela estrictamente.

Tu objetivo es ayudar a la persona a entender mejor el tema y tomar decisiones más informadas. 
Tu tono debe ser cercano pero riguroso.

Siempre debes:
- Explicar qué se sabe con cierta confianza y por qué.
- Explicar qué NO se sabe bien o es incierto.
- Sugerir cómo la persona podría validar o ampliar la información.

Estilo de respuesta:
- Responde como en una conversación normal, con párrafos cortos.
- No uses formato Markdown complejo; escribe texto plano.
- Termina siempre con una frase tipo: "En resumen..." y menciona de forma explícita tu nivel de confianza (alto / medio / bajo).

HISTORIAL RECIENTE (Para tu memoria):
$historial
PROMPT;

        // 3️⃣ UNIÓN DE CONTEXTO WEB O CONOCIMIENTO GENERAL
        if ($contextoWeb && mb_strlen($contextoWeb) > 200) {
            $instrucciones = $baseInstrucciones . <<<PROMPT

Además tienes resultados de búsqueda web. Úsalos como evidencia principal:
- Menciona de forma natural los sitios de referencia.

Al final de la respuesta, si ves URLs claras, añade una sección corta:
Fuentes sugeridas:
- URL 1

Contexto web disponible:
$contextoWeb
PROMPT;
        } else {
            $instrucciones = $baseInstrucciones . <<<PROMPT

No tienes resultados de búsqueda web útiles. Responde usando SOLO tu conocimiento general entrenado, aclarando cuando algo dependa de datos recientes.
PROMPT;
        }

        // 4️⃣ LLAMADA A LA API
        $resultado = $client->responses()->create([
            'model'        => 'gpt-4o-mini',
            'input'        => $mensaje,
            'instructions' => $instrucciones,
        ]);

        $respuesta = $resultado->outputText ?? "Lo siento, no pude generar una respuesta útil.";
        return $respuesta;

    } catch (\Throwable $e) {
        error_log('Error OpenAI: ' . $e->getMessage());
        return '❌ Error al conectar con OpenAI: ' . $e->getMessage();
    }
}