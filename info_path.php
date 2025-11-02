<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>📁 Rutas activas en el servidor</h2><hr>";
echo "<p><b>__DIR__:</b> " . __DIR__ . "</p>";
echo "<p><b>DOCUMENT_ROOT:</b> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><b>PHP_SELF:</b> " . $_SERVER['PHP_SELF'] . "</p>";
echo "<p><b>SCRIPT_FILENAME:</b> " . $_SERVER['SCRIPT_FILENAME'] . "</p>";
?>
