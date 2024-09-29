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
        // $this->markTestSkipped();
        $kbbi = (new TextProcessingService())->loadIndonesianWords();
        $correction = (new DataProcessingService())->correctWordWithCase(
            "terdriri",
            $kbbi,
            'levenshtein'
        );
        $this->assertEquals('terdiri', $correction);
    }
    public function testLevenshteinDistance()
    {
        $this->markTestSkipped();
        $kbbi = (new TextProcessingService())->loadIndonesianWords();
        $result = Levenshtein::manualLevenshteinWithLog('terdriri','berdiri');
        // $result2 = Similarity::processAlgorithmSimilarity('kemudin','kemudian');
        dd($result->distance, $result->similarity);
        // $this->assertEquals('kemudi', $correction);
    }

}
