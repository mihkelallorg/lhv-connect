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
                        'responseHandlers' => [
                'MerchantPaymentReport' => "Mihkullorg\\LhvConnect\\Tests\\TestHelpers::merchantReportFunction",
                'AccountStatement' => "Mihkullorg\\LhvConnect\\Tests\\TestHelpers::accountStatementFunction",
            ],
        ];

        $dateTime = (new DateTime())->format(DateTime::ISO8601);
        $xmlResponse = "<HeartBeatResponse>
            <TimeStamp>" . $dateTime . "</TimeStamp>
        </HeartBeatResponse>";


        /**
         * Get the retrieved requests
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

        $this->assertCount(1, $retrievedRequests);

        $req1 = $retrievedRequests[0]['request'];

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
                        'responseHandlers' => [
                'MerchantPaymentReport' => "Mihkullorg\\LhvConnect\\Tests\\TestHelpers::merchantReportFunction",
                'AccountStatement' => "Mihkullorg\\LhvConnect\\Tests\\TestHelpers::accountStatementFunction",
            ],
        ];

        $retrievedRequests = [];
        $history = Middleware::history($retrievedRequests);

        /**
         * Two responses to the two requests
         * 1st is heartbeat request, second is to get the message
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
     * @group testing
     */
    public function it_test_a_correct_account_statement_request()
    {

        $conf = [
            'IBAN' => '1234567890',
            'url' => 'https://connect.lhv.eu',
            'cert' => [ 'path' => __DIR__ . '/test_cert.p12', 'password' => 'password'],
                        'responseHandlers' => [
                'MerchantPaymentReport' => "Mihkullorg\\LhvConnect\\Tests\\TestHelpers::merchantReportFunction",
                'AccountStatement' => "Mihkullorg\\LhvConnect\\Tests\\TestHelpers::accountStatementFunction",
            ],
        ];

        /**
         * Get the retrieved requests
         */

        $retrievedRequests = [];
        $history = Middleware::history($retrievedRequests);

        $messageRID = str_random();

        $dateTime = (new DateTime())->format(DateTime::ISO8601);

        $xmlResponse = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?> 
            <Document xmlns=\"urn:iso:std:iso:20022:tech:xsd:camt.054.001.02\">
                <BkToCstmrStmt>
                    " . $dateTime . "
                </BkToCstmrStmt>
            </Document>";

        $handler = HandlerStack::create(new MockHandler([
            new Response(202), //MerchantPaymentRequest
            new Response(200, ['Message-Response-Id' => $messageRID, 'Content-Length' => 5], $xmlResponse), //InboxRequest
            new Response(200), //Deleterequest
            new Response(200, ['Content-Length' => 0], ""), //InboxRequest, no more messages
        ]));
        $handler->push($history);

        $client = new Client([
            'handler' => $handler,
        ]);

        $lhv = new LhvConnect($conf);

        $lhv->setClient($client);

        $lhv->makeAccountStatementRequest();

        $this->assertCount(4, $retrievedRequests);


        $this->assertEquals('POST', $retrievedRequests[0]['request']->getMethod());
        $this->assertEquals('GET', $retrievedRequests[1]['request']->getMethod());
        $this->assertEquals('DELETE', $retrievedRequests[2]['request']->getMethod());
        $this->assertEquals('GET', $retrievedRequests[3]['request']->getMethod());

        $this->assertEquals('account-statement', $retrievedRequests[0]['request']->getRequestTarget());
        $this->assertEquals('/messages/next', $retrievedRequests[1]['request']->getRequestTarget());
        $this->assertEquals('/messages/' . $messageRID, $retrievedRequests[2]['request']->getRequestTarget());
        $this->assertEquals('/messages/next', $retrievedRequests[3]['request']->getRequestTarget());

        $expectedXml = "<?xml version=\"1.0\"?>
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
        </AcctRptgReq>";
        $expectedXmlObject = new \SimpleXMLElement($expectedXml);

        $retrievedXml = $retrievedRequests[0]['request']->getBody()->getContents();
        $retrievedXmlObject = new \SimpleXMLElement($retrievedXml);
        $msgId = $retrievedXmlObject->children()->GrpHdr->MsgId;
        $expectedXmlObject->children()->GrpHdr->addChild('MsgId', $msgId);

        $this->assertEquals($retrievedXmlObject->asXml(), $retrievedXmlObject->asXml());

        $retrievedMessageText = file_get_contents("tests/account-statement.xml");

        $this->assertEquals($xmlResponse, $retrievedMessageText);
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
                        'responseHandlers' => [
                'MerchantPaymentReport' => "Mihkullorg\\LhvConnect\\Tests\\TestHelpers::merchantReportFunction",
                'AccountStatement' => "Mihkullorg\\LhvConnect\\Tests\\TestHelpers::accountStatementFunction",
            ],
        ];

        /**
         * Get the retrieved requests
         */

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
            new Response(200, ['Message-Response-Id' => $messageRID, 'Content-Length' => 5], $xmlResponse), //InboxRequest
            new Response(200), //Deleterequest
            new Response(200, ['Content-Length' => 0], ""), //InboxRequest, no more messages
        ]));
        $handler->push($history);

        $client = new Client([
            'handler' => $handler,
        ]);

        $lhv = new LhvConnect($conf);

        $lhv->setClient($client);

        $lhv->makeMerchantPaymentReportRequest();

        $this->assertCount(4, $retrievedRequests);

        $this->assertEquals('POST', $retrievedRequests[0]['request']->getMethod());
        $this->assertEquals('GET', $retrievedRequests[1]['request']->getMethod());
        $this->assertEquals('DELETE', $retrievedRequests[2]['request']->getMethod());
        $this->assertEquals('GET', $retrievedRequests[3]['request']->getMethod());

        $this->assertEquals('merchant-report', $retrievedRequests[0]['request']->getRequestTarget());
        $this->assertEquals('/messages/next', $retrievedRequests[1]['request']->getRequestTarget());
        $this->assertEquals('/messages/' . $messageRID, $retrievedRequests[2]['request']->getRequestTarget());
        $this->assertEquals('/messages/next', $retrievedRequests[3]['request']->getRequestTarget());

        $expectedXml = "<?xml version=\"1.0\"?>
        <MerchantReportRequest><Tp>CAMT_SETTLEMENT</Tp><PeriodStart>" .
            (new DateTime())->sub(new DateInterval('P1M'))->format('Y-m-d')
            . "</PeriodStart><PeriodEnd>" .
            (new DateTime())->format('Y-m-d')
            . "</PeriodEnd></MerchantReportRequest>";

        $retrievedXml = $retrievedRequests[0]['request']->getBody()->getContents();

        $this->assertEquals(preg_replace('/\s+/', '', $expectedXml), preg_replace('/\s+/', '', $retrievedXml));

        $retrievedMessageText = file_get_contents("tests/merchant-report.xml");

        $this->assertEquals($xmlResponse, $retrievedMessageText);

    }
    
}