<?php


namespace AMBERSIVE\PdfPrinter\Classes;
 
class PdfPrinterOption {

     public String $filename;
     public String $postBackUrl;
     public Array  $postBackBody;
     public String $token;

     public function __construct(String $filename, String $postBackUrl = null, Array $postBackBody = [], String $token = null) {
        $this->filename     = $filename;
        $this->postBackUrl  = $postBackUrl;
        $this->postBackBody = $postBackBody;
        $this->token        = $token;
     }

          
     /**
      * Will return the json decoded string
      * This is required cause the FormData cannot store json
      *
      * @return String
      */
     public function getPostBackBody(): String {
         return json_encode($this->postBackBody != null ? $this->postBackBody : []);
     }

}