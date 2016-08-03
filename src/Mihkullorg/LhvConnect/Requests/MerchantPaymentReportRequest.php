<?php

namespace Mihkullorg\LhvConnect\Requests;

use DateInterval;
use DateTime;
use Mihkullorg\LhvConnect\Tag;

/**
 * The xml generating is updated. This class isn't

class MerchantPaymentReportRequest extends FullRequest {

    protected $url = "merchant-report";
    protected $method = "POST";

    protected $xmlTag = Tag::MERCHANT_REPORT_REQUEST;
    protected $xmlFormat = "";

    protected $rules = [
        'MERCHANT_PAYMENT_TYPE' => 'in:CAMT_SETTLEMENT,CAMT_TRANSACTION',
        'PERIOD_START' => 'date',
        'PERIOD_END' => 'date',
    ];

    protected $fields = [
        'PERIOD_START' => "",
        'PERIOD_END' => "",
        'MERCHANT_PAYMENT_TYPE' => "",
    ];

    protected $xml = [
        'MERCHANT_PAYMENT_TYPE' => "",
        'PERIOD_START' => "",
        'PERIOD_END' => "",
    ];

    protected function prepareFields()
    {
        $this->fields['MERCHANT_PAYMENT_TYPE'] = "CAMT_SETTLEMENT";
        $dateTime = new DateTime(); // It's now
        $this->fields['PERIOD_START'] = $dateTime->sub(new DateInterval('P1M'))->format('Y-m-d');  //Last month
        $this->fields['PERIOD_END'] = (new DateTime())->format('Y-m-d');
    }

    protected function prepareXmlArray()
    {
        // Nothing to do here
    }
}
 */
