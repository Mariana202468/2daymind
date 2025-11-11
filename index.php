<?php
session_start();
if (!isset($_SESSION['nombre'])) {
  header('Location: bienvenida.php');
  exit;
}
$nombre = $_SESSION['nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>2DayMind – Asesor Inteligente</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- 🎨 Estilo dorado metálico externo -->
  <link rel="stylesheet" href="includes/style.css?v=4">
</head>

<body>
  <!-- 🌌 Fondo animado -->
  <div id="particles-js"></div>

  <!-- 🧠 Contenido principal -->
  <div class="overlay text-center">
    <img src="assets/logo.png" alt="Logo 2DayMind" width="120" class="logo mb-3">
    <h1>Hola <?= htmlspecialchars($nombre) ?> 👋</h1>
    <p>Asesor Inteligente por Sectores de Mercado</p>

    <div class="d-flex flex-wrap justify-content-center">
      <a href="app.php?sector=salud" class="sector-btn">🏥 Salud</a>
      <a href="app.php?sector=educacion" class="sector-btn">📚 Educación</a>
      <a href="app.php?sector=turismo" class="sector-btn">✈️ Turismo</a>
      <a href="app.php?sector=finanzas" class="sector-btn">💹 Finanzas</a>
    </div>

    <p class="credit mt-4">
      Actualizado con las últimas tendencias y datos globales 🌐
    </p>
  </div>

  <!-- ✨ Librería de partículas -->
  <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
  <script>
    particlesJS("particles-js", {
      "particles": {
        "number": { "value": 80, "density": { "enable": true, "value_area": 800 } },
        "color": { "value": ["#c9a227", "#1f6f8b", "#ffffff"] },
        "shape": { "type": "circle" },
        "opacity": { "value": 0.6, "random": true },
        "size": { "value": 3, "random": true },
        "line_linked": {
          "enable": true,
          "distance": 150,
          "color": "#c9a227",
          "opacity": 0.25,
          "width": 1
        },
        "move": {
          "enable": true,
          "speed": 1.5,
          "direction": "none",
          "random": true,
          "straight": false,
          "out_mode": "out",
          "bounce": false
        }
      },
      "interactivity": {
        "detect_on": "canvas",
        "events": {
          "onhover": { "enable": true, "mode": "repulse" },
          "onclick": { "enable": true, "mode": "push" },
          "resize": true
        },
        "modes": {
          "repulse": { "distance": 100, "duration": 0.4 },
          "push": { "particles_nb": 4 }
        }
      },
      "retina_detect": true
    });
  </script>
</body>
</html>
