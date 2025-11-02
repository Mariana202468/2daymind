<?php
require_once __DIR__ . '/db.php';

function obtenerCogniciones() {
    global $pdo;

    try {
        $stmt = $pdo->query("SELECT * FROM cognicion ORDER BY fecha DESC LIMIT 10");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}
?>
