<?php
// ✅ Protege para evitar que se vuelva a declarar si se carga más de una vez
if (!function_exists('guardarCognicion')) {
    function guardarCognicion($pregunta, $respuesta) {
        $archivo = __DIR__ . '/modelo_cognitivo.txt';
        $entrada = "\n[Usuario]: $pregunta\n[2DayMind]: $respuesta\n";
        file_put_contents($archivo, $entrada, FILE_APPEND);
    }
}

if (!function_exists('obtenerContextoCognitivo')) {
    function obtenerContextoCognitivo() {
        $archivo = __DIR__ . '/modelo_cognitivo.txt';
        if (!file_exists($archivo)) return '';
        return file_get_contents($archivo);
    }
}
?>
