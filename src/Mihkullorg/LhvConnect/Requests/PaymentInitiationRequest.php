<?php

namespace Mihkullorg\LhvConnect\Requests;

use Mihkullorg\LhvConnect\Tag;
use SimpleXMLElement;

class PaymentInitiationRequest extends FullRequest {

    protected $url = "payment-initiation";
    protected $method = "POST";

    protected $xmlTag = Tag::PAYMENT_INITIATION_REQUEST;
    protected $xmlFormat = "";

    protected $fields = [
        "MESSAGE_IDENTIFICATION"        => "",
        "CREATION_DATETIME"             => "",
        "NUMBER_OF_TRANSACTIONS"        => "",
        "CONTROL_SUM"                   => "",
        "PAYMENT_METHOD"                => "",
        "BATCH_BOOKING"                 => "",
        "INNER_NUMBER_OF_TRANSACTIONS"  => "",
        "REQUESTED_EXECUTION_DATE"      => "",
        "REMITTER_NAME"                 => "",
    ];

    protected $xml = [

        "PAYMENT_INFORMATION" => [
            "PAYMENT_INFORMATION_IDENTIFICATION"    => "",
            "PAYMENT_METHOD"                        => "",
            "BATCH_BOOKING"                         => "",
            "INNER_NUMBER_OF_TRANSACTIONS"          => "",
            "INNER_CONTROL_SUM"                     => "",
            "REQUESTED_EXECUTION_DATE"              => "",
            "REMITTER"                              => [
                "REMITTER_NAME" => "",
            ],
            "REMITTER_ACCOUNT"                      => [
                "REMITTER_ACCOUNT_IDENTIFICATION" => [
                    "IBAN"      => "",
                    "CURRENCY"  => "",
                ],
            ],
            "REMITTER_AGENT"                        => [
                "FINANCTIAL_INSTITUTION_IDENTIFICATION" => [
                    "BIC" => "",
                ],
            ],
            "CHARGES_BEARER"                        => "",
            "CREDIT_TRANSFER_TRANSACTION_INFORMATION" => [
                "PAYMENT_IDENTIFICATION" => [
                    "END_TO_END_IDENTIFICATION" => "",
                ],
                "PAYMENT_TYPE_IDENTIFICATION" => [
                    "LOCAL_INSTRUMENT" => [
                        "PROPRIETARY" => "",
                    ],
                ],
                "AMOUNT" => [
                    "INSTRUCTED_AMOUNT" => "",
                ],
                "CHARGERS_BEARER"   => "",
                "BENEFICIARY_AGENT" => [
                    "FINANCTIAL_INSTITUTION_IDENTIFICATION" => "",
                ],
                "BENEFICIARY" => [
                    "BENEFICIARY_NAME" => "",
                ],
                "BENEFICIARY_ACCOUNT" => [
                    "BENEFICIARY_ACCOUNT_IDENTIFICATION" => [
                        "IBAN" => "",
                    ],
                ],
                "REMITTANCE_INFORMATION" => [
                    "UNSTRUCTURED" => "",
                ],
            ],
        ],
    ];


    protected function prepareFields()
    {
        // TODO: Implement prepareFields() method.
    }

    /**
     * Return the xml as a string
     *
     * @return string
     */
    protected function createXML()
    {
        $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><$this->xmlTag></$this->xmlTag>");

        $this->array_to_xml($this->getGroupHeaderArray(), $xml);
        $this->array_to_xml($this->getPaymentsXml(), $xml);

        return $xml->asXML();
    }

    private function getGroupHeaderArray()
    {
        return [
            "GROUP_HEADER" => [
                "MESSAGE_IDENTIFICATION"    => "",
                "CREATION_DATETIME"         => "",
                "NUMBER_OF_TRANSACTIONS"    => "",
                "CONTROL_SUM"               => "",
                "INITIATING_PARTY"          => [
                    "NAME" => "",
                ],
            ],
        ];
    }

    private function getPaymentsXml()
    {
        return [];
    }


}