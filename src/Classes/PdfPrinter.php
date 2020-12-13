<?php

namespace AMBERSIVE\PdfPrinter\Classes;

use AMBERSIVE\PdfPrinter\Classes\PdfPrinterFile;
use AMBERSIVE\PdfPrinter\Classes\PdfPrinterOption;
use AMBERSIVE\PdfPrinter\Interfaces\PdfPrinterInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Storage;
use Str;
use Validator;

class PdfPrinter implements PdfPrinterInterface
{
    public ?PdfPrinterResult $result;
    public PdfPrinterSetting $settings;
    public Client $client;

    public String $authType;
    public String $authToken;

    public bool $testmode = false;
    public array $fakeData = [];

    public function __construct(PdfPrinterSetting $settings = null, Client $client = null)
    {
        $this->settings = $settings;
        $this->client = $client !== null ? $client : new Client();
        $this->authType = '';
    }

    public function authBasic(String $username, String $password): self
    {
        $this->authType = 'Basic';
        $this->authToken = 'Basic '.base64_encode("${username}:${password}");

        return $this;
    }

    public function authBearer(String $token): self
    {
        $this->authType = 'Bearer';
        $this->authToken = "$this->authType ${token}";

        return $this;
    }

    public function useTestmode(array $fakeData = []):self
    {
        $this->testmode = true;

        if (! empty($fakeData)) {
            $this->fakeData = $fakeData;
        } else {
            $this->fakeData = [
                'statusCode' => 200,
                'uploaded'   => false,
                'downloadUrl' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
                'filename' => 'dummy',
            ];
        }

        return $this;
    }

    /**
     * Send a print request to the print api.
     *
     * @param  mixed $url
     * @param  mixed $options
     * @param  mixed $callback
     * @return PdfPrinter
     */
    public function create(String $url, PdfPrinterOption $options = null, callable $callback = null): self
    {
        if ($options !== null && $options->testmode === true) {
            $this->useTestmode($options->fakeData);
        }

        try {
            $validator = Validator::make(['url' => $url], [
                'url' => 'required|url',
            ]);

            if ($validator->fails()) {
                throw Exception('missing url');
            }

            $headers = [];

            if ($this->authType !== null && $this->authType !== '') {
                $headers['Authorization'] = $this->authToken;
            }

            if ($this->testmode === true) {
                $response = new GuzzleResponse(200, [], json_encode($this->fakeData));
            } else {
                $response = $this->client->request('POST', $this->settings->url('api/browse'), [
                'headers' => $headers,
                'json' => array_merge([
                    'url' => $url,
                ], $options !== null ? $options->toArray() : []),
            ]);
            }

            $this->result = new PdfPrinterResult($response);
        } catch (\GuzzleHttp\Exception\ServerException $ex) {
            $this->result = new PdfPrinterResult();
            $this->result->statusCode = 500;
        } catch (\GuzzleHttp\Exception\ClientException $ex) {
            $this->result = new PdfPrinterResult();
            $this->result->statusCode = $ex->getResponse()->getStatusCode();
        }

        if (is_callable($callback)) {
            $callback($this, $this->result, $options, $this->result->statusCode === 200);
        }

        return $this;
    }

    /**
     * Returns a collection of all files available on the printer api.
     *
     * @return Collection
     */
    public function listFiles(): Collection
    {
        $headers = [];

        if ($this->authType !== null && $this->authType !== '') {
            $headers['Authorization'] = $this->authToken;
        }

        $response = $this->client->request('GET', $this->settings->url('api/browse'), [
            'headers' => $headers,
        ]);

        $json = $response === null ? null : json_decode($response->getBody());

        $result = collect($json)->map(function ($item) {
            return new PdfPrinterFile($item);
        });

        return $result;
    }

    /**
     * Downlaod the pdf document if the the endpoint failed uploading it.
     *
     * @param  mixed $path
     * @param  mixed $disk
     * @param  mixed $callback
     * @return PdfPrinter
     */
    public function save(String $path = null, String $disk = null, callable $callback = null): self
    {
        $success = false;
        $filename = '';

        if ($path === null) {
            $path = '';
        }

        if ($this->result->statusCode === 200 && $this->result->uploaded !== true && $this->result->downloadUrl !== null) {
            if (! Storage::disk($disk != null ? $disk : 'local')->exists($path)) {
                Storage::disk($disk != null ? $disk : 'local')->makeDirectory($path, 0775, true); //creates directory
            }

            $filename = $this->result->filename != null && $this->result->filename != '' ? $this->result->filename : Str::random(20).'.pdf';

            if (! Storage::disk($disk != null ? $disk : 'local')->exists($path)) {
                Storage::disk($disk != null ? $disk : 'local')->makeDirectory($path, 0775, true); //creates directory
            }

            $filename = $this->result->filename != null && $this->result->filename != '' ? $this->result->filename : Str::random(20).'.pdf';

            $this->client->request('GET', $this->result->downloadUrl, [
                'sink' => Storage::disk($disk != null ? $disk : 'local')->path("${path}/$filename"),
            ]);

            $success = true;
        }

        if (is_callable($callback)) {
            $callback($this, $this->result, $filename, $path, $success);
        }

        return $this;
    }
}
