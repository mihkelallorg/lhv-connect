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

    public function __construct(Client $client, $configuration, $body = null, $headers = [])
    {
        date_default_timezone_set('Europe/Istanbul');
        $this->client = $client;
        $this->configuration = $configuration;
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
     * Make the request to the server
     *
     * @return \Psr\Http\Message\ResponseInterface
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

        return $options;
    }
}