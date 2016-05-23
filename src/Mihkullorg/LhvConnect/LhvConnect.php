<?php

namespace Mihkullorg\LhvConnect;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Mihkullorg\LhvConnect\Requests\HeartbeatGetRequest;
use Mihkullorg\LhvConnect\Requests\MerchantPaymentReportRequest;

class LhvConnect {

    private $client;
    private $configuration;
    
    public function __construct($name)
    {
        $this->configuration = Config::get('lhv-connect.' . $name);
        $this->client = new Client([
            'base_uri' => BANK_URI,
        ]);
    }

    public function makeHeartbeatGetRequest()
    {
        $request = new HeartbeatGetRequest($this->client, $this->configuration);
        
        return $request->sendRequest();
    }

    public function makeHeartbeatPostRequest()
    {
        //TODO
    }
    
    public function makeMerchantPaymentReportRequest(array $data)
    {
        $request = new MerchantPaymentReportRequest($this->client, $this->configuration, $data);
        
        return $request->sendRequest();
    }


}