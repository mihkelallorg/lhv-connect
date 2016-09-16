<?php

namespace Mihkullorg\LhvConnect;

use DateTime;
use SimpleXMLElement;

class XMLGenerator {

    /**
     * Transforms the xml array to XML
     *
     * @param array $data
     * @param SimpleXMLElement $xml_data
     */
    protected static function array_to_xml(array $data, SimpleXMLElement &$xml_data)
    {
        foreach( $data as $key => $value ) {
            if( is_array($value) ) {
                $subnode = $xml_data->addChild(constant("Mihkullorg\\LhvConnect\\Tag::$key"));
                self::array_to_xml($value, $subnode);
            } else {
                $xml_data->addChild(constant("Mihkullorg\\LhvConnect\\Tag::$key"), $value);
            }
        }
    }

    /**
     * @param array $data
     * @param array $configuration
     * @return string
     */
    public static function paymentInitiationXML(array $data, array $configuration)
    {
        $xmlTag = Tag::PAYMENT_INITIATION_REQUEST;

        $sum = 0;

        foreach($data['payments'] as $payment)
        {
            $sum += $payment['sum'];
        }

        $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<Document xmlns=\"urn:iso:std:iso:20022:tech:xsd:pain.001.001.03\" 
xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" 
xsi:schemaLocation=\"urn:iso:std:iso:20022:tech:xsd:pain.001.001.03 pain.001.001.03.xsd\"><$xmlTag></$xmlTag></Document>");

        self::array_to_xml(self::generatePaymentInitiationGroupHeaderXml(count($data['payments']), $sum, array_get($data, 'initiator', '')), $xml->$xmlTag);

        foreach ($data['payments'] as $payment)
        {
            self::array_to_xml(self::generatePaymentInitiationPaymentXml($payment, $configuration), $xml->$xmlTag);
        }

        return $xml->asXML();

    }

    private static function generatePaymentInitiationPaymentXml($payment, $configuration)
    {
        return [
            "PAYMENT_INFORMATION" => [
                "PAYMENT_INFORMATION_IDENTIFICATION"    => $payment['id'],
                "PAYMENT_METHOD"                        => "TRF",
                "INNER_NUMBER_OF_TRANSACTIONS"          => 1,
                "REQUESTED_EXECUTION_DATE"              => (new DateTime())->format("Y-m-d"),
                "REMITTER"                              => [
                    "REMITTER_NAME" => $configuration['name'],
                ],
                "REMITTER_ACCOUNT"                      => [
                    "REMITTER_ACCOUNT_IDENTIFICATION" => [
                        "IBAN"      => $configuration['IBAN'],
                    ],
                    "CURRENCY"  => $payment['currency'],
                ],
                "REMITTER_AGENT"                        => [
                    "FINANCIAL_INSTITUTION_IDENTIFICATION" => [
                        "BIC" => $configuration['bic'],
                    ],
                ],
                "CHARGES_BEARER"                            => "DEBT",
                "CREDIT_TRANSFER_TRANSACTION_INFORMATION"   => [
                    "PAYMENT_IDENTIFICATION" => [
                        "END_TO_END_IDENTIFICATION" => rand(100000, 999999),
                    ],
                    "PAYMENT_TYPE_INFORMATION" => [
                        "LOCAL_INSTRUMENT" => [
                            "PROPRIETARY" => "NORM",
                        ],
                    ],
                    "AMOUNT" => [
                        "INSTRUCTED_AMOUNT" => $payment['sum'],
                    ],
                    "CHARGES_BEARER"       => "DEBT",
                    "BENEFICIARY" => [
                        "BENEFICIARY_NAME" => $payment['name'],
                    ],
                    "BENEFICIARY_ACCOUNT"   => [
                        "BENEFICIARY_ACCOUNT_IDENTIFICATION" => [
                            "IBAN" => $payment['IBAN'],
                        ],
                    ],
                    "REMITTANCE_INFORMATION" => [
                        "UNSTRUCTURED" => $payment['description'],
                    ],
                ],
            ],
        ];
    }

    private static function generatePaymentInitiationGroupHeaderXml($count, $sum, $initiator)
    {
        return [
            "GROUP_HEADER" => [
                "MESSAGE_IDENTIFICATION"    => str_random(16),
                "CREATION_DATETIME"         => (new DateTime())->format(DateTime::ISO8601),
                "NUMBER_OF_TRANSACTIONS"    => $count,
                "CONTROL_SUM"               => $sum,
                "INITIATING_PARTY"          => [
                    "NAME" => $initiator,
                ],
            ],
        ];
    }

}