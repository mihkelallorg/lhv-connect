<?php

namespace Mihkullorg\LhvConnect\Tests;

use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Mihkullorg\LhvConnect\LhvConnect;
use Mihkullorg\LhvConnect\Requests\HeartbeatGetRequest;
use PHPUnit\Framework\TestCase;

class LhvConnectTest extends TestCase
{
    /**
     * @test
     */
    public function it_test_a_correct_heartbeat_request_data()
    {
        $conf = [
            'IBAN' => '',
            'url'  => 'https://connect.lhv.eu',
            'cert' => ['path' => __DIR__.'/test_cert.p12', 'password' => 'password'],
        ];

        /**
         * The response from the bank.
         */
        date_default_timezone_set('Europe/Istanbul');
        $dateTime = (new DateTime())->format(DateTime::ISO8601);
        $xmlResponse = '<HeartBeatResponse>
            <TimeStamp>'.$dateTime.'</TimeStamp>
        </HeartBeatResponse>';

        /**
         * Prepare container for the requests to be made.
         */
        $retrievedRequests = [];
        $history = Middleware::history($retrievedRequests);

        /**
         * Heartbeat request returns the timestamp.
         * Other requests use the message inbox system.
         */
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], $xmlResponse),
        ]));
        $handler->push($history);

        $client = new Client([
            'handler' => $handler,
        ]);

        $request = new HeartbeatGetRequest($client, $conf);
        $response = $request->sendRequest();

        /**
         * Only 1 request made.
         */
        $this->assertCount(1, $retrievedRequests);

        $req1 = $retrievedRequests[0]['request'];

        /**
         * Make sure the request was correct.
         */
        $this->assertEquals('GET', $req1->getMethod());
        $this->assertEquals('heartbeat', $req1->getRequestTarget());
        $this->assertEquals($xmlResponse, $response->getBody()->getContents());
    }

    /**
     * @test
     */
    public function it_test_a_failed_heartbeat_request_response()
    {
        $conf = [
            'IBAN' => '',
            'url'  => 'https://connect.lhv.eu',
            'cert' => ['path' => __DIR__.'/test_cert.p12', 'password' => 'password'],
        ];

        $retrievedRequests = [];
        $history = Middleware::history($retrievedRequests);

        /**
         * 1 request, heartbeat doesn't use inbox system.
         */
        $handler = HandlerStack::create(new MockHandler([
            new Response(503),
        ]));
        $handler->push($history);

        $client = new Client([
            'handler' => $handler,
        ]);

        $request = new HeartbeatGetRequest($client, $conf);

        $this->expectException(Exception::class, '', 503);

        $request->sendRequest();
    }

    /**
     * @test
     */
    public function it_test_a_correct_payment_initiation_request()
    {
        $conf = [
            'IBAN'      => 'EE1955501215926523',
            'name'      => 'Hendrik Ilves Toomas',
            'url'       => 'https://connect.lhv.eu',
            'cert'      => ['path' => __DIR__.'/test_cert.p12', 'password' => 'password'],
            'bic'       => 'LHVBEE22',
            'initiator' => 'TestUser',
        ];

        $payments = [
            [
                'id'            => 1,
                'currency'      => 'EUR',
                'sum'           => rand(1, 250),
                'name'          => Str::random(),
                'IBAN'          => Str::random(),
                'description'   => Str::random(),
                'ref_nr'        => Str::random(),
            ],
            [
                'id'            => 2,
                'currency'      => 'EUR',
                'sum'           => rand(1, 250),
                'name'          => Str::random(),
                'IBAN'          => Str::random(),
                'description'   => Str::random(),
                'ref_nr'        => Str::random(),
            ],
        ];

        $lhv = new LhvConnect($conf);

        $xml = $lhv->getPaymentInitiationXML(['payments' => $payments, 'initiator' => $conf['initiator']]);

        $sum = array_sum(Arr::pluck($payments, 'sum'));

        $correctXml = $this->getPaymentInitiationRequestXML($conf, $payments, $sum);

        $xml = new \SimpleXMLElement($xml);
        $correctXml = new \SimpleXMLElement($correctXml);

        $this->assertEquals($correctXml->CstmrCdtTrfInitn->GrpHdr->NbOfTxs, $xml->CstmrCdtTrfInitn->GrpHdr->NbOfTxs);
        $this->assertEquals($correctXml->CstmrCdtTrfInitn->GrpHdr->CtrlSum, $xml->CstmrCdtTrfInitn->GrpHdr->CtrlSum);
        $this->assertEquals($correctXml->CstmrCdtTrfInitn->GrpHdr->InitgPty, $xml->CstmrCdtTrfInitn->GrpHdr->InitgPty);
        $this->assertEquals($correctXml->CstmrCdtTrfInitn->GrpHdr->InitgPty, $xml->CstmrCdtTrfInitn->GrpHdr->InitgPty);

        for ($i = 0; $i < 2; $i++) {
            $this->assertEquals($correctXml->CstmrCdtTrfInitn->PmtInf[$i]->PmtInfId, $xml->CstmrCdtTrfInitn->PmtInf[$i]->PmtInfId);
            $this->assertEquals($correctXml->CstmrCdtTrfInitn->PmtInf[$i]->ReqdExctdnDt, $xml->CstmrCdtTrfInitn->PmtInf[$i]->ReqdExctdnDt);
            $this->assertEquals($correctXml->CstmrCdtTrfInitn->PmtInf[$i]->Dbtr->Nm, $xml->CstmrCdtTrfInitn->PmtInf[$i]->Dbtr->Nm);
            $this->assertEquals($correctXml->CstmrCdtTrfInitn->PmtInf[$i]->DbtrAgt->FinInstnId->BIC, $xml->CstmrCdtTrfInitn->PmtInf[$i]->DbtrAgt->FinInstnId->BIC);
            $this->assertEquals($correctXml->CstmrCdtTrfInitn->PmtInf[$i]->CdtTrfTxInf->Ctdr->Nm, $xml->CstmrCdtTrfInitn->PmtInf[$i]->CdtTrfTxInf->Ctdr->Nm);
            $this->assertEquals($correctXml->CstmrCdtTrfInitn->PmtInf[$i]->CdtTrfTxInf->Amt->InstdAmt, $xml->CstmrCdtTrfInitn->PmtInf[$i]->CdtTrfTxInf->Amt->InstdAmt);
            $this->assertEquals($correctXml->CstmrCdtTrfInitn->PmtInf[$i]->CdtTrfTxInf->Amt->InstdAmt['Ccy'], $xml->CstmrCdtTrfInitn->PmtInf[$i]->CdtTrfTxInf->Amt->InstdAmt['Ccy']);
            $this->assertEquals($correctXml->CstmrCdtTrfInitn->PmtInf[$i]->CdtTrfTxInf->CdtrAcct->Id->IBAN, $xml->CstmrCdtTrfInitn->PmtInf[$i]->CdtTrfTxInf->CdtrAcct->Id->IBAN);
            $this->assertEquals($correctXml->CstmrCdtTrfInitn->PmtInf[$i]->CdtTrfTxInf->RmtInf->Ustrd, $xml->CstmrCdtTrfInitn->PmtInf[$i]->CdtTrfTxInf->RmtInf->Ustrd);
        }
    }

    private function getPaymentInitiationRequestXML($conf, $payments, $sum)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.001.001.03" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:iso:std:iso:20022:tech:xsd:pain.001.001.03
