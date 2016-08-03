<?php

namespace Mihkullorg\LhvConnect\Requests;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

abstract class BasicRequest {

    protected $url;
    protected $method;
    protected $client;
    protected $configuration;
    protected $body;
    protected $headers;
    protected $files;


    public function __construct(Client $client, $configuration, $body = null, $headers = [], $files = [])
    {
        $this->client = $client;
        $this->configuration = $configuration;
        $this->headers = $headers;
        $this->body = $body;
        $this->files = $files;
    }

    /**
     * Make the request to the server
     * 
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function sendRequest()
    {
        $options = $this->prepareRequestOptions();

        $response = $this->client->request($this->method, $this->url, $options);
        
        return $response;
    }

    private function prepareRequestOptions()
    {
        $options = [
            RequestOptions::CERT => [
                $this->configuration['cert']['path'],
                $this->configuration['cert']['password'],
            ],
            RequestOptions::BODY => $this->body,
            RequestOptions::HEADERS => $this->headers,
        ];

        if ( ! empty($this->files))
        {
            $options[RequestOptions::MULTIPART] = [];
            foreach($this->files as $file)
            {
                $options[RequestOptions::MULTIPART][] = [
                    'name'      => "file",
                    'filename'  => "request.bdoc",
                    'contents'  => $file,
                ];
            }
        }

        return $options;
    }
}