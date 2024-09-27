<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\TextProcessingService;
use Cache;
use DB;
use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;
use Smalot\PdfParser\Parser;
use Spatie\PdfToText\Pdf;
use Dompdf\Dompdf;
use File;
use Storage;

class SpellingCorrectionController extends Controller
{
    // Daftar kata target (kata-kata dalam Bahasa Indonesia)
    protected $textProcessingService;

    public function __construct(TextProcessingService $textProcessingService)
    {
        $this->textProcessingService = $textProcessingService;
    }

    // Fungsi untuk meng-upload dan memproses file PDF
    public function upload(Request $request)
    {
        set_time_limit(300);
        $request->validate([
            'pdf' => 'required|mimes:pdf|max:2048'
        ]);

        // Simpan file PDF ke dalam storage (storage/app/pdf)
        $filePath = $request->file('pdf')->store('pdfs');

        // Ambil path lengkap file PDF
        $pdfFullPath = Storage::path($filePath);

        // Proses file PDF untuk mengekstrak teks
        $text = Pdf::getText($pdfFullPath, '/opt/homebrew/bin/pdftotext');


        // Menambahkan spasi di antara kata-kata yang berdekatan
        // $cleanedText = preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $cleanedText);
        // dd($cleanedText);

        // Koreksi ejaan
        // $processedText = $this->textProcessingService->preprocessText($text);

        // Koreksi ejaan
        // $correctedText = $this->textProcessingService->spellCheck($text);


        // // Generate PDF baru dengan teks yang sudah dikoreksi
        // $pdf = new Dompdf();
        // $pdf->loadHtml($correctedText);
        // $pdf->setPaper('A4', 'portrait');
        // $pdf->render();

        // // Simpan PDF hasil koreksi
        // $outputPath = storage_path('app/corrected_' . time() . '.pdf');
        // File::put($outputPath, $pdf->output());

        // Perbaikan mulai dari sini
        $pages = preg_split('/\n\s*\n/', $text); // Misalnya, dua newline sebagai pemisah halaman

        // dd($pages);
        
        $correctedPages = [];
        foreach ($pages as $page) {
            // Lakukan koreksi pada setiap halaman
            $correctedPages[] = $this->textProcessingService->spellCheck($page);
        }

        dd($correctedPages);
        // Menggabungkan halaman yang sudah dikoreksi
        // $correctedText = implode("\n\n", $correctedPages);
        $correctedText = implode("<div style='page-break-after: always;'></div>", $correctedPages);
        // dd($correctedText);

        // Generate PDF baru dengan teks yang sudah dikoreksi
        $pdf = new Dompdf();
        $pdf->loadHtml($correctedText);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        // Simpan PDF hasil koreksi
        $outputPath = storage_path('app/corrected_' . time() . '.pdf');
        File::put($outputPath, $pdf->output());

        // return response()->json([
        //     'data' => $correctedText
        // ]);
        return response()->download($outputPath);
    }

    // Fungsi untuk melakukan koreksi ejaan
    protected function spellCheck($text)
    {
        $words = explode(' ', $text); // Pisahkan teks menjadi kata-kata
        $correctedWords = [];

        foreach (array_chunk($words, 100) as $chunk) { // Memproses dalam chunk
            foreach ($chunk as $word) {
                $correctedWords[] = $this->correctWord($word);
            }
        }

        return implode(' ', $correctedWords); // Gabungkan kata-kata yang sudah dikoreksi
    }


    // Fungsi untuk mengoreksi sebuah kata menggunakan algoritma Levenshtein
    protected function correctWord($word)
    {
        // Abaikan kata yang terlalu pendek
        if (strlen($word) < 3) {
            return $word;
        }

        $lowestDistance = PHP_INT_MAX; // Jarak terendah
        $closestWord = $word; // Kata terdekat

        foreach ($this->indonesianWords as $targetWord) {
            $distance = $this->levenshteinDistance(strtolower($word), strtolower($targetWord));

            // Jika jarak lebih kecil dari yang terendah dan sesuai dengan batas
            if ($distance < $lowestDistance) {
                $lowestDistance = $distance;
                $closestWord = $targetWord;
            }
        }

        // Menetapkan ambang batas jarak untuk akurasi
        return $lowestDistance <= 2 ? $closestWord : $word; // Kembalikan kata terdekat atau kata asli
    }



    // Implementasi algoritma Levenshtein secara manual
    protected function levenshteinDistance($str1, $str2)
    {
        $len1 = strlen($str1);
        $len2 = strlen($str2);

        $matrix = [];

        // Inisialisasi matriks
        for ($i = 0; $i <= $len1; $i++) {
            $matrix[$i][0] = $i;
        }
        for ($j = 0; $j <= $len2; $j++) {
            $matrix[0][$j] = $j;
        }

        // Mengisi matriks dengan nilai Levenshtein
        for ($i = 1; $i <= $len1; $i++) {
            for ($j = 1; $j <= $len2; $j++) {
                if ($str1[$i - 1] == $str2[$j - 1]) {
                    $cost = 0;
                } else {
                    $cost = 1;
                }

                $matrix[$i][$j] = min(
                    $matrix[$i - 1][$j] + 1, // Penghapusan
                    $matrix[$i][$j - 1] + 1, // Penyisipan
                    $matrix[$i - 1][$j - 1] + $cost // Substitusi
                );
            }
        }

        return $matrix[$len1][$len2];
    }

    protected function preprocessText($text)
    {
        // Menghapus karakter khusus dan simbol yang tidak diperlukan
        $text = preg_replace('/[^a-zA-Z\s]/', '', $text);

        // Mengganti banyak spasi dengan satu spasi
        $text = preg_replace('/\s+/', ' ', $text);

        // Menghapus spasi di awal dan akhir
        return trim($text);
    }

}
