<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require __DIR__ . '/includes/openai.php';

$sector  = $_GET['sector'] ?? 'general';
$nombre  = $_SESSION['nombre'] ?? 'Usuario';

$sector_safe = htmlspecialchars($sector, ENT_QUOTES, 'UTF-8');
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
      --texto-oscuro: #1f2933;
      --gris-fondo: #f4f6fb;
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      min-height: 100vh;
      background: var(--gris-fondo);
      color: var(--texto-oscuro);
      font-family: 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
      display: flex;
      flex-direction: column;
    }

    .page {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 24px 12px 96px; /* espacio para la barra inferior */
    }

    .header {
      width: 100%;
      max-width: 960px;
      text-align: center;
      margin-bottom: 12px;
    }

    .header h2 {
      margin: 0 0 6px;
      font-weight: 700;
      font-size: 1.6rem;
      background: linear-gradient(90deg, var(--azul-profundo), var(--dorado-metal));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .header p {
      margin: 0;
      font-size: 0.95rem;
      color: #4b5563;
    }

    .sector-badge {
      display: inline-block;
      margin-top: 8px;
      background: var(--dorado-metal);
      color: #0b1f2d;
      font-weight: 700;
      border-radius: 999px;
      padding: 4px 16px;
      box-shadow: 0 0 10px rgba(201,162,39,0.4);
      font-size: 0.85rem;
      letter-spacing: 0.04em;
    }

    /* Contenedor principal del chat */
    .chat-container {
      width: 100%;
      max-width: 960px;
      flex: 1;
      display: flex;
      flex-direction: column;
      margin-top: 12px;
    }

    #chatBox {
      flex: 1;
      background: #ffffff;
      border-radius: 18px;
      box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
      padding: 18px 18px 12px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .bubble {
      max-width: 80%;
      padding: 10px 14px;
      border-radius: 14px;
      font-size: 0.95rem;
      line-height: 1.4;
      box-shadow: 0 2px 6px rgba(15,23,42,0.08);
      animation: fadeIn 0.18s ease-out;
      white-space: pre-wrap;
      word-break: break-word;
    }

    .bubble.user {
      align-self: flex-end;
      background: linear-gradient(135deg, var(--azul-metal), #2196f3);
      color: #ffffff;
    }

    .bubble.bot {
      align-self: flex-start;
      background: #fff8e3;
      border: 1px solid rgba(201,162,39,0.35);
      color: #1f2933;
    }

    .bubble.bot strong.label {
      display: block;
      margin-bottom: 4px;
      color: var(--azul-profundo);
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(6px); }
      to   { opacity: 1; transform: translateY(0);   }
    }

    /* Sugerencias dentro del chat */
    #suggestionsPanel {
      margin-bottom: 6px;
    }

    .suggestion-title {
      font-size: 0.85rem;
      color: #6b7280;
      margin-bottom: 6px;
    }

    .suggestion-pill {
      display: inline-block;
      margin: 3px 4px 4px 0;
      padding: 6px 10px;
      border-radius: 999px;
      border: 1px solid rgba(15,23,42,0.08);
      background: #f9fafb;
      font-size: 0.8rem;
      cursor: pointer;
      color: #374151;
      transition: all 0.15s ease;
    }

    .suggestion-pill:hover {
      background: #e5edf9;
      border-color: var(--azul-metal);
      color: var(--azul-profundo);
      transform: translateY(-1px);
    }

    /* Barra inferior fija */
    .input-bar-wrapper {
      position: fixed;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(244,246,251,0.96);
      backdrop-filter: blur(8px);
      border-top: 1px solid rgba(148,163,184,0.35);
      padding: 10px 12px;
      display: flex;
      justify-content: center;
      z-index: 20;
    }

    .input-bar {
      width: 100%;
      max-width: 960px;
      display: flex;
      gap: 10px;
      align-items: center;
    }

    .input-bar input[type="text"] {
      flex: 1;
      border-radius: 999px;
      border: 1px solid rgba(148,163,184,0.8);
      padding: 10px 16px;
      font-size: 1rem;
      background: #ffffff;
      color: #111827;
      box-shadow: 0 3px 8px rgba(15, 23, 42, 0.08);
    }

    .input-bar input[type="text"]:focus {
      outline: none;
      border-color: var(--azul-metal);
      box-shadow: 0 0 0 2px rgba(31,111,139,0.25);
    }

    .input-bar button {
      border-radius: 999px;
      border: none;
      padding: 10px 20px;
      font-weight: 600;
      font-size: 0.95rem;
      background: linear-gradient(135deg, var(--dorado-metal), #f5c84b);
      color: #0b1f2d;
      box-shadow: 0 4px 12px rgba(201,162,39,0.55);
      display: inline-flex;
      align-items: center;
      gap: 6px;
      cursor: pointer;
      transition: all 0.15s ease;
    }

    .input-bar button:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 18px rgba(201,162,39,0.75);
    }

    .input-bar button:active {
      transform: translateY(0);
      box-shadow: 0 2px 8px rgba(55,65,81,0.4);
    }

    .nav-links {
      width: 100%;
      max-width: 960px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 10px;
      font-size: 0.85rem;
      color: #6b7280;
    }

    .nav-links a {
      color: var(--azul-metal);
      text-decoration: none;
    }

    .nav-links a:hover {
      text-decoration: underline;
    }

    @media (max-width: 768px) {
      .bubble {
        max-width: 100%;
      }
      .input-bar button span.text {
        display: none; /* solo ícono en móviles si quieres */
      }
    }
  </style>
