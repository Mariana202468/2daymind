<?php

function buscarEnWeb(string $query, int $maxFuentes = 3): string
{
    $query = urlencode($query);
    $url   = "https://api.duckduckgo.com/?q=$query&format=json&no_redirect=1&no_html=1";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        return "⚠️ No se pudo obtener información reciente.";
    }

    $data    = json_decode($response, true);
    $fuentes = [];

    // 1) Resumen principal
    if (!empty($data['AbstractText'])) {
        $fuentes[] = [
            'texto' => $data['AbstractText'],
            'url'   => $data['AbstractURL'] ?? null,
        ];
    }

    // 2) Related topics
    if (!empty($data['RelatedTopics'])) {
        foreach ($data['RelatedTopics'] as $topic) {
            if (isset($topic['FirstURL'], $topic['Text'])) {
                $fuentes[] = [
                    'texto' => $topic['Text'],
                    'url'   => $topic['FirstURL'],
                ];
            }
        }
    }

    if (!$fuentes) {
        return "⚠️ No se encontraron resultados relevantes en línea.";
    }

    // 3) Filtro de dominios “más confiables”
    $whitelist     = ['.gov', '.edu', '.org', 'who.int', 'un.org', 'worldbank.org'];
    $seleccionadas = [];

    foreach ($fuentes as $f) {
        if (empty($f['url'])) continue;

        $host = parse_url($f['url'], PHP_URL_HOST) ?? '';
        foreach ($whitelist as $dom) {
            if (str_ends_with($host, $dom)) {
                $seleccionadas[] = $f + ['host' => $host];
                break;
            }
        }
        if (count($seleccionadas) >= $maxFuentes) break;
    }

    // Si el filtro fue muy duro, usa lo que haya
    if (!$seleccionadas) {
        $seleccionadas = array_slice($fuentes, 0, $maxFuentes);
    }

    // 4) Construir contexto legible
    $contexto = "Fuentes web:\n";
    foreach ($seleccionadas as $i => $f) {
        $host = $f['host'] ?? parse_url($f['url'] ?? '', PHP_URL_HOST);
        $contexto .= "- Fuente " . ($i + 1) . " ($host): {$f['texto']} [{$f['url']}]\n";
    }

    return $contexto;
}
