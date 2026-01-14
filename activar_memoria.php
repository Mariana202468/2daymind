<?php
require_once 'includes/db.php';
try {
    // 1. Crear la base de datos si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS 2dyamind");
    $pdo->exec("USE 2dyamind");

    // 2. Crear la tabla de memoria
    $pdo->exec("CREATE TABLE IF NOT EXISTS memoria_xavier (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id VARCHAR(50),
        rol ENUM('user', 'assistant'),
        contenido TEXT,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id)
    )");
    echo "✅ ¡Memoria activada con éxito en DBngin!";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}