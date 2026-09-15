<?php

function normalizarTexto(string $texto): string {
    $texto = mb_strtolower(trim($texto), 'UTF-8');
    $reemplazos = [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u',
        'ñ' => 'n', 'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
    ];
    $texto = strtr($texto, $reemplazos);
    $texto = preg_replace('/[^a-z0-9\s]/', ' ', $texto);
    $texto = preg_replace('/\s+/', ' ', $texto);
    return trim($texto);
}

function calcularSimilitud(string $query, string $candidato): array {
    $normQuery = normalizarTexto($query);
    $normCand = normalizarTexto($candidato);

    if ($normQuery === '' || $normCand === '') {
        return ['score' => 0, 'motivo' => ''];
    }

    // 1. Coincidencia exacta
    if ($normQuery === $normCand) {
        return ['score' => 100, 'motivo' => 'Coincidencia exacta'];
    }

    $lenQ = mb_strlen($normQuery);
    $lenC = mb_strlen($normCand);

    // Palabras / tokens (mínimo 2 caracteres)
    $palabrasQ = array_values(array_filter(explode(' ', $normQuery), fn($w) => mb_strlen($w) >= 2));
    $palabrasC = array_values(array_filter(explode(' ', $normCand), fn($w) => mb_strlen($w) >= 2));

    // 2. Contención exacta de frase
    if ($lenQ >= 3 && strpos($normCand, $normQuery) !== false) {
        $score = 80 + round(($lenQ / max($lenC, 1)) * 15);
        return ['score' => (int) $score, 'motivo' => 'Nombre contenido'];
    }
    if ($lenC >= 3 && strpos($normQuery, $normCand) !== false) {
        $score = 80 + round(($lenC / max($lenQ, 1)) * 15);
        return ['score' => (int) $score, 'motivo' => 'Contiene al existente'];
    }

    // 3. Comparación por palabras (tokens) y permutaciones
    $comunes = array_intersect($palabrasQ, $palabrasC);
    $totalPalabras = max(count($palabrasQ), count($palabrasC));

    if (count($comunes) > 0 && count($comunes) === count($palabrasQ) && count($comunes) === count($palabrasC)) {
        return ['score' => 95, 'motivo' => 'Mismas palabras en distinto orden'];
    }

    // Comprobación de palabras con Levenshtein / similitud fonética
    $coincidenciasPalabras = 0;
    $palabrasDetectadas = [];

    foreach ($palabrasQ as $pq) {
        $mejorMatchPalabra = 0;
        $mejorPc = '';
        foreach ($palabrasC as $pc) {
            if ($pq === $pc) {
                $mejorMatchPalabra = 1.0;
                $mejorPc = $pc;
                break;
            }
            $lq = mb_strlen($pq);
            $lc = mb_strlen($pc);
            $levW = levenshtein($pq, $pc);
            $maxW = max($lq, $lc);
            if ($maxW >= 3 && $levW <= 1) {
                $matchRatio = 1.0 - ($levW / $maxW);
                if ($matchRatio > $mejorMatchPalabra) {
                    $mejorMatchPalabra = $matchRatio;
                    $mejorPc = $pc;
                }
            } elseif ($maxW >= 5 && $levW <= 2) {
                $matchRatio = 1.0 - ($levW / $maxW);
                if ($matchRatio > $mejorMatchPalabra) {
                    $mejorMatchPalabra = $matchRatio;
                    $mejorPc = $pc;
                }
            } elseif (metaphone($pq) === metaphone($pc) && $maxW >= 4) {
                $matchRatio = 0.85;
                if ($matchRatio > $mejorMatchPalabra) {
                    $mejorMatchPalabra = $matchRatio;
                    $mejorPc = $pc;
                }
            }
        }
        if ($mejorMatchPalabra >= 0.6) {
            $coincidenciasPalabras += $mejorMatchPalabra;
            $palabrasDetectadas[] = $mejorPc;
        }
    }

    $porcentajePalabras = count($palabrasQ) > 0 ? ($coincidenciasPalabras / max(count($palabrasQ), count($palabrasC))) * 100 : 0;
    $porcentajePalabrasQuery = count($palabrasQ) > 0 ? ($coincidenciasPalabras / count($palabrasQ)) * 100 : 0;

    // 4. Distancia global Levenshtein para cadenas completas
    $maxLen = max($lenQ, $lenC);
    $levGlobal = levenshtein($normQuery, $normCand);
    $levScore = max(0, 100 - (($levGlobal / max($maxLen, 1)) * 100));

    // Si la distancia global es muy corta (ej. 1 o 2 letras de diferencia)
    if ($levGlobal <= 2 && $maxLen >= 5) {
        $score = max(85, (int) round($levScore));
        return ['score' => $score, 'motivo' => 'Variación ortográfica / tipeo'];
    }

    if ($porcentajePalabras >= 80) {
        return ['score' => (int) round($porcentajePalabras), 'motivo' => 'Palabras muy similares (' . implode(', ', array_unique($palabrasDetectadas)) . ')'];
    }

    if ($porcentajePalabrasQuery >= 75 && count($palabrasC) > count($palabrasQ)) {
        $score = (int) round(65 + ($porcentajePalabrasQuery * 0.25));
        return ['score' => $score, 'motivo' => 'Palabras coincidentes (' . implode(', ', array_unique($palabrasDetectadas)) . ')'];
    }

    if ($porcentajePalabras >= 45) {
        return ['score' => (int) round($porcentajePalabras), 'motivo' => 'Posible similitud en palabras (' . implode(', ', array_unique($palabrasDetectadas)) . ')'];
    }

    if ($levScore >= 70 && $coincidenciasPalabras > 0) {
        return ['score' => (int) round($levScore), 'motivo' => 'Similitud de texto'];
    }

    return ['score' => 0, 'motivo' => ''];
}

$candidato = "Mario Perez";
$pruebas = [
    "Mario Perez",
    "mario perez",
    "Mario Pérez",
    "Perez Mario",
    "Mario Perz",
    "Marrio Perez",
    "Mario Peres",
    "Mario Perez S.A.",
    "Distribuidora Mario Perez",
    "Mario",
    "Perez",
    "Carlos Ramirez",
    "CIT",
    "Ares Sun",
    "Juan Perez",
    "Maria Perez",
    "Mario Gomez",
];

echo "Buscando similitudes para candidato '$candidato':\n\n";
foreach ($pruebas as $p) {
    $res = calcularSimilitud($p, $candidato);
    printf("Input: %-30s => Score: %3d%% | Motivo: %s\n", "'$p'", $res['score'], $res['motivo']);
}
