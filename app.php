<?php
// 🛠️ ACTIVADOR DE ERRORES (Para evitar pantallas blancas)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Conexión a la base de datos para asegurar que el sistema esté listo
require_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>2DayMind | Xavier AI Pro Global</title>
    <style>
        /* Estilos corporativos de Mariana */
        :root { 
            --white-elegant: #F0F4F9; --dark-blue: #002D5A; 
            --bright-blue: #007BFF; --yellow: #FFCC00;
            --text-main: #1f1f1f; --border: #dde3ea;
        }
        body { margin: 0; height: 100vh; background: var(--white-elegant); color: var(--text-main); font-family: sans-serif; display: flex; overflow: hidden; }
        #avatar-container { flex: 1; position: relative; background: #fff; }
        #avatar-canvas { width: 100%; height: 100%; }
        #chat-panel { width: 420px; background: #FFFFFF; display: flex; flex-direction: column; border-left: 1px solid var(--border); z-index: 20; }
        .panel-header { padding: 15px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        #messages { flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 20px; }
        .msg { padding: 12px 16px; font-size: 14.5px; line-height: 1.6; max-width: 90%; border-radius: 12px; }
        .bot { background: #f1f8ff; align-self: flex-start; }
        .user { background: #e3e3e3; align-self: flex-end; }
        #input-area-container { padding: 15px 20px 30px; background: #fff; }
        #input-area { display: flex; align-items: center; background: var(--white-elegant); border-radius: 28px; padding: 5px 15px; gap: 10px; }
        input[type="text"] { flex: 1; border: none; outline: none; background: transparent; padding: 10px; font-size: 15px; }
        .icon-btn { background: transparent; border: none; cursor: pointer; font-size: 20px; color: var(--dark-blue); transition: 0.3s; }
        .recording { color: #d93025 !important; transform: scale(1.2); } /* Feedback visual para el micro */
        #doc-indicator { font-size: 11px; color: var(--bright-blue); padding: 5px 20px; display: none; font-weight: bold; }
    </style>
    <script type="importmap">
    { "imports": { "three": "https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js", "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/" } }
    </script>
</head>
<body>

<div id="avatar-container">
    <div id="avatar-canvas"></div>
    <div id="overlay" style="position:absolute; top:0; width:100%; height:100%; background:#fff; z-index:100; display:flex; justify-content:center; align-items:center; flex-direction:column;">
        <h2 style="color:var(--dark-blue);">Xavier AI Pro</h2>
        <button onclick="start()" style="padding:15px 50px; background:var(--dark-blue); color:#fff; border:none; border-radius:30px; cursor:pointer; font-weight:bold;">CONECTAR SISTEMA</button>
    </div>
</div>

<div id="chat-panel">
    <div class="panel-header">
        <div class="header-title">Xavier AI</div>
        <select id="lang-select" class="lang-selector">
            <option value="auto">🌐 Auto-detectar</option>
            <option value="es">Español</option>
            <option value="en">English</option>
        </select>
    </div>
    <div id="messages">
        <div class="msg bot">Hola Mariana, soy Xavier. Mi micrófono está listo para dictados largos y puedo leer tus PDFs.</div>
    </div>
    <div id="doc-indicator">📄 PDF cargado y procesado</div>
    <div id="input-area-container">
        <div id="input-area">
            <button class="icon-btn" title="Adjuntar PDF" onclick="document.getElementById('file-input').click()">📎</button>
            <input type="file" id="file-input" hidden accept=".pdf" onchange="subirArchivo()">
            
            <input type="text" id="user-input" placeholder="Hablemos o escribe..." onkeypress="if(event.key==='Enter') enviar()">
            
            <button id="mic-btn" class="icon-btn" title="Micrófono" onclick="toggleDictado()">🎤</button>
            <button class="icon-btn" onclick="enviar()" style="color:var(--bright-blue)">➤</button>
        </div>
    </div>
</div>

<script type="module">
    import { TalkingHead } from "https://cdn.jsdelivr.net/gh/met4citizen/TalkingHead@1.1/modules/talkinghead.mjs?v=1";
    let head; 
    let textoDocumento = "";

    // 🆔 IDENTIDAD ÚNICA PARA LA MEMORIA PERMANENTE
    let xavierId = localStorage.getItem('xavier_id');
    if (!xavierId) {
        xavierId = 'USR-' + Math.random().toString(36).substr(2, 9).toUpperCase();
        localStorage.setItem('xavier_id', xavierId);
    }

    // Inicializar Avatar (Ready Player Me)
    window.start = async function() {
        const node = document.getElementById('avatar-canvas');
        head = new TalkingHead(node, {
            cameraView: "upper",
            ttsEndpoint: "api/tts.php", 
            tts: async (text) => {
                const res = await fetch("api/tts.php", { 
                    method: "POST", 
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ text }) 
                });
                return await (await res.blob()).arrayBuffer();
            }
        });
        await head.showAvatar({ url: 'https://models.readyplayer.me/695c2eb90ca398caea3efe26.glb?morphTargets=ARKit', body: 'M' });
        document.getElementById('overlay').style.display = 'none';
    }

    // 🎙️ RECONOCIMIENTO DE VOZ (DISEÑADO PARA CUBRIR 3 MINUTOS)
    const recognition = (window.SpeechRecognition || window.webkitSpeechRecognition) ? 
        new (window.SpeechRecognition || window.webkitSpeechRecognition)() : null;

    if (recognition) {
        recognition.continuous = true; // No se corta al dejar de hablar un segundo
        recognition.interimResults = true; // Muestra resultados en tiempo real

        recognition.onresult = (e) => {
            let transcript = '';
            for (let i = e.resultIndex; i < e.results.length; ++i) {
                transcript += e.results[i][0].transcript;
            }
            document.getElementById('user-input').value = transcript;
        };

        recognition.onend = () => {
            const btn = document.getElementById('mic-btn');
            if (btn.classList.contains('recording')) {
                recognition.start(); // Reinicio automático para asegurar los 3 minutos
            }
        };
    }

    window.toggleDictado = () => {
        const btn = document.getElementById('mic-btn');
        if (!btn.classList.contains('recording')) {
            btn.classList.add('recording');
            recognition.lang = document.getElementById('lang-select').value === 'en' ? 'en-US' : 'es-ES';
            recognition.start();
        } else {
            btn.classList.remove('recording');
            recognition.stop();
        }
    };

    // 📄 SUBIR Y LEER CONTENIDO DE PDF
    window.subirArchivo = async function() {
        const indicator = document.getElementById('doc-indicator');
        const file = document.getElementById('file-input').files[0];
        if (!file) return;

        indicator.innerText = "⏳ Leyendo PDF...";
        indicator.style.display = 'block';

        const formData = new FormData();
        formData.append('documento', file);

        try {
            const res = await fetch('api/upload.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.ok) {
                textoDocumento = data.texto; // Aquí se guarda lo que Xavier leyó
                indicator.innerText = "✅ PDF cargado y analizado";
            } else {
                indicator.innerText = "❌ Error: " + data.error;
            }
        } catch (e) {
            indicator.innerText = "❌ Error de conexión al leer PDF";
        }
    }

    // 🚀 ENVÍO CON MEMORIA E IDENTIDAD
    window.enviar = async function() {
        const input = document.getElementById('user-input');
        const txt = input.value.trim();
        if (!txt) return;

        // Mostrar mensaje en burbuja
        const msgs = document.getElementById('messages');
        msgs.innerHTML += `<div class="msg user">${txt}</div>`;
        input.value = '';
        msgs.scrollTop = msgs.scrollHeight;

        const formData = new FormData();
        
        // Unir el texto del PDF con la pregunta del usuario
        const mensajeFinal = textoDocumento 
            ? `[MEMORIA DE DOCUMENTO: ${textoDocumento}] Pregunta: ${txt}` 
            : txt;

        formData.append('mensaje', mensajeFinal);
        formData.append('idioma_preferido', document.getElementById('lang-select').value);
        formData.append('user_id', xavierId); // <--- IDENTIFICACIÓN PERMANENTE

        try {
            const res = await fetch('api/analizar.php?sector=salud', { method: 'POST', body: formData });
            const data = await res.json();
            
            msgs.innerHTML += `<div class="msg bot">${data.respuesta}</div>`;
            msgs.scrollTop = msgs.scrollHeight;
            
            if (head) head.speakText(data.respuesta);
            
            // Limpiar memoria temporal del PDF tras la respuesta para no saturar
            textoDocumento = ""; 
            document.getElementById('doc-indicator').style.display = 'none';

        } catch (e) {
            console.error("Error en Xavier:", e);
        }
    }
</script>
</body>
</html>