pain.001.001.03.xsd">
            <CstmrCdtTrfInitn>
                <GrpHdr>
                    <MsgId>TestID</MsgId>
                    <CreDtTm>'.(new DateTime())->format(DateTime::ATOM).'</CreDtTm>
                    <NbOfTxs>'.count($payments).'</NbOfTxs>
                    <CtrlSum>'.$sum.'</CtrlSum>
                    <InitgPty>
                        <Nm>'.$conf['initiator'].'</Nm>
                    </InitgPty>
                </GrpHdr>
        ';

        foreach ($payments as $p) {
            $xml .= '<PmtInf>
                <PmtInfId>'.$p['id'].'</PmtInfId>
                <PmtMtd>TRF</PmtMtd>
                <BtchBookg>false</BtchBookg>
                <NbOfTxs>1</NbOfTxs>
                <ReqdExctnDt>'.(new DateTime())->format('Y-m-d').'</ReqdExctnDt>
                <Dbtr>
                    <Nm>'.$conf['name'].'</Nm>
                </Dbtr>
                <DbtrAcct>
                    <Id>
                        <IBAN>'.$conf['IBAN'].'</IBAN>
                    </Id>
                    <Ccy>EUR</Ccy>
                </DbtrAcct>
                <DbtrAgt>
                    <FinInstnId>
                        <BIC>LHVBEE22</BIC>
                    </FinInstnId>
                </DbtrAgt>
                <ChrgBr>DEBT</ChrgBr>
                <CdtTrfTxInf>
                    <PmtId>
                        <EndToEndId/>
                    </PmtId>
                    <PmtTpInf>
                        <LclInstrm>
                            <Prtry>NORM</Prtry>
                        </LclInstrm>
                    </PmtTpInf>
                    <Amt>
                        <InstdAmt Ccy="EUR">'.$p['sum'].'</InstdAmt>
                    </Amt>
                    <ChrgBr>DEBT</ChrgBr>
                    <Cdtr>
                        <Nm>'.$p['name'].'</Nm>
                    </Cdtr>
                    <CdtrAcct>
                        <Id>
                            <IBAN>'.$p['IBAN'].'</IBAN>
                        </Id>
                    </CdtrAcct>
                    <RmtInf>
                        <Ustrd>'.$p['description'].'</Ustrd>
                        <Strd>
                            <CdtrRefInf>
                                <Ref>'.$p['ref_nr'].'</Ref>
                            </CdtrRefInf>
                        </Strd>
                    </RmtInf>
                </CdtTrfTxInf>
            </PmtInf>';
        }

        $xml .= '
        </CstmrCdtTrfInitn>
        </Document>';

        return $xml;
    }
}
