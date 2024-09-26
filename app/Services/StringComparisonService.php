<?php

namespace App\Services;

class StringComparisonService
{
    public function levenshteinDistance($s1, $s2)
    {
        $lenS1 = strlen($s1);
        $lenS2 = strlen($s2);

        // Membuat matriks untuk menyimpan jarak
        $matrix = array();

        // Inisialisasi matriks
        for ($i = 0; $i <= $lenS1; $i++) {
            $matrix[$i] = array();
            $matrix[$i][0] = $i; // Mengisi kolom pertama
        }
        for ($j = 0; $j <= $lenS2; $j++) {
            $matrix[0][$j] = $j; // Mengisi baris pertama
        }

        // Mengisi matriks
        for ($i = 1; $i <= $lenS1; $i++) {
            for ($j = 1; $j <= $lenS2; $j++) {
                if ($s1[$i - 1] === $s2[$j - 1]) {
                    $matrix[$i][$j] = $matrix[$i - 1][$j - 1]; // Tidak ada operasi diperlukan
                } else {
                    $matrix[$i][$j] = min(
                        $matrix[$i - 1][$j] + 1, // Penghapusan
                        $matrix[$i][$j - 1] + 1, // Penyisipan
                        $matrix[$i - 1][$j - 1] + 1 // Penggantian
                    );
                }
            }
        }

        return $matrix;
    }

    public function calculateSimilarity($s1, $s2)
    {
        $distanceMatrix = $this->levenshteinDistance($s1, $s2);
        $distance = $distanceMatrix[strlen($s1)][strlen($s2)];
        $maxLength = max(strlen($s1), strlen($s2));

        if ($maxLength == 0) return 100; // Jika kedua string kosong
        $similarity = (1 - $distance / $maxLength) * 100;

        return $similarity;
    }

    public function getDistanceAndSimilarity($s1, $s2)
    {
        $distanceMatrix = $this->levenshteinDistance($s1, $s2);
        $similarity = $this->calculateSimilarity($s1, $s2);

        return [
            'distance' => $distanceMatrix[strlen($s1)][strlen($s2)],
            'similarity' => $similarity,
            'distanceMatrix' => $distanceMatrix
        ];
    }
}
