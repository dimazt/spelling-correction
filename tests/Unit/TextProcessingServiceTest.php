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

    public function testPreprocessText()
    {
        $input = " Hello, World!   ";
        $expected = "Hello World";
        $this->assertEquals($expected, $this->service->preprocessText($input));
    }

    public function testSpellCheck()
    {
        $input = "ADalah";
        $expected = "adalah"; // assuming LEGENDARIS is in the database
        $this->assertEquals($expected, $this->service->spellCheck($input));
    }
    public function testSpellCheck2()
    {
        $input = "mengkoreksi";
        $expected = "mengoreksi"; // assuming LEGENDARIS is in the database
        $this->assertEquals($expected, $this->service->spellCheck($input));
    }

    public function testSimiliarity(){
        $input = "iin";
        $expected = "ini";
        $result = $this->similarityService->getDistanceAndSimilarity($input,$expected);
        dump($result['similarity'], $result['distance']);
        $this->assertTrue(true);

    }

   
}
