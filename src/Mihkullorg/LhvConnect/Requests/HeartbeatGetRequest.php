<?php

namespace Mihkullorg\LhvConnect\Request;

class HeartbeatGetRequest extends BasicRequest {

    protected $method = "GET";
    protected $url = "heartbeat";
    protected $name = "Heartbeat";

    public function handleMessage($message)
    {
        return "Service is online and you are authorized";
    }
}