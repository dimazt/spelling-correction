<?php

namespace Tests\Unit;

use App\Services\StringComparisonService;
use App\Services\TextProcessingService;
use Tests\TestCase;

class TextProcessingServiceTest extends TestCase
{
    protected $service;
    protected $similarityService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TextProcessingService();
        $this->similarityService = new StringComparisonService();
    }

    public function testSpellCheck()
    {
        $this->markTestSkipped();
        $input = "ADalah";
        $expected = "Adalah"; // assuming LEGENDARIS is in the database
        $this->assertEquals($expected, $this->service->spellCheck($input));
    }
    public function testSpellCheck2()
    {
        $this->markTestSkipped();
        $input = "mengkoreksi";
        $expected = "mengoreksi"; // assuming LEGENDARIS is in the database
        $this->assertEquals($expected, $this->service->spellCheck($input));
    }

    public function testSimiliarity(){
        $this->markTestSkipped();

        $input = "adlah";
        $expected = "alih";
        $result = $this->similarityService->getDistanceAndSimilarity($input,$expected);
        dump($result['similarity'], $result['distance']);
        $this->assertTrue(true);
    }

    public function testIndonesianWords(){
        $this->markTestSkipped();
        $kbbi = $this->service->loadIndonesianWords();
        dd($kbbi);
        dd(in_array("kit", $kbbi));
    }

   
}
