<?php

namespace App\Models;

use Core\Model;

class Proveedor extends Model
{
    protected static string $table = 'proveedores';

    public static function activos(): array
    {
        $stmt = self::db()->query("SELECT * FROM proveedores WHERE activo = 1 ORDER BY nombre");
        return $stmt->fetchAll();
    }

    /**
     * Solo los proveedores marcados como "habilitado_proforma" pueden
     * continuar el proceso más allá de Gestiones — es decir, aparecer
     * como opción al crear una Proforma (venga de una cotización o de
     * una mensualidad).
     */
    public static function habilitadosParaProforma(): array
    {
        $stmt = self::db()->query(
            "SELECT * FROM proveedores WHERE activo = 1 AND habilitado_proforma = 1 ORDER BY nombre"
        );
        return $stmt->fetchAll();
    }

    public static function estaHabilitadoParaProforma(int $id): bool
    {
        $stmt = self::db()->prepare('SELECT habilitado_proforma FROM proveedores WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public static function contarGestiones(int $id): int
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM gestiones WHERE proveedor_id = :id AND eliminado_en IS NULL');
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn();
    }

    public static function contarProformas(int $id): int
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM proformas WHERE proveedor_id = :id AND eliminado_en IS NULL');
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn();
    }

    public static function allConEstadisticas(): array
    {
        $sql = "SELECT p.*,
                       (SELECT COUNT(*) FROM gestiones g WHERE g.proveedor_id = p.id AND g.eliminado_en IS NULL) AS total_gestiones,
                       (SELECT COUNT(*) FROM proformas pr WHERE pr.proveedor_id = p.id AND pr.eliminado_en IS NULL) AS total_proformas
                FROM proveedores p
                ORDER BY p.nombre ASC";
        return self::db()->query($sql)->fetchAll();
    }

    public static function normalizarTexto(string $texto): string
    {
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

    public static function calcularSimilitud(string $query, string $candidato): array
    {
        $normQuery = self::normalizarTexto($query);
        $normCand = self::normalizarTexto($candidato);

        if ($normQuery === '' || $normCand === '') {
            return ['score' => 0, 'motivo' => '', 'tipo' => 'ninguno'];
        }

        // 1. Coincidencia exacta normalizada
        if ($normQuery === $normCand) {
            return ['score' => 100, 'motivo' => 'Coincidencia exacta', 'tipo' => 'exacto'];
        }

        $lenQ = mb_strlen($normQuery);
        $lenC = mb_strlen($normCand);

        // Palabras / tokens (mínimo 2 caracteres)
        $palabrasQ = array_values(array_filter(explode(' ', $normQuery), fn($w) => mb_strlen($w) >= 2));
        $palabrasC = array_values(array_filter(explode(' ', $normCand), fn($w) => mb_strlen($w) >= 2));

        // 2. Contención exacta de frase
        if ($lenQ >= 3 && strpos($normCand, $normQuery) !== false) {
            $score = 80 + round(($lenQ / max($lenC, 1)) * 15);
            return ['score' => (int) $score, 'motivo' => 'Nombre contenido', 'tipo' => 'contenido'];
        }
        if ($lenC >= 3 && strpos($normQuery, $normCand) !== false) {
            $score = 80 + round(($lenC / max($lenQ, 1)) * 15);
            return ['score' => (int) $score, 'motivo' => 'Contiene al existente', 'tipo' => 'contenido'];
        }

        // 3. Comparación por palabras (tokens) y permutaciones
        $comunes = array_intersect($palabrasQ, $palabrasC);

        if (count($comunes) > 0 && count($comunes) === count($palabrasQ) && count($comunes) === count($palabrasC)) {
            return ['score' => 95, 'motivo' => 'Mismas palabras en distinto orden', 'tipo' => 'permutacion'];
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
        $levGlobal = ($maxLen <= 255) ? levenshtein($normQuery, $normCand) : 999;
        $levScore = max(0, 100 - (($levGlobal / max($maxLen, 1)) * 100));

        if ($levGlobal <= 2 && $maxLen >= 5) {
            $score = max(85, (int) round($levScore));
            return ['score' => $score, 'motivo' => 'Variación ortográfica / tipeo', 'tipo' => 'tipeo'];
        }

        if ($porcentajePalabras >= 80) {
            return ['score' => (int) round($porcentajePalabras), 'motivo' => 'Palabras muy similares (' . implode(', ', array_unique($palabrasDetectadas)) . ')', 'tipo' => 'similar'];
        }

        if ($porcentajePalabrasQuery >= 75 && count($palabrasC) > count($palabrasQ)) {
            $score = (int) round(65 + ($porcentajePalabrasQuery * 0.25));
            return ['score' => $score, 'motivo' => 'Palabras coincidentes (' . implode(', ', array_unique($palabrasDetectadas)) . ')', 'tipo' => 'similar'];
        }

        if ($porcentajePalabras >= 45) {
            return ['score' => (int) round($porcentajePalabras), 'motivo' => 'Posible similitud en palabras (' . implode(', ', array_unique($palabrasDetectadas)) . ')', 'tipo' => 'posible'];
        }

        if ($levScore >= 70 && $coincidenciasPalabras > 0) {
            return ['score' => (int) round($levScore), 'motivo' => 'Similitud de texto', 'tipo' => 'similar'];
        }

        return ['score' => 0, 'motivo' => '', 'tipo' => 'ninguno'];
    }

    public static function buscarSimilares(string $nombre, ?int $excluirId = null, int $limite = 8): array
    {
        $nombre = trim($nombre);
        if (mb_strlen($nombre) < 2) {
            return [];
        }

        $sql = "SELECT id, nombre, activo, habilitado_proforma FROM proveedores";
        if ($excluirId !== null && $excluirId > 0) {
            $sql .= " WHERE id != " . (int) $excluirId;
        }
        $sql .= " ORDER BY nombre ASC";

        $todos = self::db()->query($sql)->fetchAll();
        $similares = [];

        foreach ($todos as $p) {
            $calculo = self::calcularSimilitud($nombre, $p['nombre']);
            if ($calculo['score'] >= 45) {
                $p['score'] = $calculo['score'];
                $p['motivo'] = $calculo['motivo'];
                $p['tipo_similitud'] = $calculo['tipo'];
                $similares[] = $p;
            }
        }

        usort($similares, function ($a, $b) {
            if ($b['score'] === $a['score']) {
                return strcmp($a['nombre'], $b['nombre']);
            }
            return $b['score'] <=> $a['score'];
        });

        return array_slice($similares, 0, $limite);
    }
}