</head>
<body>
  <div class="page">
    <div class="header">
      <h2>💡 2DayMind – Asistente Inteligente</h2>
      <p>Tu asesor especializado en el sector:</p>
      <span class="sector-badge"><?= strtoupper($sector_safe) ?></span>
    </div>

    <div class="chat-container">
      <div id="chatBox">
        <!-- Panel de sugerencias iniciales -->
        <div id="suggestionsPanel"></div>
      </div>

      <div class="nav-links">
        <a href="index.php">⬅ Volver a sectores</a>
        <a href="bienvenida.php">🏠 Inicio</a>
        <a href="https://www.2daymind.com" target="_blank">🌐 Sitio oficial</a>
      </div>
    </div>
  </div>

  <!-- Barra de entrada fija abajo -->
  <div class="input-bar-wrapper">
    <form id="chatForm" class="input-bar" action="./api/analizar.php?sector=<?= urlencode($sector) ?>" method="POST">
      <input id="mensaje" name="mensaje" type="text" autocomplete="off"
             placeholder="Escribe tu pregunta aquí…">
      <button type="submit">
        <span class="text">Enviar</span> 🚀
      </button>
    </form>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const form   = document.getElementById('chatForm');
    const box    = document.getElementById('chatBox');
    const input  = document.getElementById('mensaje');
    const suggEl = document.getElementById('suggestionsPanel');

    if (!form || !box || !input) return;

    const sectorActual = "<?= strtolower($sector_safe) ?>";

    const sugerenciasPorSector = {
      salud: [
        "¿Cuáles son hoy los principales retos del sistema de salud en mi país?",
        "¿Qué tecnologías están transformando el sector salud?",
        "¿Qué riesgos éticos existen con el uso de IA en salud?",
        "¿Qué indicadores básicos debería vigilar de un sistema de salud?"
      ],
      finanzas: [
        "¿Qué riesgos económicos enfrenta Colombia en 2025 y qué tan confiables son esas estimaciones?",
        "¿Qué debo revisar antes de invertir en un fondo de inversión?",
        "¿Cómo afecta la inflación a mis ahorros?",
        "¿Qué señales alertan de una posible crisis financiera?"
      ],
      tecnologia: [
        "¿Qué tecnologías emergentes tendrán más impacto en los próximos 5 años?",
        "¿Qué riesgos de ciberseguridad son más críticos para una pyme?",
        "¿Qué buenas prácticas recomiendas para usar IA de forma responsable?",
        "¿Cómo evaluar la confiabilidad de una fuente tecnológica en línea?"
      ],
      general: [
        "¿Qué preguntas clave debería hacer antes de tomar una decisión importante?",
        "¿Cómo puedo evaluar si una noticia en internet es confiable?",
        "¿Qué sesgos cognitivos afectan más a los líderes al decidir?",
        "¿Cómo estructurar pros y contras de una decisión compleja?"
      ]
    };

    const sugerencias = sugerenciasPorSector[sectorActual] || sugerenciasPorSector.general;

    function renderSugerencias() {
      if (!suggEl) return;
      let html = '<div class="suggestion-title">Sugerencias para empezar:</div>';
      for (const q of sugerencias) {
        html += `<span class="suggestion-pill">${q}</span>`;
      }
      suggEl.innerHTML = html;

      suggEl.querySelectorAll('.suggestion-pill').forEach(btn => {
        btn.addEventListener('click', () => {
          input.value = btn.textContent;
          form.requestSubmit();
        });
      });
    }

    renderSugerencias();

    // Opcional: reset de memoria cognitiva al cargar
    fetch('./api/analizar.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'reset=true'
    }).catch(() => {});

    async function obtenerUbicacion() {
      try {
        const res = await fetch("https://ipapi.co/json/");
        const data = await res.json();
        sessionStorage.setItem("paisUsuario", data.country_name || "Global");
        sessionStorage.setItem("ciudadUsuario", data.city || "");
      } catch (err) {
        console.warn("No se pudo obtener la ubicación:", err);
      }
    }
    obtenerUbicacion();

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const msg = input.value.trim();
      if (!msg) return;

      const pais   = sessionStorage.getItem("paisUsuario")   || "Global";
      const ciudad = sessionStorage.getItem("ciudadUsuario") || "";

      // Ocultar sugerencias al primer mensaje (si quieres)
      if (suggEl) suggEl.style.display = 'none';

      // Burbuja usuario
      box.insertAdjacentHTML('beforeend',
        `<div class="bubble user"><strong>Tú:</strong> ${msg}</div>`
      );
      box.scrollTop = box.scrollHeight;
      input.value = '';

      // Burbuja “pensando…”
      const thinkingId = 'thinking-' + Date.now();
      box.insertAdjacentHTML('beforeend',
        `<div class="bubble bot" id="${thinkingId}">
           <strong class="label">2DayMind:</strong>
           Estoy analizando tu pregunta con información reciente y fuentes confiables… ⏳
         </div>`
      );
      box.scrollTop = box.scrollHeight;

      try {
        const res = await fetch(form.action, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({
            mensaje: msg,
            pais: pais,
            ciudad: ciudad
          })
        });

        const raw = await res.text();
        let data = null;
        try { data = JSON.parse(raw); } catch (_) {}

        const thinkingEl = document.getElementById(thinkingId);
        if (thinkingEl) thinkingEl.remove();

        let texto;
        if (data && typeof data === 'object' && 'respuesta' in data) {
          texto = data.respuesta || '⚠️ Sin respuesta.';
        } else {
          texto = raw || '⚠️ Sin respuesta.';
        }

        box.insertAdjacentHTML('beforeend',
          `<div class="bubble bot">
             <strong class="label">2DayMind:</strong>
             ${texto}
           </div>`
        );
      } catch (err) {
        const thinkingEl = document.getElementById(thinkingId);
        if (thinkingEl) thinkingEl.remove();

        box.insertAdjacentHTML('beforeend',
          `<div class="bubble bot">
             <strong class="label">2DayMind:</strong>
             ❌ No pude conectarme al servidor. Intenta de nuevo en un momento.
           </div>`
        );
      }

      box.scrollTop = box.scrollHeight;
    });
  });
  </script>
</body>
</html>
