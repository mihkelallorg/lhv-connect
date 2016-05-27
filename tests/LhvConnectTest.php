<?php

namespace Mihkullorg\LhvConnect\Tests;

use DateInterval;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Mihkullorg\LhvConnect\LhvConnect;
use Mihkullorg\LhvConnect\Requests\HeartbeatGetRequest;
use PHPUnit_Framework_TestCase;

class LhvConnectTest extends PHPUnit_Framework_TestCase {

    
    /**
     * @test
     */
    public function it_test_a_correct_heartbeat_request_data()
    {
        $conf = [
            'IBAN' => '',
            'url' => 'https://connect.lhv.eu',
            'cert' => [ 'path' => __DIR__ . '/test_cert.p12', 'password' => 'password'],
        ];

        /**
         * The response from the bank
         */
        $dateTime = (new DateTime())->format(DateTime::ISO8601);
        $xmlResponse = "<HeartBeatResponse>
            <TimeStamp>" . $dateTime . "</TimeStamp>
        </HeartBeatResponse>";


        /**
         * Prepare container for the requests to be made
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
         * Only 1 request made
         */
        $this->assertCount(1, $retrievedRequests);

        $req1 = $retrievedRequests[0]['request'];

        /**
         * Make sure the request was correct
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
            'url' => 'https://connect.lhv.eu',
            'cert' => [ 'path' => __DIR__ . '/test_cert.p12', 'password' => 'password'],
        ];

        $retrievedRequests = [];
        $history = Middleware::history($retrievedRequests);

        /**
         * 1 request, heartbeat doesn't use inbox system
         */
        $handler = HandlerStack::create(new MockHandler([
            new Response(503),
        ]));
        $handler->push($history);

        $client = new Client([
            'handler' => $handler,
        ]);

        $request = new HeartbeatGetRequest($client, $conf);

        $this->setExpectedException(Exception::class, "", 503);

        $request->sendRequest();
    }

    /**
     * @test
     */
    public function it_test_a_correct_account_statement_request()
    {
        $conf = [
            'IBAN' => '1234567890',
            'url' => 'https://connect.lhv.eu',
            'cert' => [ 'path' => __DIR__ . '/test_cert.p12', 'password' => 'password'],
        ];

        /**
         * Prepare container for the requests to be made
         */
        $retrievedRequests = [];
        $history = Middleware::history($retrievedRequests);

        $messageRID = str_random();

        /**
         * Prepare the response from the server
         */
        $dateTime = (new DateTime())->format(DateTime::ISO8601);
        $xmlResponse = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?> 
            <Document xmlns=\"urn:iso:std:iso:20022:tech:xsd:camt.054.001.02\">
                <BkToCstmrStmt>
                    " . $dateTime . "
                </BkToCstmrStmt>
            </Document>";

        /**
         * Prepare 4 responses for 4 requests.
         */
        $handler = HandlerStack::create(new MockHandler([
            new Response(202), //MerchantPaymentRequest
        ]));
        $handler->push($history);

        /**
         * Create LhvConnect with custom Client, which catches the requests
         */
        $client = new Client([
            'handler' => $handler,
        ]);

        $lhv = new LhvConnect($conf);

        $lhv->setClient($client);

        $lhv->makeAccountStatementRequest();

        /**
         * Total of 4 request must have been made
         * And 1 message should be retrieved from the inbox
         */
        $this->assertCount(1, $retrievedRequests);

        /**
         * Check all the request were correct
         */
        $this->assertEquals('POST', $retrievedRequests[0]['request']->getMethod());

        $this->assertEquals('account-statement', $retrievedRequests[0]['request']->getRequestTarget());

        /**
         * Response with the same structure will be sent from the server
         */
        $expectedXml = "<?xml version=\"1.0\"?>
        <Document xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"urn:iso:std:iso:20022:tech:xsd:camt.060.001.03\">
            <AcctRptgReq>
                <GrpHdr>
                    <MsgId></MsgId>
                    <CrdDtTm>" . (new DateTime())->format(DateTime::ISO8601) . "</CrdDtTm>
                </GrpHdr>
                <RptgReq>
                    <ReqdMsgNmId>camt.060.001.03</ReqdMsgNmId>
                    <Acct>
                        <Id>
                            <IBAN>" . $conf['IBAN'] . "</IBAN>
                        </Id>
                    </Acct>
                    <AcctOwnr>
                        <Pty></Pty>
                    </AcctOwnr>
                    <RptgPrd>
                        <FrToDt>
                            <FrDt>" . (new DateTime)->sub(new DateInterval('P1M'))->format('Y-m-d') . "</FrDt>
                            <ToDt>" . (new DateTime())->format('Y-m-d') . "</ToDt>
                        </FrToDt>
                    </RptgPrd>
                </RptgReq>
            </AcctRptgReq>
        </Document>";
        $expectedXmlObject = new \SimpleXMLElement($expectedXml);

        /**
         * As MsgId is generated in the Request class, we have no way to know it before the request is created
         * So we take it from the response and add it to prepared xml and then assert that the xmls are equal
         */
        $retrievedXml = $retrievedRequests[0]['request']->getBody()->getContents();
        $retrievedXmlObject = new \SimpleXMLElement($retrievedXml);
        
        $msgId = $retrievedXmlObject->AcctRptgReq->GrpHdr->MsgId;
        $expectedXmlObject->AcctRptgReq->GrpHdr->addChild('MsgId', $msgId);

        $this->assertEquals($retrievedXmlObject->asXml(), $retrievedXmlObject->asXml());
    }

    /**
     * @test
     */
    public function it_test_a_correct_merchant_payment_request()
    {
        $conf = [
            'IBAN' => '',
            'url' => 'https://connect.lhv.eu',
            'cert' => [ 'path' => __DIR__ . '/test_cert.p12', 'password' => 'password'],
        ];

        $retrievedRequests = [];
        $history = Middleware::history($retrievedRequests);

        $messageRID = str_random();

        $dateTime = (new DateTime())->format(DateTime::ISO8601);
        $xmlResponse = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?> 
            <Document xmlns=\"urn:iso:std:iso:20022:tech:xsd:camt.054.001.02\">
                <BkToCstmrDbtCdtNtfctn>
                " . $dateTime . "
                </BkToCstmrDbtCdtNtfctn>
            </Document>";

        $handler = HandlerStack::create(new MockHandler([
            new Response(202), //MerchantPaymentRequest
        ]));
        $handler->push($history);

        $client = new Client([
            'handler' => $handler,
        ]);

        $lhv = new LhvConnect($conf);

        $lhv->setClient($client);

        $lhv->makeMerchantPaymentReportRequest();

        $this->assertCount(1, $retrievedRequests);

        $this->assertEquals('POST', $retrievedRequests[0]['request']->getMethod());

        $this->assertEquals('merchant-report', $retrievedRequests[0]['request']->getRequestTarget());


        $expectedXml = "<?xml version=\"1.0\"?>
        <MerchantReportRequest><Type>CAMT_SETTLEMENT</Type><PeriodStart>" .
            (new DateTime())->sub(new DateInterval('P1M'))->format('Y-m-d')
            . "</PeriodStart><PeriodEnd>" .
            (new DateTime())->format('Y-m-d')
            . "</PeriodEnd></MerchantReportRequest>";

        $retrievedXml = $retrievedRequests[0]['request']->getBody()->getContents();

        $this->assertEquals(preg_replace('/\s+/', '', $expectedXml), preg_replace('/\s+/', '', $retrievedXml));
    }

}