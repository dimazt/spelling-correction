<?php
namespace App\Services\SpellingCorrection;
use App\Services\SpellingCorrection\Algorithms\BothAlgorithm;
use App\Services\SpellingCorrection\Algorithms\Levenshtein;
use App\Services\SpellingCorrection\Algorithms\Similarity;

class DataProcessingService
{
    public function correctWordWithCase($word, $kbbiWords, $comparisonMode = null)
    {
        $isCapitalized = ctype_upper($word[0]);
        $lowercaseWord = strtolower($word);

        if (in_array($lowercaseWord, $kbbiWords)) {
            return $word;
        }

        $correctedWord = match ($comparisonMode) {
            'similarity' => Similarity::correctWord($lowercaseWord, $kbbiWords),
            'levenshtein' => Levenshtein::correctWord($lowercaseWord, $kbbiWords),
            default => BothAlgorithm::correctWord($lowercaseWord, $kbbiWords),
        };

        // Kembalikan huruf kapital jika awalnya kapital
        return $isCapitalized ? ucfirst($correctedWord) : $correctedWord;

    }

    public function correctSpellingWithStructureAndCase($text, $kbbiWords)
    {
        // Tokenisasi dengan mempertahankan tanda baca
        $tokens = DataPreparationService::tokenizeWithPunctuation($text);
        $correctedTokens = [];

        foreach ($tokens as $token) {
            // Periksa apakah token adalah kata, dan bukan simbol atau angka
            if (ctype_alpha($token)) {
                // Lakukan koreksi pada kata sambil mempertahankan huruf kapital
                $correctedToken = $this->correctWordWithCase($token, $kbbiWords);

                // Jika token yang diperbaiki berbeda dengan token asli, beri warna merah
                $correctedTokens[] = ($correctedToken !== $token)
                    ? "<span style='color: red;'>$correctedToken</span>"
                    : $correctedToken;

            } else {
                // Jika token adalah simbol atau tanda baca, biarkan
                $correctedTokens[] = $token;
            }
        }

        // Gabungkan kembali menjadi string
        return implode('', array_map(function ($token) {
            return ctype_punct($token) ? $token : ' ' . $token;
        }, $correctedTokens));
    }


}