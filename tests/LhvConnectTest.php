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
        date_default_timezone_set('Europe/Istanbul');
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
    public function it_test_a_correct_payment_initiation_request()
    {
        $conf = [
            'IBAN'  => '',
            'name'  => '',
            'url'   => 'https://connect.lhv.eu',
            'cert'  => [ 'path' => __DIR__ . '/test_cert.p12', 'password' => 'password'],
            'bic'   => 'LHVVEE22',
        ];



    }

}