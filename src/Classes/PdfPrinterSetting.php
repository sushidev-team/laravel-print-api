<?php


namespace AMBERSIVE\PdfPrinter\Classes;
 
class PdfPrinterSetting {

     public String $baseUrl;
     public int $basePort = 3000;

     public function __construct(String $baseUrl, int $port = 3000) {
        $this->baseUrl  = $baseUrl;
        $this->basePort = $port;
     }
     
     /**
      * Returns the endpoit url for the printer-api
      *
      * @return String
      */
     public function url($endpoint = null): String {
        return $this->baseUrl . ($this->basePort != 80 ? ":{$this->basePort}" : '') . ($endpoint != null && $endpoint != "" ? "/${endpoint}" : "");
     }

}