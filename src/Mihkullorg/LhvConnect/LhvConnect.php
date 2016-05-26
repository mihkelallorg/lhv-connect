<?php

namespace Mihkullorg\LhvConnect;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Mihkullorg\LhvConnect\Requests\AccountStatementRequest;
use Mihkullorg\LhvConnect\Requests\DeleteMessageInInbox;
use Mihkullorg\LhvConnect\Requests\HeartbeatGetRequest;
use Mihkullorg\LhvConnect\Requests\MerchantPaymentReportRequest;
use Mihkullorg\LhvConnect\Requests\RetrieveMessageFromInbox;
use SimpleXMLElement;

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

    public function makeHeartbeatGetRequest()
    {
        $request = new HeartbeatGetRequest($this->client, $this->configuration);
        
        return $request->sendRequest();
    }

    public function makeHeartbeatPostRequest()
    {
        //TODO
    }
    
    public function makeMerchantPaymentReportRequest(array $data = [])
    {
        $request = new MerchantPaymentReportRequest($this->client, $this->configuration, $data);
        $request->sendRequest();

        $this->getAndResolveAllMessages();
    }

    public function makeAccountStatementRequest(array $data = [])
    {
        $request = new AccountStatementRequest($this->client, $this->configuration, $data);
        $request->sendRequest();

        $this->getAndResolveAllMessages();
    }

    private function getAndResolveAllMessages()
    {
        while(true)
        {
            $message = $this->makeRetrieveMessageFromInboxRequest();

            if ( !isset($message->getHeaders()['Content-Length']) || $message->getHeader('Content-Length')[0] == 0)
            {
                break;
            }
            $this->handleMessage($message);
            $this->makeDeleteMessageInInboxRequest($message->getHeader('Message-Response-Id')[0]);
        }
    }

    private function makeRetrieveMessageFromInboxRequest()
    {
        $request = new RetrieveMessageFromInbox($this->client, $this->configuration);

        return $request->sendRequest();
    }

    private function handleMessage($message)
    {
        $xml = new SimpleXMLElement($message->getBody()->getContents());
        $functions = $this->configuration['responseHandlers'];

        if (isset($xml->BkToCstmrDbtCdtNtfctn))
        {
            call_user_func($functions['MerchantPaymentReport'], $message);
        }else if (isset($xml->BkToCstmrStmt))
        {
            call_user_func($functions['AccountStatement'], $message);
        }else{
            Log::warning("Weird XML response: \n" . $xml->asXML());
        }
    }

    private function makeDeleteMessageInInboxRequest($id)
    {
        $request = new DeleteMessageInInbox($this->client, $this->configuration, null, [], $id);

        return $request->sendRequest();
    }

    public function setClient(Client $client)
    {
        $this->client = $client;    
    }
}