if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('Location: ' . $redirect);
    exit;
}

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/includes/openai.php';
session_start();

$sector = $_GET['sector'] ?? 'general';
$nombre = $_SESSION['nombre'] ?? 'Usuario';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>2DayMind – Asistente Inteligente</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    :root {
      --azul-oscuro: #0b1f2d;
      --azul-profundo: #0e2c44;
      --dorado-metal: #c9a227;
      --azul-metal: #1f6f8b;
      --texto-claro: #e6edf3;
    }

    body {
      margin: 0;
      background: radial-gradient(circle at center, var(--azul-profundo), var(--azul-oscuro));
      color: var(--texto-claro);
      font-family: 'Segoe UI', sans-serif;
      overflow: hidden;
      height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding-top: 25px;
    }

    h2 {
      font-weight: 700;
      background: linear-gradient(90deg, var(--dorado-metal), var(--azul-metal));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      text-shadow: 0 0 20px rgba(201,162,39,0.4);
    }

    .sector-badge {
      background: var(--dorado-metal);
      color: #0b1f2d;
      font-weight: 700;
      border-radius: 8px;
      padding: 4px 14px;
      box-shadow: 0 0 10px rgba(201,162,39,0.5);
    }

    #chatBox {
      width: 85%;
      max-width: 900px;
      height: 60vh;
      overflow-y: auto;
      background: rgba(14, 30, 45, 0.85);
      border: 1px solid rgba(201,162,39,0.25);
      border-radius: 14px;
      padding: 15px;
      box-shadow: 0 0 25px rgba(0,0,0,0.35);
      margin-top: 15px;
      scroll-behavior: smooth;
    }

    .user, .bot {
      max-width: 75%;
      padding: 10px 14px;
      margin: 10px;
      border-radius: 10px;
      font-size: 0.95rem;
      animation: fadeIn 0.4s ease;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .user {
      background: linear-gradient(145deg, #17415e, #1f6f8b);
      color: #ffffff;
      align-self: flex-end;
      margin-left: auto;
    }

    .bot {
      background: linear-gradient(145deg, #c9a227, #b38f2a);
      color: #0b1f2d;
      align-self: flex-start;
      margin-right: auto;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    form {
      width: 85%;
      max-width: 900px;
      display: flex;
      gap: 10px;
      margin-top: 10px;
    }

    input {
      background: #0c1c29;
      color: #fff;
      border: 1px solid rgba(201,162,39,0.3);
      border-radius: 10px;
      padding: 10px;
    }

    input:focus {
      border-color: var(--dorado-metal);
      box-shadow: 0 0 12px rgba(201,162,39,0.4);
      outline: none;
    }

    button {
      background: linear-gradient(145deg, var(--dorado-metal), #d4af37);
      color: #1a1e24;
      font-weight: 600;
      border: none;
      border-radius: 10px;
      padding: 10px 20px;
      transition: all 0.3s ease;
      box-shadow: 0 0 15px rgba(201,162,39,0.3);
    }

    button:hover {
      transform: scale(1.05);
      box-shadow: 0 0 25px rgba(201,162,39,0.6);
    }

    .suggestions {
      width: 85%;
      max-width: 900px;
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      margin-top: 10px;
      gap: 8px;
    }

    .suggestions button {
      background: rgba(201,162,39,0.15);
      border: 1px solid rgba(201,162,39,0.4);
      color: var(--texto-claro);
      font-size: 0.9rem;
      border-radius: 8px;
      padding: 6px 12px;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .suggestions button:hover {
      background: rgba(201,162,39,0.35);
      transform: scale(1.05);
    }

    .nav-links {
      width: 85%;
      max-width: 900px;
      display: flex;
      justify-content: space-between;
      margin-top: 10px;
      font-size: 0.9rem;
    }

    .nav-links a {
      color: var(--dorado-metal);
      text-decoration: none;
    }

    .nav-links a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="text-center">
    <h2>💡 2DayMind – Asistente Inteligente</h2>
    <p>Tu asesor especializado en el sector:</p>
    <span class="sector-badge"><?= strtoupper($sector) ?></span>
  </div>

  <div id="chatBox" class="d-flex flex-column"></div>

  <div class="suggestions">
    <button>¿Cuáles son las tendencias actuales en este sector?</button>
    <button>¿Qué oportunidades de inversión existen?</button>
    <button>¿Cómo afecta la tecnología al sector?</button>
    <button>¿Qué riesgos debo tener en cuenta?</button>
  </div>

  <form id="chatForm">
    <input id="mensaje" name="mensaje" type="text" placeholder="Escribe tu pregunta...">
    <button type="submit">Enviar</button>
  </form>

  <div class="nav-links">
    <a href="index.php">⬅ Volver a sectores</a>
    <a href="bienvenida.php">🏠 Inicio</a>
    <a href="https://www.2daymind.com" target="_blank">🌐 Sitio oficial</a>
  </div>

  <script>
  const form = document.getElementById('chatForm');
  const box = document.getElementById('chatBox');
  const input = document.getElementById('mensaje');

  // Reiniciar la memoria al cargar la app
  fetch('./api/analizar.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'reset=true'
  });

  // Geolocalización automática
  async function obtenerUbicacion() {
    try {
      const res = await fetch("https://ipapi.co/json/");
      const data = await res.json();
      sessionStorage.setItem("paisUsuario", data.country_name);
      sessionStorage.setItem("ciudadUsuario", data.city);
    } catch (err) {
      console.warn("No se pudo obtener la ubicación:", err);
    }
  }
  obtenerUbicacion();

  // Botones de sugerencias
  document.querySelectorAll('.suggestions button').forEach(btn => {
    btn.addEventListener('click', () => {
      input.value = btn.textContent;
      form.requestSubmit();
    });
  });

  // Envío del mensaje
  form.addEventListener('submit', async e => {
    e.preventDefault();
    const msg = input.value.trim();
    if (!msg) return;

    box.innerHTML += `<div class="user">${msg}</div>`;
    input.value = '';
    box.scrollTop = box.scrollHeight;

    const pais = sessionStorage.getItem("paisUsuario") || "Global";
    const ciudad = sessionStorage.getItem("ciudadUsuario") || "";

    const res = await fetch('./api/analizar.php?sector=<?= urlencode($sector) ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        mensaje: msg,
        pais: pais,
        ciudad: ciudad
      })
    });

    const data = await res.text();
    box.innerHTML += `<div class="bot">${data}</div>`;
    box.scrollTop = box.scrollHeight;
  });
  </script>
</body>
</html>
