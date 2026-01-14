<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>2DayMind – Safari Compatible</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; background: #f0f2f5; height: 100vh; display: flex; align-items: center; justify-content: center; gap: 20px; margin: 0; }
        
        /* Caja del Chat */
        #chat-section { width: 350px; height: 600px; background: white; border-radius: 20px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        #messages { flex: 1; padding: 20px; overflow-y: auto; background: #f9f9f9; }
        .msg { padding: 10px; border-radius: 10px; margin-bottom: 8px; font-size: 14px; }
        .bot { background: #eee; color: #333; align-self: flex-start; }
        .user { background: #007bff; color: white; align-self: flex-end; }
        #input-area { padding: 15px; border-top: 1px solid #ddd; display: flex; gap: 5px; }
        input { flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        
        /* Caja del Avatar */
        #avatar-container { width: 350px; height: 600px; background: black; border-radius: 20px; position: relative; border: 4px solid #d4a017; overflow: hidden; }
        #avatar-canvas { width: 100%; height: 100%; }
        
        /* PANTALLA DE INICIO (Para desbloquear Safari) */
        #overlay { 
            position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(0,0,0,0.85); color: white; 
            display: flex; flex-direction: column; 
            justify-content: center; align-items: center; z-index: 10; 
            text-align: center;
        }
        button.start-btn { 
            background: #d4a017; color: white; border: none; padding: 15px 30px; 
            font-size: 16px; border-radius: 30px; cursor: pointer; font-weight: bold; margin-top: 15px; 
        }
        #debug-msg { color: #ff6b6b; margin-top: 20px; font-size: 12px; padding: 0 20px; }
    </style>
    
    <script type="importmap">
    { "imports": { "three": "https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js" } }
    </script>
</head>
<body>

<div id="chat-section">
    <div style="padding:15px; background:#d4a017; color:white; text-align:center; font-weight:bold;">2DayMind</div>
    <div id="messages"><div class="msg bot">¡Hola! Activa el avatar a la derecha para comenzar.</div></div>
    <div id="input-area">
        <input type="text" id="user-input" placeholder="Escribe aquí...">
        <button onclick="enviar()">Enviar</button>
    </div>
</div>

<div id="avatar-container">
    <div id="overlay">
        <h2>Modo Seguro</h2>
        <p>Safari requiere permiso para iniciar</p>
        <button class="start-btn" onclick="iniciarAvatar()">▶ ACTIVAR AVATAR</button>
        <div id="debug-msg"></div>
    </div>
    <div id="avatar-canvas"></div>
</div>

<script type="module">
    import { TalkingHead } from "https://cdn.jsdelivr.net/gh/met4citizen/TalkingHead@1.1/modules/talkinghead.mjs?v=1";

    let head; 

    // Función para iniciar SOLO cuando tú haces clic
    window.iniciarAvatar = async function() {
        const btn = document.querySelector('.start-btn');
        const debug = document.getElementById('debug-msg');
        
        btn.innerText = "Cargando...";
        btn.disabled = true;

        try {
            const node = document.getElementById('avatar-canvas');
            
            head = new TalkingHead(node, {
                ttsEndpoint: "https://api.talkinghead.online/tts/google_tts.php",
                lipsyncLang: 'es',
                cameraView: "upper"
            });

            // Cargamos el modelo masculino estándar
            await head.showAvatar({
                url: 'https://models.readyplayer.me/64bfa15f0e72c63d7c3934a6.glb?morphTargets=ARKit&textureAtlas=1024',
                body: 'M',
                avatarMood: 'neutral'
            });

            // Si llegamos aquí, todo funcionó: Ocultamos el botón
            document.getElementById('overlay').style.display = 'none';

        } catch (error) {
            console.error(error); // Muestra error en la consola
            btn.innerText = "Reintentar";
            btn.disabled = false;
            // Muestra el error en pantalla para que me lo leas
            debug.innerText = "Error: " + error.message; 
        }
    }

    window.enviar = async function() {
        const input = document.getElementById('user-input');
        const txt = input.value.trim();
        if(!txt) return;

        const msgs = document.getElementById('messages');
        msgs.innerHTML += `<div class="msg user">${txt}</div>`;
        input.value = '';

        try {
            // Conexión con tu backend PHP
            const res = await fetch('api/analizar.php', {
                method: 'POST', 
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({mensaje: txt})
            });
            const data = await res.json();
            const respuesta = data.respuesta || "Sin respuesta";

            msgs.innerHTML += `<div class="msg bot">${respuesta}</div>`;
            
            // Hacemos hablar al avatar si ya cargó
            if(head) head.speakText(respuesta);

        } catch(e) {
            msgs.innerHTML += `<div class="msg bot" style="color:red">Error de conexión</div>`;
        }
    }
</script>

</body>
</html>