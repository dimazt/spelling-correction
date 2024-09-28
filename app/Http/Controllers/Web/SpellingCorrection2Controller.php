<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\SpellingCorrection\DataPreparationService;
use App\Services\SpellingCorrection\DataProcessingService;
use App\Services\TextProcessingService;
use File;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Spatie\PdfToText\Pdf;
use Storage;

class SpellingCorrection2Controller extends Controller
{
    private $processing;
    private $preparation;
    public function __construct(DataPreparationService $dataPreparationService, DataProcessingService $dataProcessingService)
    {
        $this->preparation = $dataPreparationService;
        $this->processing = $dataProcessingService;
    }
    //
    public function upload(Request $request)
    {
        // 1. Upload dan ekstrak teks dari PDF
        $request->validate([
            'pdf' => 'required|mimes:pdf|max:2048'
        ]);

        // Simpan file PDF ke dalam storage (storage/app/pdf)
        $filePath = $request->file('pdf')->store('pdfs');
        $pdfText = $this->extractTextFromPdf($filePath);

        // 2. Siapkan kamus KBBI (array kata-kata KBBI)
        $kbbiWords = (new TextProcessingService())->loadIndonesianWords();

        // 3. Preprocess data
        // $processedText = $this->preparation->preprocessText($pdfText, $kbbiWords);

        // 4. Lakukan koreksi ejaan
        // $correctedText = $this->processing->correctSpelling($processedText, $kbbiWords);
        $pages = preg_split('/\n\s*\n/', $pdfText);
        // dd($pages);
        $correctedPages = [];
        foreach ($pages as $page) {
            // Lakukan koreksi pada setiap halaman
            $correctedPages[] = $this->processing->correctSpellingWithStructureAndCase($page, $kbbiWords);
            // $correctedPages[] = $this->textProcessingService->spellCheck($page);
        }
        $correctedText = implode("\n\n", $correctedPages);
        // dd($correctedPages);
        // dd($correctedPages);
        // Menggabungkan halaman yang sudah dikoreksi
        // $correctedText = implode("<div style='page-break-after: always;'></div>", $correctedPages);
        // dd($correctedText);
        // 5. Simpan hasil koreksi ke PDF baru

        $outputPdfPath = $this->saveToPdf($correctedText);

        // 6. Kembalikan file PDF yang sudah dikoreksi untuk didownload
        return response()->download($outputPdfPath);
    }

    private function extractTextFromPdf($filePath)
    {
        $pdfFullPath = Storage::path($filePath);
        return Pdf::getText($pdfFullPath, env('PDF_TO_TEXT', null));
    }

    private function saveToPdf($correctedLines)
    {
        $dompdf = new Dompdf();

        $html = $correctedLines;
        // foreach ($correctedLines as $line) {
        //     $html .= '<p>' . $line . '</p>';
        // }

        // Load HTML ke Dompdf
        $dompdf->loadHtml($html);

        // Set ukuran kertas
        $dompdf->setPaper('A4', 'portrait');

        // Render PDF
        $dompdf->render();

        // Simpan file PDF
        $outputPath = storage_path('app/corrected_' . time() . '.pdf');
        File::put($outputPath, $dompdf->output());
        return $outputPath;
    }

}
