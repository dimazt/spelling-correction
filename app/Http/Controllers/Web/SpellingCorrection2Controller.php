<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\CorrectWord;
use App\Models\SpellingCorrection;
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
    public function __construct(
        DataPreparationService $dataPreparationService,
        DataProcessingService $dataProcessingService
    ) {
        $this->preparation = $dataPreparationService;
        $this->processing = $dataProcessingService;
    }


    public function home()
    {
        $status = (object) [
            "done" => (object) [
                "status" => "Selesai",
                "label" => "success"
            ],
            "failed" => (object) [
                "status" => "Gagal",
                "label" => "danger"
            ],
            "processing" => (object) [
                "status" => "Sedang Diproses",
                "label" => "primary"
            ],
            "waiting" => (object) [
                "status" => "Menunggu Diproses",
                "label" => "primary"
            ]
        ];

        $user = auth()->user();
        $spellingCorrection = SpellingCorrection::where('user_id', $user->id)->get();

        $spellingCorrection->transform(function ($item) use ($status) {
            // Tentukan apakah 'is_enable' true atau false
            $item->is_enable = $item->status === "done" ? true : false;
            // Gunakan notasi objek (->) untuk mengakses properti
            $transform_status = isset($status->{$item->status}) ? $status->{$item->status} : $status->failed;
            $item->status = $transform_status->status;
            $item->label = $transform_status->label;
            return $item;
        });


        return view('home_page', [
            'active_page' => 'home',
            'data' => $spellingCorrection
        ]);
    }
    public function upload(Request $request)
    {
        $user = auth()->user();
        // 1. Upload dan ekstrak teks dari PDF
        $request->validate([
            'pdf' => 'required|mimes:pdf|max:2048'
        ]);

        // Simpan file PDF ke dalam storage (storage/app/pdf)
        $file = $request->file('pdf');

        // Mengambil nama asli file
        $originalFileName = $file->getClientOriginalName();

        // Menyimpan file ke direktori 'pdfs'
        $filePath = $file->store('pdfs');
        $spellingCorrection = SpellingCorrection::create([
            'name' => $originalFileName,
            'status' => 'waiting',
            'type' => 'testing',
            'user_id' => $user->id
        ]);
        $pdfText = $this->extractTextFromPdf($filePath);

        // pisahkan kalimat pada setiap spasi enter
        $pages = preg_split('/\n\s*\n/', $pdfText);
        CorrectWord::dispatch((object) [
            "pages" => $pages,
            "model" => $spellingCorrection
        ]);
        return back()->with('success', "Dokumen sedang diproses, mohon menunggu beberapa saat!");
    }

    private function extractTextFromPdf($filePath)
    {
        $pdfFullPath = Storage::path($filePath);
        return Pdf::getText($pdfFullPath, env('PDF_TO_TEXT', null));
    }



}
