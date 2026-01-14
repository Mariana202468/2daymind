<?php
// api/upload.php
require_once __DIR__ . '/../vendor/autoload.php';

header("Content-Type: application/json");

if (!isset($_FILES['documento'])) {
    echo json_encode(["error" => "No se recibió archivo"]);
    exit;
}

try {
    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseFile($_FILES['documento']['tmp_name']);
    $text = $pdf->getText();

    echo json_encode([
        "ok" => true, 
        "texto" => mb_substr($text, 0, 5000) // Enviamos los primeros 5000 caracteres
    ]);
} catch (\Exception $e) {
    echo json_encode(["error" => "No se pudo leer el PDF: " . $e->getMessage()]);
}