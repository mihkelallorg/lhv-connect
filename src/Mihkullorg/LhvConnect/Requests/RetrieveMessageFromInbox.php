<?php

namespace Mihkullorg\LhvConnect\Requests;

class RetrieveMessageFromInbox extends BasicRequest
{
    protected $url = '/messages/next';
    protected $method = 'GET';
}
