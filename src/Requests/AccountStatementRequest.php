<?php

namespace LhvConnect\Request;

use DateInterval;
use DateTime;
use LhvConnect\Tags;

class AccountStatementRequest extends FullRequest {

    protected $data;
    protected $client;
    protected $xmlFile;

    protected $url = "account-statement";
    protected $method = "POST";

    protected $xmlTag = Tags::ACCOUNT_STATEMENT_REQUEST;
    protected $xmlFormat = "camt.060.001.03";

    protected $rules = [
        'IBAN' => 'required',
        'FROM_DATE' => 'date',
        'TO_DATE' => 'date'
    ];

    protected $fields = [
        'MESSAGE_IDENTIFICATION' => "",
        'CREATION_DATETIME' => "",
        'IBAN' => "",
        'FROM_DATE' => "",
        'TO_DATE' => "",
        'TYPE' => "",
        'REQUESTED_MESSAGE_NAME_IDENTIFICATION' => ""
    ];

    protected $xml = [
        'ACCOUNT_STATEMENT_REQUEST' => [
            'GROUP_HEADER' => [
                'MESSAGE_IDENTIFICATION' => "",
                'CREATION_DATETIME' => "",
            ],
            'REPORTING_REQUEST' => [
                'REQUESTED_MESSAGE_NAME_IDENTIFICATION' => "",
                'ACCOUNT' => [
                    'ACCOUNT_IDENTIFICATION' => [
                        'IBAN' => "",
                    ],
                ],
                'ACCOUNT_OWNER' => [
                    'PARTY' => ""
                ],
                'REPORTING_PERIOD' => [
                    'FROM_TO_DATE' => [
                        'FROM_DATE' => "",
                        'TO_DATE' => ""
                    ],
                    'TYPE' => ""
                ],
            ],
        ],
    ];

    protected function handleMessage($message)
    {
        return $message;
    }

    protected function prepareFields()
    {
        $this->fields['MESSAGE_IDENTIFICATION'] = $this->msgId;
        
        $dateTime = new DateTime(); // It's now
        $this->fields['CREATION_DATETIME'] = $dateTime::ISO8601;
        $this->fields['FROM_DATE'] = $dateTime->sub(new DateInterval('P1M'))->format('Y-m-d');  //Last month
        $this->fields['TO_DATE'] = $dateTime->format('Y-m-d');

        $this->fields['TYPE'] = "ALLL";
        $this->fields['REQUESTED_MESSAGE_NAME_IDENTIFICATION'] = $this->xmlFormat;
    }

    protected function prepareXmlArray()
    {
        // Nothing to do here
    }
}