<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/openai.php';
require __DIR__ . '/../includes/cognicion.php';

// Obtener datos de la base de datos
$cogniciones = obtenerCogniciones();
$resumen = "";

foreach ($cogniciones as $c) {
    $resumen .= "Contexto: {$c['contexto']}\nAprendizaje: {$c['aprendizaje']}\n\n";
}

// Crear instrucción para OpenAI
$instruccion = "Analiza el siguiente historial de experiencias y genera un modelo de aprendizaje interno para 2DayMind:\n\n" . $resumen;

// Llamar a OpenAI
$respuesta = consultarOpenAI($instruccion);

// Guardar el resultado en un archivo de texto
file_put_contents(__DIR__ . '/../includes/modelo_cognitivo.txt', $respuesta);

echo "<h2 style='color:lime;'>✅ Modelo cognitivo actualizado exitosamente.</h2>";
?>
