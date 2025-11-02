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
  <title>2DayMind – Asesor Inteligente</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --azul-oscuro: #0b1f2d;
      --azul-profundo: #0e2c44;
      --dorado-metal: #c9a227;
      --azul-metal: #1f6f8b;
      --gris-azulado: #9fb3c8;
      --texto-claro: #e6edf3;
    }

    body {
      margin: 0;
      height: 100vh;
      background: radial-gradient(circle at center, var(--azul-profundo), var(--azul-oscuro));
      color: var(--texto-claro);
      font-family: 'Segoe UI', sans-serif;
      overflow: hidden;
    }

    /* === Partículas dinámicas === */
    #particles-js {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      z-index: 0;
      background: radial-gradient(circle at center, var(--azul-profundo), var(--azul-oscuro));
    }

    .overlay {
      position: relative;
      z-index: 1;
      height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
    }

    .logo {
      filter: drop-shadow(0 0 25px rgba(201,162,39,0.5));
      animation: glow 4s ease-in-out infinite alternate;
    }

    @keyframes glow {
      from { filter: drop-shadow(0 0 10px rgba(201,162,39,0.3)); }
      to { filter: drop-shadow(0 0 25px rgba(201,162,39,0.7)); }
    }

    h1 {
      color: var(--dorado-metal);
      font-weight: 700;
      font-size: 2.2rem;
      margin-top: 15px;
      text-shadow: 0 0 15px rgba(201,162,39,0.4);
    }

    p {
      color: var(--gris-azulado);
      margin-bottom: 25px;
    }

    /* === Botones metálicos === */
    .sector-btn {
      background: linear-gradient(145deg, #d4af37, #a67c00);
      color: #0b1f2d;
      font-weight: 600;
      border: none;
      border-radius: 12px;
      padding: 10px 25px;
      margin: 8px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(201,162,39,0.25);
    }

    .sector-btn:hover {
      transform: scale(1.08);
      background: linear-gradient(145deg, #ffd700, #b8860b);
      box-shadow: 0 0 25px rgba(201,162,39,0.6);
    }

    .credit {
      color: var(--gris-azulado);
      font-size: 0.85rem;
      margin-top: 35px;
      opacity: 0.75;
    }

    .emoji {
      font-size: 1.2rem;
      vertical-align: middle;
    }
  </style>
</head>
<body>
  <!-- Fondo dinámico -->
  <div id="particles-js"></div>

  <!-- Contenido principal -->
  <div class="overlay">
    <img src="assets/logo.png" alt="Logo 2DayMind" width="120" class="logo mb-3">
    <h1>Hola <?= htmlspecialchars($nombre) ?> 👋</h1>
    <p>Asesor Inteligente por Sectores de Mercado</p>

    <div class="d-flex flex-wrap justify-content-center">
      <a href="app.php?sector=salud" class="sector-btn">🏥 Salud</a>
      <a href="app.php?sector=educacion" class="sector-btn">📚 Educación</a>
      <a href="app.php?sector=turismo" class="sector-btn">✈️ Turismo</a>
      <a href="app.php?sector=finanzas" class="sector-btn">💹 Finanzas</a>
    </div>

    <p class="credit mt-4">Actualizado con las últimas tendencias y datos globales 🌐</p>
  </div>

  <!-- Librería de partículas -->
  <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
  <script>
    /* === Configuración de partículas tipo “multiverso de datos” === */
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
