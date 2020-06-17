<?php


namespace AMBERSIVE\PdfPrinter\Classes;

use GuzzleHttp\Psr7\Response;
 
class PdfPrinterResult {

     public int $statusCode = 0;
     public String $requestUrl;
     public String $downloadUrl;
     public bool $uploaded = false;
     public String $filename;

     public function __construct(Response $response = null) {
         $json = $response === null ? null : json_decode($response->getBody());
         if ($json !== null){
             $this->statusCode = isset($json->statusCode) ? $json->statusCode : 0;
             $this->requestUrl = isset($json->requestUrl) ? $json->requestUrl : null;
             $this->downloadUrl = isset($json->downloadUrl) ? $json->downloadUrl : null;
             $this->uploaded = isset($json->uploaded) ? $json->uploaded : false;
             $this->filename = isset($json->filename) ? "$json->filename.pdf" : false;
         }
     }

}