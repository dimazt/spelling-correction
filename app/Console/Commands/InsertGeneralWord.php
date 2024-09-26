<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InsertGeneralWord extends Command
{
    protected $signature = 'generate:kbbi';
    protected $description = 'Import Indonesian words from text file, filter, and save common words to database or file';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $filePath = 'kbbi/dict.json';

        if (!Storage::exists($filePath)) {
            $this->error("File not found.");
            return;
        }

        $fileContent = Storage::get($filePath);

        // Pisahkan setiap baris menjadi array kata
        // $words = explode(PHP_EOL, $fileContent);
        $words = json_decode($fileContent);
        // dd($words);

        $combinedData = [];

        foreach ($words as $word => $details) {
            $synonyms = implode(' ', $details->sinonim); // Ambil sinonim dan gabungkan menjadi string
            $combinedData[] = $word . ' ' . $synonyms; // Gabungkan kata dan sinonim
        }

        // Menggabungkan semua kata dan sinonim menjadi satu string
        $finalString = implode('; ', $combinedData); 

        // Simpan ke file atau database
        $this->saveToFile($combinedData);
        // $this->saveToDatabase($filteredWords); // Jika ingin menyimpan ke database

        $this->info('Common words imported successfully!');
    }

    // Fungsi untuk memeriksa apakah kata adalah kata dasar (tidak ada imbuhan)
    private function isKataDasar($word)
    {
        // Periksa apakah kata tidak mengandung awalan atau akhiran
        $prefixes = ['me', 'di', 'ber', 'ke', 'se', 'pe'];
        $suffixes = ['kan', 'i', 'an', 'nya'];

        foreach ($prefixes as $prefix) {
            if (strpos($word, $prefix) === 0) {
                return false;
            }
        }

        foreach ($suffixes as $suffix) {
            if (substr($word, -strlen($suffix)) === $suffix) {
                return false;
            }
        }

        return true;
    }

    // Fungsi untuk menyimpan ke file
    private function saveToFile(array $words)
    {
        $filePath = 'kbbi/common_words.txt';
        $content = implode(PHP_EOL, $words);
        Storage::disk('local')->put($filePath, $content);
    }

    // Fungsi untuk menyimpan ke database
    private function saveToDatabase(array $words)
    {
        foreach ($words as $word) {
            DB::table('kbbi')->updateOrInsert([
                'word' => $word,
            ], [
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
