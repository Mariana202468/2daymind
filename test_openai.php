<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>🚀 Iniciando prueba con OpenAI...</h3>";

try {
    require __DIR__ . '/includes/openai.php';
    echo "<p>✅ Archivo <b>openai.php</b> cargado correctamente.</p>";

    $respuesta = consultarOpenAI("Dame un análisis del mercado colombiano 2025, breve pero profesional.");
    echo "<hr><h4>🧠 Respuesta:</h4>";
    echo "<div style='white-space: pre-wrap; font-family: monospace;'>$respuesta</div>";
} catch (Throwable $e) {
    echo "<p style='color:red;'>❌ Error detectado: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
