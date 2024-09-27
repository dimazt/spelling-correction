<?php

namespace App\Services\Levenstein;

class DataPreparationService
{
    public function preprocessSingleSentence($sentence)
    {
        // Hapus tanda baca dan whitespace
        $sentence = trim($sentence);
        return $sentence;
    }

    public function removePunctuation($text)
    {
        // Hapus tanda baca yang tidak diperlukan
        return preg_replace('/[^\w\s]/', '', $text);
    }

    public function toLowercase($text)
    {
        return strtolower($text);
    }

    public function removeInvalidWords($text, $kbbiWords)
    {
        // Pisahkan kata per baris
        $words = explode(' ', $text);
        $validWords = [];

        // Hanya kata yang ada di KBBI yang dimasukkan
        foreach ($words as $word) {
            if (in_array($word, $kbbiWords)) {
                $validWords[] = $word;
            }
        }

        // Gabungkan kembali menjadi string
        return implode(' ', $validWords);
    }

    public function preprocessText($pdfText, $kbbiWords)
    {
        $lines = explode("\n", $pdfText);
        $processedLines = [];

        foreach ($lines as $line) {
            $cleanedLine = $this->preprocessSingleSentence($line);
            $cleanedLine = $this->removePunctuation($cleanedLine);
            $cleanedLine = $this->toLowercase($cleanedLine);
            $cleanedLine = $this->removeInvalidWords($cleanedLine, $kbbiWords);

            if (!empty($cleanedLine)) {
                $processedLines[] = $cleanedLine;
            }
        }

        return $processedLines;
    }

  
    


}