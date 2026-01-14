<?php
// includes/db.php - CONEXIÓN OFICIAL AWS RDS
$host = 'TU_PUNTO_DE_ENLACE_AQUI'; // El que acabas de copiar de la consola de AWS
$db   = 'xavier_memoria_db'; 
$user = 'admin'; 
$pass = 'xavier-memoria-db.c878qou2s6fq.us-east-1.rds.amazonaws.com'; // La que anotaste al crearla

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Esto asegura que la base y la tabla existan en AWS desde el primer segundo
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4");
    $pdo->exec("USE `$db` ");
    $pdo->exec("CREATE TABLE IF NOT EXISTS memoria_xavier (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id VARCHAR(50),
        rol ENUM('user', 'assistant'),
        contenido TEXT,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id)
    )");
} catch (PDOException $e) {
    die("❌ Error de conexión en AWS: " . $e->getMessage());
}