<?php
// includes/busqueda_web.php

/**
 * Busca en la web usando la API pública de DuckDuckGo
 * y devuelve un texto con resumen + algunas fuentes.
 */
function buscarEnWeb(string $query): string
{
    $query = trim($query);
    if ($query === '') {
        return '';
    }

    $url = 'https://api.duckduckgo.com/?' . http_build_query([
        'q'           => $query,
        'format'      => 'json',
        'no_redirect' => 1,
        'no_html'     => 1,
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_USERAGENT      => '2DayMind/1.0 (+https://2daymind.com)',
    ]);

    $response = curl_exec($ch);
    $err      = curl_error($ch);
    curl_close($ch);

    if ($err || !$response) {
        return '';
    }

    $data = json_decode($response, true);
    if (!is_array($data)) {
        return '';
    }

    $partes = [];

    if (!empty($data['AbstractText'])) {
        $partes[] = "Resumen:\n" . $data['AbstractText'];
    }

    // coger algunas fuentes con título + URL
    $fuentes = [];
    if (!empty($data['RelatedTopics']) && is_array($data['RelatedTopics'])) {
        foreach ($data['RelatedTopics'] as $topic) {
            if (!empty($topic['Text']) && !empty($topic['FirstURL'])) {
                $fuentes[] = '- ' . $topic['Text'] . ' (' . $topic['FirstURL'] . ')';
            }
            if (count($fuentes) >= 5) {
                break;
            }
        }
    }

    if ($fuentes) {
        $partes[] = "Fuentes:\n" . implode("\n", $fuentes);
    }

    return implode("\n\n", $partes);
}
