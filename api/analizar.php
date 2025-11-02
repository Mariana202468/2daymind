<?php
require __DIR__ . '/../includes/openai.php';
require __DIR__ . '/../includes/modelo_cognitivo.php';

// 🧹 Permite reiniciar la memoria cognitiva si se solicita
if (isset($_POST['reset']) && $_POST['reset'] === 'true') {
    file_put_contents(__DIR__ . '/../includes/modelo_cognitivo.txt', '');
    echo "🧠 Memoria cognitiva reiniciada.";
    exit;
}

// 🛑 Validar que haya mensaje
if (!isset($_POST['mensaje']) || trim($_POST['mensaje']) === '') {
    echo 'Por favor escribe algo.';
    exit;
}

$mensaje = trim($_POST['mensaje']);
$pais = $_POST['pais'] ?? 'Global';
$ciudad = $_POST['ciudad'] ?? '';
$sector = $_GET['sector'] ?? 'general';

// 🧩 Cargar contexto anterior (memoria local)
$contexto = obtenerContextoCognitivo();

// 🧠 Construir entrada con contexto global
$entrada = "Usuario desde $pais" . ($ciudad ? ", ciudad $ciudad" : "") .
           " en el sector $sector pregunta:\n\n" . $mensaje .
           "\n\nContexto previo:\n" . $contexto;

// 🤖 Obtener respuesta del modelo
$respuesta = consultarOpenAI($entrada);

// 💾 Guardar la interacción en el modelo cognitivo local
guardarCognicion($mensaje, $respuesta);

// 📨 Mostrar respuesta limpia
echo nl2br($respuesta);
?>
