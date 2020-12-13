<?php

namespace AMBERSIVE\PdfPrinter\Classes;

class PdfPrinterOption
{
    public String $filename;
    public ?String $postBackUrl;
    public array  $postBackBody;
    public ?String $token;
    public bool $autodelete = false;
    public bool $testmode = false;

    public function __construct(String $filename, String $postBackUrl = null, array $postBackBody = [], String $token = null, bool $autodelete = false)
    {
        $this->filename = $filename;
        $this->postBackUrl = $postBackUrl;
        $this->postBackBody = $postBackBody;
        $this->token = $token;
        $this->autodelete = $autodelete;
    }

    public function useTestmode()
    {
        $this->testmode = true;

        return $this;
    }

    /**
     * Will return the json decoded string
     * This is required cause the FormData cannot store json.
     *
     * @return string
     */
    public function getPostBackBody(): String
    {
        return json_encode($this->postBackBody != null ? $this->postBackBody : []);
    }

    /**
     * Returns the options as an array.
     *
     * @return void
     */
    public function toArray()
    {
        return (array) $this;
    }
}
