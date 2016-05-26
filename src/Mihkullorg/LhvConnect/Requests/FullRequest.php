<?php

namespace Mihkullorg\LhvConnect\Requests;

use DateTime;
use Exception;
use GuzzleHttp\Client;
use SimpleXMLElement;

abstract class FullRequest extends BasicRequest {

    protected $data;
    protected $client;

    protected $msgId;
    protected $xmlTag;
    protected $xmlFormat;
    protected $fields;
    
    protected $rules;
    
    protected $xml;


    public function __construct(Client $client, $configuration, array $data, $body = null, $headers = [])
    {
        parent::__construct($client, $configuration, $body, $headers);

        $this->data = $data;
        $this->msgId = str_random(30);

    }

    public function sendRequest()
    {
        $this->prepareFields();
        $this->validate();
        $this->body = $this->createXML();

        return parent::sendRequest();
    }

    protected function createXML()
    {
        $xml = new SimpleXMLElement("<$this->xmlTag></$this->xmlTag>");
        $this->array_to_xml($this->xml, $xml);

        return $xml->asXML();
    }

    /**
     * Checks if the data the user entered, matches the rules
     * If a field matches the rules, it's saved to $fields
     *
     * @throws Exception
     */
    protected function validate()
    {
        foreach ($this->rules as $field => $rules){
            foreach (explode('|', $rules) as $rule)
            {
                switch (explode(':',$rule)[0])
                {
                    case "required":
                        if ( ! isset($this->data[$field]))
                        {
                            throw new Exception("FIELD " . $field . " MISSING", 400);
                        }
                        break;
                    case "date":
                        if (isset($this->data[$field]) and DateTime::createFromFormat('Y-m-d', $this->data[$field]) == false)
                        {
                            throw new Exception("FIELD " . $field . " NOT IN Y-m-d FORMAT", 400);
                        }
                        break;
                    case "in":
                        if (isset($this->data[$field])){
                            $acceptables = explode(',', explode(':', $rule)[1]);
                            if ( ! in_array($this->data[$field], $acceptables))
                            {
                                throw new Exception(
                                    "FIELD " . $field . " CAN BE ONLY ONE OF (" . implode(", ", $acceptables) . ")",
                                    400
                                );
                            }
                        }
                        break;
                }
            }
            if (isset($this->data[$field]))
            {
                $this->fields[$field] = $this->data[$field];
            }
        }
    }

    protected function array_to_xml($data, SimpleXMLElement &$xml_data)
    {
        foreach( $data as $key => $value ) {
            if( is_array($value) ) {
                $subnode = $xml_data->addChild(constant("Mihkullorg\\LhvConnect\\Tag::$key"));
                self::array_to_xml($value, $subnode);
            } else {
                if($value == "")
                {
                    $xml_data->addChild(constant("Mihkullorg\\LhvConnect\\Tag::$key"), htmlspecialchars($this->fields[$key]));
                }else 
                {
                    $xml_data->addChild(constant("Mihkullorg\\LhvConnect\\Tag::$key"), $value);
                }
            }
        }
    }

    /**
     * Set the (default) values for fields
     * Some might be overwritten by input data
     */
    protected abstract function prepareFields();

    protected abstract function prepareXmlArray();
    
}