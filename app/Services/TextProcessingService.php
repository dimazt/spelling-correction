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
    public function preprocessText($text, &$symbols, &$capitalization)
    {
        // Simpan simbol dan posisinya relatif terhadap kata
        preg_match_all('/[^a-zA-Z\s]/', $text, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as $match) {
            $symbols[$match[1]] = $match[0]; // Simpan simbol dengan posisi awalnya
        }

        // Pisahkan kata-kata dan simpan informasi kapitalisasi
        $words = preg_split('/\s+/', $text);
        foreach ($words as $index => $word) {
            // Pastikan $word tidak kosong sebelum mengakses huruf pertamanya
            if (strlen($word) > 0) {
                // Simpan informasi kapitalisasi (true untuk huruf besar, false untuk huruf kecil)
                $capitalization[$index] = ctype_upper($word[0]);
            } else {
                // Jika kata kosong, simpan false sebagai default
                $capitalization[$index] = false;
            }
        }

        // Mengganti banyak spasi dengan satu spasi
        $text = preg_replace('/\s+/', ' ', $text);

        // Menghapus spasi di awal dan akhir
        return trim($text);
    }



    public function spellCheck($text)
    {
        // Simpan simbol dan posisinya
        $symbols = [];
        $capitalization = [];
        $textWithoutSymbols = $this->preprocessText($text, $symbols, $capitalization);

        // Pisahkan kata-kata untuk dicek ejaannya
        $words = explode(' ', strtolower($textWithoutSymbols));
        $correctedWords = [];
        $currentIndex = 0;

        foreach ($words as $index => $word) {
            // Koreksi kata
            $correctedWord = $this->correctWord($word);

            // Terapkan kapitalisasi kembali
            if ($capitalization[$index]) {
                $correctedWord = ucfirst($correctedWord); // Huruf pertama kapital
            }

            $correctedWords[] = $correctedWord;

            // Periksa posisi dari kata asli untuk menambahkan simbol
            $originalPosition = strpos($text, $word, $currentIndex); // Cari posisi asli kata
            $currentIndex = $originalPosition + strlen($word); // Update posisi setelah kata

            // Tambahkan simbol setelah kata yang sudah dikoreksi
            while (isset($symbols[$currentIndex])) {
                $correctedWords[] = $symbols[$currentIndex];
                unset($symbols[$currentIndex]); // Hapus simbol setelah digunakan
                $currentIndex++; // Perbarui posisi untuk simbol berikutnya
            }
        }

        // Gabungkan kata-kata yang telah dikoreksi dan simbol
        return implode(' ', $correctedWords);
    }




    protected function correctWord($word)
    {
        $kbbi = array_map('strtolower', $this->indonesianWords); // Ambil kata-kata KBBI dan ubah ke lowercase

        // Abaikan kata yang terlalu pendek
        if (strlen($word) < 3) {
            return $word;
        }
        if (in_array($word, $kbbi)) {
            // dump($word);
            return $word;
        }

        $lowestDistance = PHP_INT_MAX; // Jarak terendah
        $closestWord = $word; // Kata terdekat

        $results = [];
        foreach ($kbbi as $targetWord) {
            $result = $this->comparisonService->getDistanceAndSimilarity($word, $targetWord);
            if($word=="levenstein"){
                dump($result);
            }
            // Jika jarak lebih kecil dari yang terendah dan sesuai dengan batas
            if ($result['distance'] <= 2 && $result['similarity'] >= 60) {
                $results[] = [
                    "distance" => $result['distance'],
                    "replacement_word" => $targetWord,
                    "similarity" => $result['similarity']
                ];
            }
        }
        if (!empty($results)) {
            foreach ($results as $result) {
                if ($result['distance'] <= 1 && $result['similarity'] >= 80) {
                    return $result['replacement_word'];
                }
            }
        }

        // Menetapkan ambang batas jarak untuk akurasi
        return $word;
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

    public function loadIndonesianWords()
    {
        $filePath = 'kbbi/common_words.txt';
        // $filePath = 'kbbi/indonesian-words.txt';

        if (!Storage::exists($filePath)) {
            throw new \Exception("File not found: $filePath");
        }

        // Membaca konten file
        $fileContent = Storage::get($filePath);

        // Menggunakan preg_split untuk memisahkan kata berdasarkan spasi dan newline
        $words = preg_split('/[\s\n]+/', $fileContent);

        // Menghapus kata yang kosong setelah pemisahan
        // $words = array_filter(array_map('trim', $words));
        // // sort($words);

        // // Jika diperlukan, konversi ke lowercase
        // $words = array_map('strtolower', $words);

        // $words = preg_split('/[\s\n]+/', $combinedString);

        // Menghapus kata yang kosong setelah pemisahan
        $words = array_filter(array_map('trim', $words));

        // Jika diperlukan, konversi ke lowercase
        $words = array_map('strtolower', $words);

        // Hapus duplikat lagi setelah konversi ke lowercase
        $words = array_unique($words);
        $words = array_map(function($word) {
            return trim(preg_replace('/[()]/', '', $word)); // Hapus tanda kurung dan trim whitespace
        }, $words);
        $words = array_unique($words);
        // Urutkan kata
        sort($words);
        return $words;
    }
}
