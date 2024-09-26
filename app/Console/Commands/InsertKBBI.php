<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InsertKBBI extends Command
{
    protected $signature = 'import:kbbi';
    protected $description = 'Import Indonesian words from text file to database';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $filePath = 'kbbi/indonesian-words.txt';
        // dd($filePath);
        if (!Storage::exists($filePath)) {
            $this->error("File not found.");
            return;
        }

        $fileContent = Storage::get($filePath);

        // Pisahkan setiap baris menjadi array kata
        $words = explode(PHP_EOL, $fileContent);

        // Loop setiap kata dan masukkan ke dalam database
        foreach ($words as $word) {
            $trimmedWord = trim($word);

            // Pastikan kata tidak kosong setelah di-trim
            if (!empty($trimmedWord)) {
                DB::table('kbbi')->updateOrInsert([
                    'word' => $trimmedWord,
                ], [
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        $this->info('KBBI words imported successfully!');
    }
}
