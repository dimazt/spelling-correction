<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TextProcessingService
{
    protected $indonesianWords;
    protected $comparisonService;

    public function __construct()
    {
        $this->indonesianWords = $this->loadIndonesianWords();
        $this->comparisonService = new StringComparisonService();
    }

    public function preprocessText($text)
    {
        // Menghapus karakter khusus dan simbol yang tidak diperlukan
        $text = preg_replace('/[^a-zA-Z\s]/', '', $text);

        // Mengganti banyak spasi dengan satu spasi
        $text = preg_replace('/\s+/', ' ', $text);

        // Menghapus spasi di awal dan akhir
        return trim($text);
    }

    public function spellCheck($text)
    {

        $words = explode(' ', strtolower($text));
        $correctedWords = [];

        foreach ($words as $word) {
            $correctedWords[] = $this->correctWord($word);
        }


        return implode(' ', $correctedWords);
    }
    protected function correctWord($word)
    {
        $kbbi = array_map('strtolower', array: $this->indonesianWords); // Ambil kata-kata KBBI dan ubah ke lowercase
        // Abaikan kata yang terlalu pendek
        if (strlen($word) < 3) {
            return $word;
        }

        if (in_array($word, $kbbi)) {
            return $word;
        }

        $lowestDistance = PHP_INT_MAX; // Jarak terendah
        $closestWord = $word; // Kata terdekat

        foreach ($kbbi as $targetWord) {
            // $distance = levenshtein(strtolower($word), strtolower($targetWord));
            $result = $this->comparisonService->getDistanceAndSimilarity($word, $targetWord);
            // Jika jarak lebih kecil dari yang terendah dan sesuai dengan batas
            if ($result['similarity'] >= 80) {
                $lowestDistance = $result['distance'];
                $closestWord = $targetWord;
            }
        }

        // if ($lowestDistance > 0 && $lowestDistance <= 3)
        //     echo "Kata: $word, koreksi: $closestWord \n";
        // Menetapkan ambang batas jarak untuk akurasi
        return $lowestDistance <= 3 ? $closestWord : $word; // Kembalikan kata terdekat atau kata asli
    }
    // protected function correctWord($content)
    // {
    //     $kbbi = array_map('strtolower', $this->indonesianWords); // Ambil kata-kata KBBI dan ubah ke lowercase
    //     $words = explode(' ', strtolower($content));
    //     foreach ($words as $word) {
    //         $suggestion = '';
    //         $minDistance = PHP_INT_MAX;
    //         // dump($word);
    //         foreach ($kbbi as $kbbiWord) {
    //             $distance = $this->optimizedLevenshtein($word, $kbbiWord);
    //             if ($distance < $minDistance) {
    //                 $minDistance = $distance;
    //                 $suggestion = $kbbiWord;
    //             }
    //         }

    //         // Tampilkan kata yang salah dan koreksi yang disarankan
    //         if ($minDistance > 0 && $minDistance <= 1) {
    //             // echo "Kata: $word, Koreksi: $suggestion \n";
    //             return $suggestion;
    //         }

    //     }
    // }
    function optimizedLevenshtein($str1, $str2)
    {
        $len1 = strlen($str1);
        $len2 = strlen($str2);

        // Jika salah satu string kosong, jaraknya adalah panjang string lainnya
        if ($len1 == 0)
            return $len2;
        if ($len2 == 0)
            return $len1;

        // Baris sebelumnya dan saat ini
        $prevRow = range(0, $len2);
        $currRow = array_fill(0, $len2 + 1, 0);

        // Iterasi di sepanjang string pertama
        for ($i = 1; $i <= $len1; $i++) {
            $currRow[0] = $i;

            // Iterasi di sepanjang string kedua
            for ($j = 1; $j <= $len2; $j++) {
                $cost = ($str1[$i - 1] == $str2[$j - 1]) ? 0 : 1;

                // Hitung nilai minimum dari tiga kemungkinan operasi
                $currRow[$j] = min(
                    $prevRow[$j] + 1,      // Penghapusan
                    $currRow[$j - 1] + 1,  // Penyisipan
                    $prevRow[$j - 1] + $cost // Penggantian
                );
            }

            // Tukar baris, sehingga baris saat ini menjadi baris sebelumnya di iterasi berikutnya
            $prevRow = $currRow;
        }

        // Hasil akhir adalah elemen terakhir di baris terakhir
        return $currRow[$len2];
    }
    protected function levenshteinDistance($str1, $str2)
    {
        $len1 = strlen($str1);
        $len2 = strlen($str2);
        $matrix = [];

        for ($i = 0; $i <= $len1; $i++) {
            $matrix[$i][0] = $i;
        }
        for ($j = 0; $j <= $len2; $j++) {
            $matrix[0][$j] = $j;
        }

        for ($i = 1; $i <= $len1; $i++) {
            for ($j = 1; $j <= $len2; $j++) {
                $cost = ($str1[$i - 1] == $str2[$j - 1]) ? 0 : 1;
                $matrix[$i][$j] = min(
                    $matrix[$i - 1][$j] + 1,
                    $matrix[$i][$j - 1] + 1,
                    $matrix[$i - 1][$j - 1] + $cost
                );
            }
        }

        return $matrix[$len1][$len2];
    }

    protected function loadIndonesianWords()
    {
        $filePath = 'kbbi/common_words.txt';
        // $filePath = 'kbbi/indonesian-words.txt';

        if (!Storage::exists($filePath)) {
            throw new \Exception("File not found: $filePath");
        }

        $fileContent = Storage::get($filePath);
        return array_filter(array_map('trim', explode(' ', $fileContent)));
    }
}
