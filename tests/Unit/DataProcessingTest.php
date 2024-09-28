<?php

namespace Tests\Unit;

use App\Services\SpellingCorrection\Algorithms\BothAlgorithm;
use App\Services\SpellingCorrection\Algorithms\Levenshtein;
use App\Services\SpellingCorrection\Algorithms\Similarity;
use App\Services\SpellingCorrection\DataPreparationService;
use App\Services\SpellingCorrection\DataProcessingService;
use App\Services\TextProcessingService;
use Tests\TestCase;

class DataProcessingTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DataProcessingService();
    }
    /**
     * A basic feature test example.
     */
    public function testToken()
    {
        $text = "ini adalah, sebuah text";
        $token = DataPreparationService::tokenizeWithPunctuation($text);
        $this->assertEquals([
            "ini",
            "adalah",
            ",",
            "sebuah",
            "text"
        ], $token);
    }

    public function testCorrection()
    {
        $kbbi = (new TextProcessingService())->loadIndonesianWords();
        $correction = BothAlgorithm::correctWord("kemudan", $kbbi);
        $this->assertEquals('kemudian', $correction);
    }
    public function testDistanceAndSimilarity()
    {
        $correction = BothAlgorithm::getDistanceAndSimilarity("kemudin", "kemudian");
        $this->assertObjectHasProperty('distance', $correction);
        $this->assertObjectHasProperty('similarity', $correction);
        $this->assertObjectHasProperty('logs', $correction);
    }
    public function testDistance()
    {
        $kbbi = (new TextProcessingService())->loadIndonesianWords();
        $correction = (new DataProcessingService())->correctWordWithCase(
            "kemudin",
            $kbbi,
            'levenshtein'
        );
        $this->assertEquals('kemudi', $correction);
    }
    public function testLevenshteinDistance()
    {
        $kbbi = (new TextProcessingService())->loadIndonesianWords();
        $result = Levenshtein::manualLevenshteinWithLog('legendaris','lenegdi');
        $result2 = Levenshtein::manualLevenshteinWithLog('lenegdi','legendaris');
        // $result2 = Similarity::processAlgorithmSimilarity('kemudin','kemudian');
        dump($result->distance,$result2->distance);
        dump($result->matrix,$result2->matrix);
        dump($result->similarity,$result2->similarity);
        dd($result->logs,$result2->logs);
        // $this->assertEquals('kemudi', $correction);
    }

}
