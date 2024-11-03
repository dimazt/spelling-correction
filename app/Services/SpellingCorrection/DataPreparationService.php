<?php

namespace App\Services\SpellingCorrection;

use Dompdf\Dompdf;
use Storage;

class DataPreparationService
{
    public static function tokenizeWithPunctuation($text)
    {
        // Tokenisasi teks sambil mempertahankan tanda baca
        preg_match_all('/\w+|[^\w\s]/u', $text, $matches);
        return $matches[0];
    }

    public static function saveToPdf($correctedLines)
    {
        $dompdf = new Dompdf();

        $html = $correctedLines;
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        if (!Storage::exists('results')) {
            Storage::makeDirectory('results');
        }

        $fileName = 'corrected_' . time() . '.pdf';
        $content = $dompdf->output();
        $path = "results/{$fileName}";
        Storage::put($path, $content);
        return $path;
    }
}