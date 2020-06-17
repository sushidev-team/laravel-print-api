<?php

namespace AMBERSIVE\PdfPrinter\Tests\Unit\Classes;

use Tests\TestCase;

use AMBERSIVE\PdfPrinter\Classes\PdfPrinter;
use AMBERSIVE\PdfPrinter\Classes\PdfPrinterSetting;

class PdfPrinterTest extends TestCase
{

    public PdfPrinter $pdfPrinter;
    public PdfPrinterSetting $pdfPrinterSettings;

    protected function setUp(): void
    {
        parent::setUp();

        $settings = new PdfPrinterSetting("http://localhost", 3000);

        $this->pdfPrinter         = new PdfPrinter($settings);
        $this->pdfPrinterSettings = $settings;

    }

    /**
     * Test if the returned path is equals to the provided path in the config
     */
    public function testIfPdfPrinterWillCallUrlWithoutException():void {

        $result = $this->pdfPrinter->create("http://127.0.0.1:8000", null, function() {
            dd('asdf');
        })->save('test', null, function(){
            dd('test');
        });

    }

}