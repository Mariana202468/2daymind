<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (isset($_SESSION['nombre'])) {
  header('Location: index.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $_SESSION['nombre'] = htmlspecialchars($_POST['nombre']);
  header('Location: index.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Bienvenido a 2DayMind</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --azul-oscuro: #0b1f2d;
      --azul-profundo: #102c44;
      --metal-azul: #2a6f97;
      --dorado-metal: #c9a227;
      --gris-azulado: #9fb3c8;
      --texto-claro: #e6edf3;
    }

    body {
      margin: 0;
      height: 100vh;
      background: radial-gradient(circle at center, var(--azul-profundo), var(--azul-oscuro));
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: 'Segoe UI', sans-serif;
      color: var(--texto-claro);
      overflow: hidden;
    }

    .card {
      background: rgba(10, 25, 40, 0.85);
      border: 1px solid rgba(201,162,39,0.2);
      border-radius: 20px;
      padding: 40px 35px;
      text-align: center;
      box-shadow: 0 0 25px rgba(201,162,39,0.15);
      animation: fadeIn 1.2s ease-in-out;
      width: 340px;
    }

    img {
      border-radius: 8px;
      box-shadow: 0 0 25px rgba(201,162,39,0.4);
      animation: glow 3s infinite alternate;
    }

    h2 {
      font-weight: 700;
      font-size: 1.6rem;
      margin-top: 20px;
      background: linear-gradient(90deg, var(--dorado-metal), var(--metal-azul));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    p {
      font-size: 0.9rem;
      color: var(--gris-azulado);
    }

    input {
      background: #0c1c29;
      color: #fff;
      border: 1px solid rgba(201,162,39,0.3);
      border-radius: 8px;
      text-align: center;
    }

    input:focus {
      border-color: var(--dorado-metal);
      box-shadow: 0 0 10px rgba(201,162,39,0.4);
      outline: none;
    }

    .btn-gold {
      background: linear-gradient(135deg, var(--dorado-metal), #d4af37);
      color: #1a1e24;
      font-weight: 600;
      border: none;
      border-radius: 10px;
      transition: all 0.3s ease;
      box-shadow: 0 0 15px rgba(201,162,39,0.3);
    }

    .btn-gold:hover {
      background: linear-gradient(135deg, #d4af37, var(--dorado-metal));
      transform: scale(1.03);
      box-shadow: 0 0 25px rgba(201,162,39,0.5);
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes glow {
      from { box-shadow: 0 0 10px rgba(201,162,39,0.4); }
      to { box-shadow: 0 0 30px rgba(201,162,39,0.8); }
    }

  </style>
</head>
<body>
  <div class="card">
    <img src="assets/logo.png" alt="Logo 2DayMind" width="120" class="mb-3">
    <h2>👋 Bienvenido a 2DayMind</h2>
    <p>Tu asesor inteligente para los mercados globales</p>

    <form method="POST" class="mt-3">
      <input type="text" name="nombre" class="form-control mb-3" placeholder="Escribe tu nombre..." required>
      <button type="submit" class="btn btn-gold w-100">Continuar</button>
    </form>
  </div>
</body>
</html>
