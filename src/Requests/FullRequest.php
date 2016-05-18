<?php

namespace LhvConnect\Request;

use DateTime;
use GuzzleHttp\Client;
use LhvConnect\Exceptions\RequestDataInvalidException;
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


    public function __construct(Client $client, array $data)
    {
        $this->data = $data;
        $this->msgId = \LhvConnect\generateMessageIdentification();

        parent::__construct($client);
    }

    public function sendRequest()
    {
        $this->prepareFields();
        $this->validate();
        $xml = $this->createXML();

        return parent::sendRequest();
    }

    protected function createXML()
    {
        $xml = new SimpleXMLElement($this->xmlTag);
        $this->array_to_xml($this->xml, $xml);
        $this->XMLfile = XML_ROOT . (new DateTime)->getTimestamp() . rand(10000, 100000) . '.xml';

        return $xml->asXML();
    }

    /**
     * Checks if the data the user entered, matches the rules
     * If a field matches the rules, it's saved to $fields
     *
     * @throws RequestDataInvalidException
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
                            throw new RequestDataInvalidException("FIELD " . $field . " MISSING");
                        }
                        break;
                    case "date":
                        if (DateTime::createFromFormat('Y-m-d', $this->data[$field]) == false)
                        {
                            throw new RequestDataInvalidException("FIELD " . $field . " NOT IN Y-m-d FORMAT");
                        }
                    case "in":
                        $acceptables = explode(',', explode(':', $rule)[1]);
                        if ( ! in_array($field, $acceptables))
                        {
                            throw new RequestDataInvalidException(
                                "FIELD " . $field . " CAN BE ONLY ONE OF (" . implode(", ", $acceptables) . ")"
                            );
                        }
                }
            }
            $this->fields[$field] = $this->data[$field];
        }
    }

    protected function array_to_xml($data, SimpleXMLElement &$xml_data)
    {
        foreach( $data as $key => $value ) {
            if( is_array($value) ) {
                $subnode = $xml_data->addChild(constant("Tags::$key"));
                self::array_to_xml($value, $subnode);
            } else {
                if($value == "")
                {
                    $xml_data->addChild(constant("Tags::$key"), htmlspecialchars($this->fields[$key]));
                }else 
                {
                    $xml_data->addChild(constant("Tags::$key"), $value);
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