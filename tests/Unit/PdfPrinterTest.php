<?php

namespace AMBERSIVE\Tests\Unit\Classes;

use AMBERSIVE\PdfPrinter\Classes\PdfPrinter;
use AMBERSIVE\PdfPrinter\Classes\PdfPrinterMockResponse;
use AMBERSIVE\PdfPrinter\Classes\PdfPrinterSetting;
use AMBERSIVE\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Storage;
use Str;

class PdfPrinterTest extends TestCase
{
    public PdfPrinter $pdfPrinter;
    public PdfPrinterSetting $pdfPrinterSettings;

    public String $filename;

    protected function setUp(): void
    {
        parent::setUp();

        $settings = new PdfPrinterSetting('http://localhost', 3000);
        $this->pdfPrinterSettings = $settings;

        // Clear storage
        $this->clearStorage();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clear storage
        $this->clearStorage();
    }

    /**
     * Clear the storage for tests.
     *
     * @return void
     */
    protected function clearStorage(): void
    {
        $this->filename = '__PdfPrintText__';
        Storage::disk('local')->exists("test/$this->filename.pdf") ? Storage::disk('local')->delete("test/$this->filename.pdf") : null;
        Storage::disk('local')->exists("$this->filename.pdf") ? Storage::disk('local')->delete("$this->filename.pdf") : null;
    }

    /**
     * Helper method to create the mock guzzele responses.
     *
     * @param  mixed $status
     * @param  mixed $responses
     * @return PdfPrinter
     */
    private function createApiMock(array $responses = [], $settings = null): PdfPrinter
    {
        $mock = new MockHandler(array_map(function ($item) {
            return $item->get();
        }, $responses));
        $handler = HandlerStack::create($mock);

        $pdfPrinter = new PdfPrinter($settings === null ? $this->pdfPrinterSettings : $settings, new Client(['handler' => $handler]));

        return $pdfPrinter;
    }

    public function testIfPdfPrinterWorksWithMinimalSetup():void
    {
        $response1 = new PdfPrinterMockResponse(200, [
            'statusCode' => 200,
            'uploaded'   => false,
            'downloadUrl' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
            'filename' => $this->filename,
        ]);

        $response2 = new PdfPrinterMockResponse(200, [
            'statusCode' => 200,
        ]);

        $pdfPrinter = $this->createApiMock([$response1, $response2]);

        $pdfPrinter->create('http://127.0.0.1:8000', null, function ($instance, $result, $options, $successful) {
            $this->assertTrue($successful);
            $this->assertEquals(200, $result->statusCode);
            $this->assertFalse(Storage::disk('local')->exists("test/$this->filename.pdf"));
        })->save('test', null, function ($instance, $result, $filename, $path, $successful) {
            $this->assertTrue(Storage::disk('local')->exists("test/$this->filename.pdf"));
        });
    }

    /**
     * Test if the $result attribute will filled.
     */
    public function testIfPdfPrinterWillFillTheResult():void
    {
        $response = new PdfPrinterMockResponse(200, [
            'statusCode' => 200,
            'uploaded'   => false,
            'downloadUrl' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
            'filename' => $this->filename,
        ]);

        $pdfPrinter = $this->createApiMock([$response]);

        $result = $pdfPrinter->create('http://127.0.0.1:8000', null, function ($instance, $result, $options, $successful) {
            $this->assertEquals(200, $result->statusCode);
            $this->assertTrue($successful);
        });

        $this->assertNotNull($result->result);
        $this->assertEquals(200, $result->result->statusCode);
        $this->assertFalse(Storage::disk('local')->exists("test/$this->filename.pdf"));
    }

    public function testIfPdfPrinterSupportTestmode():void
    {
        $response = new PdfPrinterMockResponse(200, []);
        $pdfPrinter = $this->createApiMock([$response]);

        $result = $pdfPrinter->useTestmode()->create('http://127.0.0.1:8000', null, function ($instance, $result, $options, $successful) {
            $this->assertEquals(200, $result->statusCode);
            $this->assertTrue($successful);
            $this->assertEquals('dummy.pdf', $result->filename);
        });
    }

