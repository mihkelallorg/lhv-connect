<?php

namespace LhvConnect\Request;

use LhvConnect\Tags;

class MerchantPaymentReportRequest extends FullRequest {

    protected $data;
    protected $client;
    protected $xmlFile;

    protected $url = "merchant-report";
    protected $method = "POST";

    protected $xmlTag = Tags::MERCHANT_REPORT_REQUEST;
    protected $xmlFormat = "";
    protected $fields = [
        'PERIOD_START' => "",
        'PERIOD_END' => "",
        'TYPE' => "CAMT_SETTLEMENT",
    ];

    protected $xml = [
        'MERCHANT_REPORT_REQUEST' => [
            'TYPE' => "",
            'PERIOD_START' => "",
            'PERIOD_END' => "",
        ]
    ];

    public function handleMessage($message)
    {
        return $message;
    }

}
