<?php
/**
 * Búsqueda web sencilla usando DuckDuckGo Instant Answer.
 * Devuelve un texto con RESUMEN + hasta 3 resultados con enlace.
 *
 * OJO: no imprime nada, solo devuelve string para que OpenAI lo use como contexto.
 */
function buscarEnWeb(string $query, ?string $pais = null, ?string $sector = null): string
{
    // Enriquecer la query con país / sector si existen
    $fullQuery = $query;
    if ($sector) {
        $fullQuery .= ' ' . $sector;
    }
    if ($pais && strcasecmp($pais, 'Global') !== 0) {
        $fullQuery .= ' ' . $pais;
    }

    $q = urlencode($fullQuery);

    // API pública de DuckDuckGo
    $url = "https://api.duckduckgo.com/?q={$q}&format=json&no_redirect=1&no_html=1";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        // Sin contexto web, mejor devolver vacío que un mensaje de error
        return '';
    }

    $data = json_decode($response, true);
    if (!is_array($data)) {
        return '';
    }

    $trozos = [];

    // Resumen principal si existe
    if (!empty($data['AbstractText'])) {
        $trozos[] = "Resumen: " . $data['AbstractText'];
    }

    if (!empty($data['AbstractURL'])) {
        $trozos[] = "Fuente principal: " . $data['AbstractURL'];
    }

    // RelatedTopics (hasta 3)
    if (!empty($data['RelatedTopics']) && is_array($data['RelatedTopics'])) {
        $count = 0;
        foreach ($data['RelatedTopics'] as $topic) {
            if ($count >= 3) {
                break;
            }

            // A veces vienen anidados en 'Topics'
            if (isset($topic['Topics']) && is_array($topic['Topics'])) {
                foreach ($topic['Topics'] as $sub) {
                    if ($count >= 3) {
                        break;
                    }
                    if (!empty($sub['Text'])) {
                        $line = "- " . $sub['Text'];
                        if (!empty($sub['FirstURL'])) {
                            $line .= " (" . $sub['FirstURL'] . ")";
                        }
                        $trozos[] = $line;
                        $count++;
                    }
                }
            } else {
                if (!empty($topic['Text'])) {
                    $line = "- " . $topic['Text'];
                    if (!empty($topic['FirstURL'])) {
                        $line .= " (" . $topic['FirstURL'] . ")";
                    }
                    $trozos[] = $line;
                    $count++;
                }
            }
        }
    }

    return trim(implode("\n", $trozos));
}
