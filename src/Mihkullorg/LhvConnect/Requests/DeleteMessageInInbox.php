<?php

namespace Mihkullorg\LhvConnect\Requests;

use GuzzleHttp\Client;

class DeleteMessageInInbox extends BasicRequest
{
    protected $url = '/messages/';
    protected $method = 'DELETE';

    public function __construct(Client $client, $configuration, $body, array $headers, $id)
    {
        $this->url .= $id;
        parent::__construct($client, $configuration, $body, $headers);
    }
}
