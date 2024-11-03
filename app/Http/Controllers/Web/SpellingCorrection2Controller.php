<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\CorrectWord;
use App\Jobs\RecorrectWord;
use App\Models\CorrectionResult;
use App\Models\SpellingCorrection;
use App\Services\SpellingCorrection\DataPreparationService;
use App\Services\SpellingCorrection\DataProcessingService;
use Illuminate\Http\Request;
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


    public function home(Request $request)
    {
        $type = "testing";
        $layout = "home";
        if ($request->path() == "training") {
            $type = "training";
            $layout = "training";
        }

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
        $spellingCorrection = SpellingCorrection::where('user_id', $user->id)->where('type', $type)->orderByDesc('updated_at')->get();

        $spellingCorrection->transform(function ($item) use ($status) {
            $item->is_enable = $item->status === "done" ? true : false;
            $transform_status = isset($status->{$item->status}) ? $status->{$item->status} : $status->failed;
            $item->status = $transform_status->status;
            $item->label = $transform_status->label;
            $item->result = $item->result ? basename($item->result) : '#';
            return $item;
        });



        return view("home_page", [
            'active_page' => $layout,
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
            'type' => $request->type == "training" ? "training" : "testing",
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

    public function editCorrection(Request $request)
    {
        // $corrections = [];
        // foreach ($request->corrections as $id => $correction) {
        //     // Pastikan koreksi tidak kosong
        //     if (!empty($correction)) {
        //         $corrections[] = (object)[
        //             'correction_id' => $id,
        //             'keyword' => $request->corrects[$id] ?? null,
        //             'correction' => $correction,
        //             'save' => isset($request->save_corrections[$id]) // Operator ternary disederhanakan
        //         ];
        //     }
        // }

        // // Periksa apakah tidak ada item yang dikoreksi
        // if (empty($corrections)) {
        //     return back()->with('failed', 'Tidak ada item yang dikoreksi');
        // }

        $user = auth()->user();
        $documentId = $request->document_id;
        // cek apakah sudah ada dokumen dengan kondisi berikut
        $documentCorrection = SpellingCorrection::where('id', $documentId)
            ->where('status', 'done')
            ->where('user_id', $user->id)
            ->first();

        if (!$documentCorrection) {
            return back()->with('failed', "Dokumen masih dalam proses, mohon menunggu beberapa saat!");
        }

        if (!Storage::exists($documentCorrection->result)) {
            return back()->with('failed', "Dokumen telah di hapus!");
        }

        // $document = $this->extractTextFromPdf($documentCorrection->result);

        // // jika ada, maka ubah status
        // // $documentCorrection->update(['status' => 'waiting']);

        // $data = (object) [
        //     'corrections' => $corrections,
        //     'model' => $documentCorrection,
        //     'document' => $document
        // ];
        // RecorrectWord::dispatch($data);

        CorrectionResult::where('document_id', $documentId)->update(['correction' => $request->content]);
        $newDocument = DataPreparationService::saveToPdf($request->content);
        $documentCorrection->update(['result' => $newDocument]);
        return back()->with('success', "Berhasil dikoreksi, silahkan download ulang dokumen untuk melihat hasil");
        // return redirect(route('training'))->with('success', "Dokumen akan di koreksi kembali, mohon menunggu beberapa saat!");

    }

    public function detail($id)
    {
        $user = auth()->user();
        $document = SpellingCorrection::where('id', $id)
            ->where('user_id', $user->id)
            ->first();
        if (!$document) {
            return back()->with('failed', "Dokumen tidak ditemukan!");
        }

        $result = CorrectionResult::where('document_id', $id)->first();
        $content = $result->correction ?? $result->correct_word;
        return view('correction_page', [
            "document" => $document,
            "result" => $content
        ]);
    }

    private function extractTextFromPdf($filePath)
    {
        $pdfFullPath = Storage::path($filePath);
        return Pdf::getText($pdfFullPath, env('PDF_TO_TEXT', null));
    }



}
