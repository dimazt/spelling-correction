<?php

namespace Tests\Unit;

use App\Services\Levenstein\DataProcessingService;
use App\Services\TextProcessingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
        $token = $this->service->tokenizeWithPunctuation($text);
    }

    public function testCorrection(){
        $kbbi = (new TextProcessingService())->loadIndonesianWords();
        $text = "ini adalah, sebuah text";
        $correction = $this->service->correctWord("ejan",$kbbi);
        dump($correction); // hasilnya kesalehan
    }
    public function testDistanceAndSimilarity(){
        $kbbi = (new TextProcessingService())->loadIndonesianWords();
        $text = "ini adalah, sebuah text";
        $correction = $this->service->getDistanceAndSimilarity("kesalhan","kesalehan");
        dd($correction); // 
    }

}
