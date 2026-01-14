<?php
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use OpenAI\Factory;

// Cargar .env de la raíz
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey = $_ENV['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY');
if (!$apiKey) {
    die("NO API KEY\n");
}

$client = (new Factory())->withApiKey($apiKey)->make();

echo "🚀 Probando OpenAI (responses API) desde CLI...\n";

try {
    $result = $client->responses()->create([
        'model'        => 'gpt-4o-mini',
        'input'        => 'Prueba simple desde PHP en la Mac',
        'instructions' => 'Responde en una sola frase corta.',
    ]);

    echo "✅ OPENAI OK\n";
    echo "Respuesta:\n";
    echo ($result->outputText ?? '⚠️ Sin respuesta.') . "\n";
} catch (Throwable $e) {
    echo "❌ ERROR OPENAI:\n";
    echo $e->getMessage() . "\n";
}

