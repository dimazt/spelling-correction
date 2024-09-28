<?php

namespace App\Services\SpellingCorrection;

class DataPreparationService
{
    public static function tokenizeWithPunctuation($text)
    {
        // Tokenisasi teks sambil mempertahankan tanda baca
        preg_match_all('/\w+|[^\w\s]/u', $text, $matches);
        return $matches[0];
    }
}