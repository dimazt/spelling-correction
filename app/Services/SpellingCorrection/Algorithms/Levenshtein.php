<?php

namespace App\Services\SpellingCorrection\Algorithms;

class Levenshtein
{
    public static function correctWord($word, $kbbiWords)
    {
        $closestWord = '';
        $shortestDistance = PHP_INT_MAX;
        $closestLengthDifference = PHP_INT_MAX;

        // Cek apakah kata sudah ada di KBBI, jika ada kembalikan langsung
        if (in_array($word, $kbbiWords)) {
            return $word;
        }

        foreach ($kbbiWords as $kbbiWord) {
            // Hitung jarak Levenshtein dan similarity
            $result = self::processAlgorithmLevenstain($word, $kbbiWord);
            $levDistance = $result->distance;
            $lengthDifference = abs(strlen($word) - strlen($kbbiWord));

            // Jika Levenshtein 0 (kata cocok sempurna), kembalikan kata
            if ($levDistance == 0) {
                return $kbbiWord;
            }

            // Mode "distance only"
            if ($levDistance < $shortestDistance) {
                $closestWord = $kbbiWord;
                $shortestDistance = $levDistance;
                $closestLengthDifference = $lengthDifference;
            }
            // Jika distance sama, gunakan panjang kata
            elseif ($levDistance == $shortestDistance) {
                if ($lengthDifference < $closestLengthDifference) {
                    $closestWord = $kbbiWord;
                    $closestLengthDifference = $lengthDifference;
                }
                // Jika panjang kata sama, gunakan urutan alfabet
                elseif ($lengthDifference == $closestLengthDifference && strcmp($kbbiWord, $closestWord) < 0) {
                    $closestWord = $kbbiWord;
                }
            }

        }

        return $closestWord ?: $word;
    }
    public static function manualLevenshteinWithLog($word1, $word2)
    {
        $logs = [];
        $len1 = strlen($word1);
        $len2 = strlen($word2);

        // Inisialisasi matriks jarak
        $distanceMatrix = [];

        // Isi matriks untuk kasus basis
        for ($i = 0; $i <= $len1; $i++) {
            $distanceMatrix[$i][0] = $i;
        }
        for ($j = 0; $j <= $len2; $j++) {
            $distanceMatrix[0][$j] = $j;
        }

        // Log inisialisasi matriks
        $logs[] = "Initial distance matrix setup:";
        $logs[] = json_encode($distanceMatrix);

        // Isi matriks dengan nilai jarak Levenshtein
        for ($i = 1; $i <= $len1; $i++) {
            for ($j = 1; $j <= $len2; $j++) {
                $cost = ($word1[$i - 1] == $word2[$j - 1]) ? 0 : 1;

                $distanceMatrix[$i][$j] = min(
                    $distanceMatrix[$i - 1][$j] + 1,    // penghapusan
                    $distanceMatrix[$i][$j - 1] + 1,    // penambahan
                    $distanceMatrix[$i - 1][$j - 1] + $cost // penggantian
                );

                // Log perubahan dalam matriks
                $operation = ($cost === 0) ? "match" : "substitution";
                if ($distanceMatrix[$i][$j] == $distanceMatrix[$i - 1][$j] + 1) {
                    $operation = "deletion";
                } elseif ($distanceMatrix[$i][$j] == $distanceMatrix[$i][$j - 1] + 1) {
                    $operation = "insertion";
                }
                $logs[] = "Processed '{$word1[$i - 1]}' and '{$word2[$j - 1]}', operation: {$operation}, matrix updated at position ({$i}, {$j}): " . $distanceMatrix[$i][$j];
            }
        }

        // Log matriks akhir
        $logs[] = "Final distance matrix:";
        $logs[] = json_encode($distanceMatrix);

        // Jarak Levenshtein adalah nilai di sudut kanan bawah matriks
        $levenshteinDistance = $distanceMatrix[$len1][$len2];
        $maxLength = max($len1, $len2);

        if ($maxLength == 0) {
            $similarity = 100;
        }
        $similarity = (1 - $levenshteinDistance / $maxLength) * 100;
        // Kembalikan jarak Levenshtein dan log
        return (object) [
            'distance' => $levenshteinDistance,
            'matrix' => $distanceMatrix,
            'similarity' => $similarity,
            'logs' => $logs
        ];
    }

    public static function processAlgorithmLevenstain($word1, $word2, $log = false)
    {
        $distanceAndLog = self::manualLevenshteinWithLog($word1, $word2);
        if (!$log) {
            $logs[] = "Levenshtein Distance calculated between '{$word1}' and '{$word2}': {$distanceAndLog->distance}";
            $distanceAndLog->logs = $logs;
        }
        // else {
        //     $distance = levenshtein($word1, $word2);
        // }
        return (object) $distanceAndLog;
    }
}