<?php

namespace Mihkullorg\LhvConnect\Requests;

class HeartbeatGetRequest extends BasicRequest
{
    protected $method = "GET";
    protected $url = "heartbeat";
}