    /**
     * Test if the print request can fail.
     */
    public function testIfPdfPrinterCanFail():void
    {
        $response = new PdfPrinterMockResponse(400, [
            'statusCode'  => 400,
            'uploaded'    => false,
            'downloadUrl' => null,
            'filename'    => null,
        ]);

        $pdfPrinter = $this->createApiMock([$response]);

        $result = $pdfPrinter->create('http://127.0.0.1:8000', null, function ($instance, $result, $options, $successful) {
            $this->assertFalse($successful);
            $this->assertEquals(400, $result->statusCode);
            $this->assertFalse(Storage::disk('local')->exists("test/$this->filename.pdf"));
        });

        $this->assertEquals(400, $pdfPrinter->result->statusCode);
        $this->assertFalse(Storage::disk('local')->exists("test/$this->filename.pdf"));
    }

    /**
     * Test if the pdf is only stored if you call the save method.
     */
    public function testIfPdfPrinterWillCallUrlWithoutException():void
    {
        $response1 = new PdfPrinterMockResponse(200, [
            'statusCode' => 200,
            'uploaded'   => false,
            'downloadUrl' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
            'filename' => $this->filename,
        ]);

        $response2 = new PdfPrinterMockResponse(200, [
            'statusCode' => 200,
        ]);

        $pdfPrinter = $this->createApiMock([$response1, $response2]);

        $result = $pdfPrinter->create('http://127.0.0.1:8000', null, function ($instance, $result, $options, $successful) use ($response2) {
            $this->assertFalse(Storage::disk('local')->exists("test/$this->filename.pdf"));
        })->save('test', null, function ($instance, $result, $filename, $path, $successful) {
            $this->assertTrue(Storage::disk('local')->exists("test/$this->filename.pdf"));
        });
    }

    /**
     * Test if the save function will not be executed if the.
     */
    public function testIfPdfPrinterSaveWillNotBeExecutedIfCreateWasNotSuccessful():void
    {
        $response = new PdfPrinterMockResponse(400, [
            'statusCode'  => 400,
            'uploaded'    => false,
            'downloadUrl' => null,
            'filename'    => null,
        ]);

        $pdfPrinter = $this->createApiMock([$response]);
        $executed = false;

        $result = $pdfPrinter->create('http://127.0.0.1:8000', null, function ($instance, $result, $options, $successful) {
            $this->assertFalse($successful);
            $this->assertEquals(400, $result->statusCode);
            $this->assertFalse(Storage::disk('local')->exists("test/$this->filename.pdf"));
        })->save('test', null, function ($instance, $result, $filename, $path, $successful) use (&$executed) {
            $executed = $successful == false ? false : true;
        });

        $this->assertEquals(400, $pdfPrinter->result->statusCode);
        $this->assertFalse(Storage::disk('local')->exists("test/$this->filename.pdf"));
        $this->assertFalse($executed);
    }

    /**
     * Test if the.
     */
    public function testIfPdfPrinterSaveWillRespectTheFolder():void
    {
        $response1 = new PdfPrinterMockResponse(200, [
            'statusCode' => 200,
            'uploaded'   => false,
            'downloadUrl' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
            'filename' => $this->filename,
        ]);

        $response2 = new PdfPrinterMockResponse(200, [
            'statusCode' => 200,
        ]);

        $pdfPrinter = $this->createApiMock([$response1, $response2]);

        $result = $pdfPrinter->create('http://127.0.0.1:8000', null, function ($instance, $result, $options, $successful) {
            $this->assertTrue($successful);
            $this->assertEquals(200, $result->statusCode);
            $this->assertFalse(Storage::disk('local')->exists("$this->filename.pdf"));
            $this->assertFalse(Storage::disk('local')->exists("test/$this->filename.pdf"));
        })->save(null, null, function ($instance, $result, $filename, $path, $successful) use (&$executed) {
            $this->assertTrue($successful);
            $this->assertTrue(Storage::disk('local')->exists("$this->filename.pdf"));
            $this->assertFalse(Storage::disk('local')->exists("test/$this->filename.pdf"));
        });
    }

    /**
     * Test if the method will the listed files from the api endpoint.
     */
    public function testIfPdfPrinterListWillReturnData(): void
    {
        $response = new PdfPrinterMockResponse(200, [
            [
                'file' =>  'XXX.pdf',
                'path' =>  'test/XXX.pd',
                'created_at' => '2020-06-27T22:27:09.876Z',
                'updated_at' => '2020-06-27T22:27:09.876Z',
            ],
        ]);

        $pdfPrinter = $this->createApiMock([$response]);
        $result = $pdfPrinter->listFiles();

        $this->assertNotNull($result);
        $this->assertEquals(1, $result->count());
    }
}
