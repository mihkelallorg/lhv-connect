<?php

namespace Mihkullorg\LhvConnect\Requests;

use GuzzleHttp\Client;

abstract class FullRequest extends BasicRequest
{
    protected $data; //The data user sets. FROM_DATE, TO_DATE etc. Depends on the request

    protected $xmlTag;
    protected $attributes;

    protected $rules; //The rules for user input ($data)

    public function __construct(Client $client, $configuration, array $data = [], $body = null, $headers = [])
    {
        parent::__construct($client, $configuration, $body, $headers);

        $this->data = $data;
    }

    /**
     * Return the xml as a string.
     *
     * @return string
     */
    abstract public function getXML();
}
