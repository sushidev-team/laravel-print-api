<?php


namespace AMBERSIVE\PdfPrinter\Classes;
 
class PdfPrinterOption {

     public String $filename;
     public String $postBackUrl;
     public Array  $postBackBody;
     public String $token;
     public bool $autodelete = false;

     public function __construct(String $filename, String $postBackUrl = null, Array $postBackBody = [], String $token = null, bool $autodelete = false) {
        $this->filename     = $filename;
        $this->postBackUrl  = $postBackUrl;
        $this->postBackBody = $postBackBody;
        $this->token        = $token;
        $this->autodelete   = $autodelete;
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