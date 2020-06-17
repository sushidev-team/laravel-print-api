<?php 

namespace AMBERSIVE\PdfPrinter\Classes;

use AMBERSIVE\PdfPrinter\Interfaces\PdfPrinterInterface;
use AMBERSIVE\PdfPrinter\Classes\PdfPrinterOption;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

use \Illuminate\Http\Response;

use Validator;
use Storage;
use Str;

class PdfPrinter implements PdfPrinterInterface {

    public PdfPrinterResult $result;
    public PdfPrinterSetting $settings;
    public Client $client;

    public String $authType;
    public String $authToken;

    public function __construct(PdfPrinterSetting $settings) {
        $this->settings = $settings;
        $this->client   = new Client();
    }

    public function authBasic(String $type, String $username, String $password): PdfPrinter {
        $this->authType  = 'Basic';
        $this->authToken = "Basic ".base64_encode("${username}:${password}");
        return $this;
    }

    public function authBearer(String $token): PdfPrinter {
        $this->authType  = 'Bearer';
        $this->authToken = "$this->authType ${token}";
        return $this;
    }
    
    /**
     * Send a print request to the print api
     *
     * @param  mixed $url
     * @param  mixed $options
     * @param  mixed $callback
     * @return PdfPrinter
     */
    public function create(String $url, PdfPrinterOption $options = null, Callable $callback = null): PdfPrinter {

        try {

            $validator = Validator::make(['url' => $url], [
                'url' => 'required|url'
            ]);

    
            if ($validator->fails()) {
                throw Exception('missing url');
            }

            $headers = [];

            if ($this->authType !== null) {
                $headers['Authorization'] = $this->authToken;
            }

            $response = $this->client->request("POST", $this->settings->url("api/browse"), [
                'headers' => $headers,
                'json' => [
                    'url' => $url
                ]
            ]);

            $this->result = new PdfPrinterResult($response);

        } catch (\GuzzleHttp\Exception\ServerException $ex) {

            $this->result = new PdfPrinterResult();
            $this->result->statusCode = 500;
            
        } catch (\GuzzleHttp\Exception\ClientException $ex) {

            $this->result = new PdfPrinterResult();
            $this->result->statusCode = $ex->getResponse()->getStatusCode();

        }

        if (is_callable($callback)) {
            $callback($this, $this->result, $options);
        }

        return $this;
    }
    
    /**
     * Downlaod the pdf document if the the endpoint failed uploading it.
     *
     * @param  mixed $path
     * @param  mixed $disk
     * @param  mixed $callback
     * @return PdfPrinter
     */
    public function save(String $path, String $disk = null, Callable $callback = null): PdfPrinter {

        $filename = "";

        if ($this->result->statusCode === 200 && $this->result->uploaded !== true && $this->result->downloadUrl !== null) {

            if(!Storage::disk($disk != null ? $disk : 'local')->exists($path)) {
                Storage::disk($disk != null ? $disk : 'local')->makeDirectory($path, 0775, true); //creates directory
            }

            $filename = $this->result->filename != null && $this->result->filename != "" ? $this->result->filename : Str::random(20).".pdf";
            $resource = fopen(Storage::disk($disk != null ? $disk : 'local')->path("${path}/$filename"), 'w');
            $stream = \GuzzleHttp\Psr7\stream_for($resource);

            $this->client->request('GET', $this->result->downloadUrl, [
                'headers' => [
                    'Cache-Control' => 'no-cache', 
                    'Content-Type' => 'application/pdf'
                ],
                'save_to' => $stream
            ]);

        }

        if (is_callable($callback)) {
            $callback($this, $this->result, $filename, $path);
        }

        return $this;
    }

}