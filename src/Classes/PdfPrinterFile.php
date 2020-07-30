<?php


namespace AMBERSIVE\PdfPrinter\Classes;

use GuzzleHttp\Psr7\Response;

use Carbon\Carbon;
 
class PdfPrinterFile {

     public String $file;
     public String $path;
     public Carbon $createdAt;
     public Carbon $updatedAt;

     public function __construct(Object $file = null) {
            $this->file = optional($file)->filename !== null ? optional($file)->filename : '';
            $this->path = optional($file)->path !== null ? optional($file)->path : '';
            $this->createdAt = Carbon::parse(optional($file)->created_at);
            $this->updatedAt = Carbon::parse(optional($file)->updated_at);
     }

}