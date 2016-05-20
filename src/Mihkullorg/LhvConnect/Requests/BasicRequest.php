<?php

namespace Mihkullorg\LhvConnect\Request;

use Exception;
use ForbiddenException;
use GuzzleHttp\Client;
use Mihkullorg\LhvConnect\Exceptions\ServiceUnavailableException;
use Mihkullorg\LhvConnect\ResponseCode;
use Psr\Http\Message\ResponseInterface;

abstract class BasicRequest {

    protected $url;
    protected $method;
    protected $client;

    public function __construct(Client $client, $cert)
    {
        $this->cert = $cert;
        $this->client = $client;
    }

    public function sendRequest()
    {
        $response = $this->client->request($this->method, $this->url, $this->params);

        return $this->handleResponse($response);
    }

    protected function handleResponse(ResponseInterface $response)
    {
        if ($response->getStatusCode() != ResponseCode::OK)
        {
            $this->handleError($response->getStatusCode(), $response->getBody());    
        } else
        {
            $message = $this->getMessageFromContainer($response->getHeader('Message-Request-Id'));

            return $this->handleMessage($message);
        }
    }

    protected function getMessageFromContainer($id)
    {
        $response = $this->client->request('GET', 'messages/' . $id);

        return $response;
    }

    protected function handleError($code, $message)
    {
        switch($code){
            case ResponseCode::FORBIDDEN:
                throw new ForbiddenException();
                break;
            case ResponseCode::SERVICE_UNAVAILABLE:
                throw new ServiceUnavailableException();
            case ResponseCode::INTERNAL_SERVER_ERROR:
            default:
                throw new Exception($message, $code);
                break;
        }
    }
    
    protected abstract function handleMessage($message);
}