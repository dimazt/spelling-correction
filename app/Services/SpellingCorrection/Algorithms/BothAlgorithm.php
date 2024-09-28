<?php

namespace App\Services\SpellingCorrection\Algorithms;

class BothAlgorithm
{
    public static function getDistanceAndSimilarity($word, $kbbiWord)
    {
        // 1. Hitung jarak Levenshtein dan log setiap langkah
        $levenstain = Levenshtein::processAlgorithmLevenstain($word, $kbbiWord);

        // 2. Hitung kemiripan menggunakan similar_text dan log prosesnya
        $similiarText = Similarity::processAlgorithmSimilarity($word, $kbbiWord, true);

        // Return result and log
        return (object) [
            'distance' => $levenstain->distance,
            'similarity' => $similiarText->similarity,
            'logs' => [
                'levenstain' => $levenstain->logs,
                'similarity' => $similiarText->logs
            ]
        ];
    }

    public static function correctWord($word, $kbbiWords)
    {
        $closestWord = '';
        $shortestDistance = PHP_INT_MAX;
        $highestSimilarity = 75; // Persentase kemiripan tertinggi
        $closestLengthDifference = PHP_INT_MAX;

        // Cek apakah kata sudah ada di KBBI, jika ada kembalikan langsung
        if (in_array($word, $kbbiWords)) {
            return $word;
        }

        foreach ($kbbiWords as $kbbiWord) {
            // Hitung jarak Levenshtein dan similarity
            $result = self::getDistanceAndSimilarity($word, $kbbiWord);
            $levDistance = $result->distance;
            $similarity = $result->similarity;
            $lengthDifference = abs(strlen($word) - strlen($kbbiWord));

            // Jika Levenshtein 0 (kata cocok sempurna), kembalikan kata
            if ($levDistance == 0) {
                return $kbbiWord;
            }
            if ($similarity == 100) {
                return $kbbiWord;
            }
            // Jika similarity lebih besar, update kata terdekat
            if ($similarity > $highestSimilarity) {
                $closestWord = $kbbiWord;
                $highestSimilarity = $similarity;
                $shortestDistance = $levDistance;
                $closestLengthDifference = $lengthDifference;
            }
            // Jika similarity sama, cek Levenshtein distance
            elseif ($similarity == $highestSimilarity) {
                if ($levDistance < $shortestDistance) {
                    $closestWord = $kbbiWord;
                    $shortestDistance = $levDistance;
                    $closestLengthDifference = $lengthDifference;
                }
                // Jika Levenshtein juga sama, gunakan panjang kata
                elseif ($levDistance == $shortestDistance) {
                    if ($lengthDifference < $closestLengthDifference) {
                        $closestWord = $kbbiWord;
                        $closestLengthDifference = $lengthDifference;
                    }
                    // Jika semuanya sama, gunakan urutan alfabet
                    elseif ($lengthDifference == $closestLengthDifference && strcmp($kbbiWord, $closestWord) < 0) {
                        $closestWord = $kbbiWord;
                    }
                }
            }
        }

        return $closestWord ?: $word;
    }
}