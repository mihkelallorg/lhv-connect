<?php

namespace Mihkullorg\LhvConnect;

use GuzzleHttp\Client;
use Mihkullorg\LhvConnect\Requests\AccountStatementRequest;
use Mihkullorg\LhvConnect\Requests\DeleteMessageInInbox;
use Mihkullorg\LhvConnect\Requests\HeartbeatGetRequest;
use Mihkullorg\LhvConnect\Requests\MerchantPaymentReportRequest;
use Mihkullorg\LhvConnect\Requests\RetrieveMessageFromInbox;

class LhvConnect {

    private $client;
    private $configuration;

    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
        $this->client = new Client([
            'base_uri' => $this->configuration['url'],
        ]);
    }

    /**
     * Test request. Tests the connection to the server
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function makeHeartbeatGetRequest()
    {
        $request = new HeartbeatGetRequest($this->client, $this->configuration);
        
        return $request->sendRequest();
    }

    public function makeHeartbeatPostRequest()
    {
        //TODO
    }

    /**
     * Make a Merchant Payment Report request.
     * Response will be added to the "inbox"
     *
     * @param array $data
     * @return array
     */
    public function makeMerchantPaymentReportRequest(array $data = [])
    {
        $request = new MerchantPaymentReportRequest($this->client, $this->configuration, $data);
        $request->sendRequest();

        return $this->getAllMessages();
    }

    /**
     * Make an Account Statement request.
     * Response will be added to the "inbox"
     *
     * @param array $data
     * @return array All the messages
     */
    public function makeAccountStatementRequest(array $data = [])
    {
        $request = new AccountStatementRequest($this->client, $this->configuration, $data);
        $request->sendRequest();

        return $this->getAllMessages();
    }

    /**
     * Retrieve all the messages from the inbox
     * Deletes all the retrieved messages from the inbox
     *
     * @return array
     */
    public function getAllMessages()
    {
        $messages = [];

        while(true)
        {
            $message = $this->makeRetrieveMessageFromInboxRequest();

            if ( !isset($message->getHeaders()['Content-Length']) || $message->getHeader('Content-Length')[0] == 0)
            {
                break;
            }

            $this->makeDeleteMessageInInboxRequest($message->getHeader('Message-Response-Id')[0]);

            array_push($messages, $message);
        }

        return $messages;
    }


    private function makeRetrieveMessageFromInboxRequest()
    {
        $request = new RetrieveMessageFromInbox($this->client, $this->configuration);

        return $request->sendRequest();
    }

    public function setClient(Client $client)
    {
        $this->client = $client;    
    }


    private function makeDeleteMessageInInboxRequest($id)
    {
        $request = new DeleteMessageInInbox($this->client, $this->configuration, null, [], $id);

        return $request->sendRequest();
    }
}