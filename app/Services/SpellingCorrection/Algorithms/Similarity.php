<?php

namespace App\Services\SpellingCorrection\Algorithms;

class Similarity
{

    public static function correctWord($word, $kbbiWords)
    {
        $closestWord = '';
        $highestSimilarity = 75; // Persentase kemiripan tertinggi

        // Cek apakah kata sudah ada di KBBI, jika ada kembalikan langsung
        if (in_array($word, $kbbiWords)) {
            return $word;
        }

        foreach ($kbbiWords as $kbbiWord) {
            // Hitung jarak Levenshtein dan similarity
            $result = self::processAlgorithmSimilarity($word, $kbbiWord);
            $similarity = $result->similarity;

            // Jika Levenshtein 0 (kata cocok sempurna), kembalikan kata
            if ($similarity == 100) {
                return $kbbiWord;
            }

            // Mode "similarity only"
            if ($similarity > $highestSimilarity) {
                $closestWord = $kbbiWord;
                $highestSimilarity = $similarity;
            }
            // Jika similarity sama, gunakan urutan alfabet
            elseif ($similarity == $highestSimilarity && strcmp($kbbiWord, $closestWord) < 0) {
                $closestWord = $kbbiWord;
            }
        }

        return $closestWord ?: $word;
    }
    public static function processAlgorithmSimilarity($word1, $word2, $log = false)
    {
        similar_text($word1, string2: $word2, percent: $similarity);
        if ($log) {
            $logs = self::logProcessSimilarity($word1, $word2);
        }
        $logs[] = "Similarity calculated between '{$word1}' and '{$word2}': {$similarity}%";
        return (object) [
            'similarity' => $similarity,
            'logs' => $logs
        ];

    }

    public static function logProcessSimilarity($word1, $kbbi)
    {
        $logs[] = "Characters compared in both words:";
        for ($i = 0; $i < min(strlen($word1), strlen($kbbi)); $i++) {
            if ($word1[$i] === $kbbi[$i]) {
                $logs[] = "Matched character at position {$i}: '{$word1[$i]}'";
            } else {
                $logs[] = "Different character at position {$i}: '{$word1[$i]}' (input) vs '{$kbbi[$i]}' (KBBI)";
            }
        }

        if (strlen($word1) > strlen($kbbi)) {
            $logs[] = "Extra characters in input word: " . substr($word1, strlen($kbbi));
        } elseif (strlen($word1) < strlen($kbbi)) {
            $logs[] = "Extra characters in KBBI word: " . substr($kbbi, strlen($word1));
        }

        return $logs;
    }
}