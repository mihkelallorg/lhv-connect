<?php

namespace Mihkullorg\LhvConnect\Requests;

use DateInterval;
use DateTime;
use Mihkullorg\LhvConnect\Tag;

class MerchantPaymentReportRequest extends FullRequest {

    protected $url = "merchant-report";
    protected $method = "POST";

    protected $xmlTag = Tag::MERCHANT_REPORT_REQUEST;
    protected $xmlFormat = "";

    protected $rules = [
        'TYPE' => 'in:CAMT_SETTLEMENT,CAMT_TRANSACTION',
        'PERIOD_START' => 'date',
        'PERIOD_END' => 'date',
    ];

    protected $fields = [
        'PERIOD_START' => "",
        'PERIOD_END' => "",
        'TYPE' => "",
    ];

    protected $xml = [
        'TYPE' => "",
        'PERIOD_START' => "",
        'PERIOD_END' => "",
    ];

    /**
     * Set the (default) values for fields
     * Some might be overwritten by input data
     */
    protected function prepareFields()
    {
        $this->fields['TYPE'] = "CAMT_SETTLEMENT";
        $dateTime = new DateTime(); // It's now
        $this->fields['PERIOD_START'] = $dateTime->sub(new DateInterval('P1M'))->format('Y-m-d');  //Last month
        $this->fields['PERIOD_END'] = (new DateTime())->format('Y-m-d');
    }

    protected function prepareXmlArray()
    {
        // Nothing to do here
    }
}
