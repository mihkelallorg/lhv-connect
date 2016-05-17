<?php

namespace LhvConnect\Request;

use LhvConnect\Tags;

class AccountStatementRequest extends FullRequest {

    protected $data;
    protected $client;
    protected $xmlFile;

    protected $url = "account-statement";
    protected $method = "POST";

    protected $xmlTag = Tags::ACCOUNT_STATEMENT_REQUEST;
    protected $xmlFormat = "camt.060.001.03";
    protected $fields = [
        'REQUEST_IDENTIFICATION' => "",
        'CREATION_DATETIME' => "",
        'IBAN' => "",
        'FROM_DATE' => "",
        'TO_DATE' => "",
        'FROM_TIME' => "",
        'TO_TIME' => "",
    ];

    protected $xml = [
        'ACCOUNT_STATEMENT_REQUEST' => [
            'GROUP_HEADER' => [
                'MESSAGE_IDENTIFICATION' => "",
                'CREATION_DATETIME' => "",
            ],
            'REPORTING_REQUEST' => [
                'REQUEST_IDENTIFICATION' => "",
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
                    'FROM_TO_TIME' => [
                        'FROM_TIME' => "",
                        'TO_TIME' => "",
                    ],
                    'TYPE' => ""
                ],
            ],
        ],
    ];

    protected function handleMessage($message)
    {
        // TODO: Implement handleMessage() method.
    }
}