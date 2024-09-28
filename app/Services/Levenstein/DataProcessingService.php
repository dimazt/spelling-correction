<?php
namespace App\Services\Levenstein;

class DataProcessingService
{

    public function getDistanceAndSimilarity($word, $kbbiWord)
    {
        $levDistance = levenshtein($word, $kbbiWord);

        // Hitung kemiripan menggunakan similar_text (mengembalikan persentase)
        similar_text($word, $kbbiWord, $similarity);
        return (object) [
            'distance' => $levDistance,
            'similarity' => $similarity
        ];
    }
    public function correctWord($word, $kbbiWords)
    {
        $closestWord = '';
        $shortestDistance = 0;
        $highestSimilarity = 75; // Persentase kemiripan tertinggi

        // Cek apakah kata sudah ada di KBBI, jika ada kembalikan langsung
        if (in_array($word, $kbbiWords)) {
            return $word;
        }

        foreach ($kbbiWords as $kbbiWord) {
            // Hitung jarak Levenshtein
            $result = $this->getDistanceAndSimilarity($word, $kbbiWord);
            $levDistance = $result->distance;
            $similarity = $result->similarity;
            // Jika Levenshtein 0 (kata cocok sempurna), kembalikan kata
            if ($levDistance == 0) {
                return $kbbiWord;
            }

            // Prioritaskan kata yang memiliki persentase kemiripan tinggi dan jarak Levenshtein pendek
            // $expectedDistance = $levDistance < $shortestDistance;
            // $expectedSimilarity = $similarity > $highestSimilarity;

            // if ($expectedSimilarity || $expectedDistance) {
            //     $closestWord = $kbbiWord;
            //     $shortestDistance = $levDistance;
            //     $highestSimilarity = $similarity;
            // }

            $lengthDifference = abs(strlen($word) - strlen($kbbiWord));

            // Jika similarity lebih besar, update kata terdekat
            if ($similarity > $highestSimilarity) {
                $closestWord = $kbbiWord;
                $highestSimilarity = $similarity;
                $shortestDistance = $levDistance;
                $closestLengthDifference = $lengthDifference;
            }
            // Jika similarity sama, cek Levenshtein distance
            elseif ($similarity == $highestSimilarity) {
                // Jika Levenshtein lebih kecil, update kata terdekat
                if ($levDistance < $shortestDistance) {
                    $closestWord = $kbbiWord;
                    $shortestDistance = $levDistance;
                    $closestLengthDifference = $lengthDifference;
                }
                // Jika Levenshtein juga sama, gunakan panjang kata sebagai penentu
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


    public function correctSpelling($processedLines, $kbbiWords)
    {
        $correctedLines = [];

        foreach ($processedLines as $line) {
            $words = explode(' ', $line);
            $correctedWords = [];

            foreach ($words as $word) {
                $correctedWord = $this->correctWord($word, $kbbiWords);
                $correctedWords[] = $correctedWord;
            }

            $correctedLines[] = implode(' ', $correctedWords);
        }

        return $correctedLines;
    }

    public function tokenizeWithPunctuation($text)
    {
        // Tokenisasi teks sambil mempertahankan tanda baca
        preg_match_all('/\w+|[^\w\s]/u', $text, $matches);
        return $matches[0];
    }

    public function correctSpellingWithStructure($text, $kbbiWords)
    {
        // Tokenisasi dengan mempertahankan tanda baca
        $tokens = $this->tokenizeWithPunctuation($text);
        $correctedTokens = [];

        foreach ($tokens as $token) {
            // Periksa apakah token adalah kata, dan bukan simbol atau angka
            if (ctype_alpha($token)) {
                // Lakukan koreksi pada kata
                $correctedToken = $this->correctWord($token, $kbbiWords);
                $correctedTokens[] = $correctedToken;
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

    public function correctWordWithCase($word, $kbbiWords)
    {
        $isCapitalized = ctype_upper($word[0]);
        $lowercaseWord = strtolower($word);

        $correctedWord = $this->correctWord($lowercaseWord, $kbbiWords);

        // Kembalikan huruf kapital jika awalnya kapital
        return $isCapitalized ? ucfirst($correctedWord) : $correctedWord;
    }

    public function correctSpellingWithStructureAndCase($text, $kbbiWords)
    {
        // Tokenisasi dengan mempertahankan tanda baca
        $tokens = $this->tokenizeWithPunctuation($text);
        dd($tokens);
        $correctedTokens = [];

        foreach ($tokens as $token) {
            // Periksa apakah token adalah kata, dan bukan simbol atau angka
            if (ctype_alpha($token)) {
                // Lakukan koreksi pada kata sambil mempertahankan huruf kapital
                $correctedToken = $this->correctWordWithCase($token, $kbbiWords);
                $correctedTokens[] = $correctedToken;
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