<?php 

namespace AMBERSIVE\PdfPrinter\Interfaces;

use AMBERSIVE\PdfPrinter\Classes\PdfPrinter;
use AMBERSIVE\PdfPrinter\Classes\PdfPrinterOption;
use AMBERSIVE\PdfPrinter\Classes\PdfPrinterSetting;

interface PdfPrinterInterface {

    public function __construct(PdfPrinterSetting $settings = null, \GuzzleHttp\Client $client);

    public function create(String $url, PdfPrinterOption $options = null, Callable $callback = null):PdfPrinter;
    public function save(String $path, String $disk = null, Callable $callback = null):PdfPrinter;

}
