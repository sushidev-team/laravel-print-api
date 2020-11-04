<?php

namespace AMBERSIVE\Tests\Unit\Classes;

use AMBERSIVE\Tests\TestCase;

use AMBERSIVE\PdfPrinter\Classes\PdfPrinterOption;

use Mockery;
use Storage;
use Str;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;

class PdfPrinterOptionTest extends TestCase
{

    public PdfPrinterOption $options;

    protected function setUp(): void
    {
        parent::setUp();
        $this->options = new PdfPrinterOption("test", null, [], null, false);
    }

    public function testIfToArrayReturnsOptionsAsArray():void {

       $data = $this->options->toArray();
       $this->assertTrue(is_array($data));

    }

}