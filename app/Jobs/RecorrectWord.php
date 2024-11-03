<?php

namespace App\Jobs;

use App\Models\CorrectionResult;
use App\Models\KamusKBBI;
use App\Services\SpellingCorrection\DataPreparationService;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;


class RecorrectWord implements ShouldQueue
{
    use Queueable;

    private $model;
    private $corrections;
    private $document;

    public function __construct($data)
    {
        $this->model = $data->model;
        $this->corrections = $data->corrections;
        $this->document = $data->document;
    }

    public function handle()
    {
        try {
            $this->model->update([
                "status" => "processing"
            ]);

            dump($this->corrections);
            foreach ($this->corrections as $value) {
                $correction = $value->correction;
                $replaced = ctype_upper($correction[0]) ? ucfirst($correction) : $correction;
                $newWord = "<span style='color: red;'>$replaced</span>";
                $this->document = $this->replaceWord($this->document, $value->keyword, $newWord);
                CorrectionResult::where('id', $value->correction_id)->update([
                    'correction' => $correction
                ]);
                if (isset($value->save)) {
                    $correction = strtolower($correction);
                    $existWord = KamusKBBI::where('word', $correction)->first();
                    if ($existWord) {
                        $existWord->update(['word' => $correction]);
                    } else {
                        KamusKBBI::create(['word' => $correction]);
                    }
                }
            }

            $correctionIds = collect($this->corrections)->pluck('correction_id')->toArray();
            $oldResult = CorrectionResult::where('document_id', $this->model->id)->whereNotIn('id', $correctionIds)->whereNull('correction')->get();
            foreach ($oldResult as $value) {
                $correction = $value->correct_word;
                $replaced = ctype_upper($correction[0]) ? ucfirst($correction) : $correction;
                $newWord = "<span style='color: red;'>$replaced</span>";
                $this->document = $this->replaceWord($this->document, $correction, $newWord);
            }

            $outputPdfPath = DataPreparationService::saveToPdf($this->document);

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

    private function replaceWord($text, $searchWord, $replacementWord)
    {
        $pattern = '/\b' . preg_quote($searchWord, '/') . '\b(?![a-zA-Z])/';

        // Mengganti kata yang cocok
        return preg_replace($pattern, $replacementWord, $text);
    }


}