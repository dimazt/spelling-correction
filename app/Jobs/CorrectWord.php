<?php

namespace App\Jobs;

use App\Models\CorrectionResult;
use App\Models\SpellingCorrection;
use App\Services\SpellingCorrection\DataProcessingService;
use App\Services\TextProcessingService;
use Dompdf\Dompdf;
use File;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Storage;

class CorrectWord implements ShouldQueue
{
    use Queueable;
    private $pages;
    private $model;
    private $kbbiWords;
    /**
     * Create a new job instance.
     */
    public $timeout = 3600;
    public function __construct($data)
    {
        $this->pages = $data->pages;
        $this->model = $data->model;
        $this->kbbiWords = (new TextProcessingService())->loadIndonesianWords();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $correctedPages = [];
        $correctionResults = [];
        $incorrectedPages = [];
        try {
            $this->model->update([
                "status" => "processing"
            ]);
            foreach ($this->pages as $page) {
                // Lakukan koreksi pada setiap halaman
                $correction = DataProcessingService::correctSpellingWithStructureAndCase(
                    $page,
                    $this->kbbiWords
                );
                $incorrectedPages[] = $page;
                $correctionResults[] = $correction->correction_results;
                $correctedPages[] = $correction->results;
            }
            $correctedPages = implode("\n\n", $correctedPages);
            $correctionResults = array_merge(...$correctionResults);
            // if (!empty($correctionResults)) {
            //     foreach ($correctionResults as $result) {
            //         CorrectionResult::create(array_merge([
            //             'document_id' => $this->model->id
            //         ], $result));
            //     }
            // }

            CorrectionResult::create(
                [
                    'document_id' => $this->model->id,
                    'incorrect_word' => implode("\n\n", $incorrectedPages),
                    'correct_word' => $correctedPages
                ]
            );
            // 5. Simpan hasil koreksi ke PDF bar
            $outputPdfPath = $this->saveToPdf($correctedPages);
            $this->model->update([
                "result" => $outputPdfPath,
                "status" => "done"
            ]);
        } catch (\Throwable $th) {
            $this->model->update([
                "status" => "failed"
            ]);
            throw $th;
        }
    }
    private function saveToPdf($correctedLines)
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
