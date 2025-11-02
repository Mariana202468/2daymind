<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>2DayMind – Asistente Inteligente</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #0d1117;
        color: white;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 100vh;
    }
    #chatBox {
        width: 80%;
        max-width: 700px;
        height: 70vh;
        background: #161b22;
        border-radius: 12px;
        padding: 20px;
        overflow-y: auto;
        margin-top: 30px;
        box-shadow: 0 0 15px rgba(0,0,0,0.3);
    }
    .user { color: #58a6ff; margin-bottom: 10px; }
    .bot { color: #c9d1d9; margin-bottom: 20px; }
    #inputBox {
        width: 80%;
        max-width: 700px;
        display: flex;
        margin-top: 15px;
    }
    input {
        flex: 1;
        padding: 12px;
        border-radius: 8px;
        border: none;
        outline: none;
    }
    button {
        background: #238636;
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 8px;
        margin-left: 10px;
        cursor: pointer;
    }
    button:hover { background: #2ea043; }
</style>
</head>
<body>

<h2>💡 2DayMind – Asistente Inteligente</h2>
<div id="chatBox"></div>

<div id="inputBox">
    <input type="text" id="mensaje" placeholder="Escribe tu pregunta...">
    <button onclick="enviarMensaje()">Enviar</button>
</div>

<script>
async function enviarMensaje() {
    const mensaje = document.getElementById('mensaje').value.trim();
    if (!mensaje) return;

    const chatBox = document.getElementById('chatBox');
    chatBox.innerHTML += `<div class="user"><b>Tú:</b> ${mensaje}</div>`;

    document.getElementById('mensaje').value = '';
    chatBox.scrollTop = chatBox.scrollHeight;

    try {
        const res = await fetch('http://localhost/2daymind/api/analizar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ mensaje })
        });
        const data = await res.json();
        chatBox.innerHTML += `<div class="bot"><b>2DayMind:</b> ${data.respuesta}</div>`;
        chatBox.scrollTop = chatBox.scrollHeight;
    } catch (err) {
        chatBox.innerHTML += `<div class="bot" style="color:red;"><b>Error:</b> No se pudo conectar.</div>`;
    }
}
</script>
</body>
</html>
