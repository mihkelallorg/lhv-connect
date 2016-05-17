<?php

namespace LhvConnect;

use GuzzleHttp\Client;
use LhvConnect\Request\HeartbeatGetRequest;
use LhvConnect\Request\MerchantPaymentReportRequest;

class LhvConnect {

    private $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => BANK_URI,
        ]);
    }

    public function makeHeartbeatGetRequest()
    {
        $request = new HeartbeatGetRequest($this->client);
        
        return $request->sendRequest();
    }

    public function makeHeartbeatPostRequest()
    {
        //TODO
    }
    
    public function makeMerchantPaymentReportRequest(array $data)
    {
        $request = new MerchantPaymentReportRequest($this->client, $data);
        
        return $request->sendRequest();
    }


}