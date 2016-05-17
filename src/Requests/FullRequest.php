<?php

namespace LhvConnect\Request;

use DateTime;
use GuzzleHttp\Client;
use LhvConnect\Exceptions\RequestDataInvalidException;
use SimpleXMLElement;

abstract class FullRequest extends BasicRequest{

    protected $xmlTag;
    protected $xmlFile;
    protected $data;
    protected $fields;
    protected $client;
    protected $xmlFormat;
    
    protected $xml;


    public function __construct(Client $client, array $data)
    {
        $this->data = $data;

        parent::__construct($client);
    }

    protected function createXML()
    {
        $xml = new SimpleXMLElement($this->xmlTag);
        $this->array_to_xml($this->xml, $xml);
        $this->XMLfile = XML_ROOT . (new DateTime)->getTimestamp() . rand(10000, 100000) . '.xml';

        return $xml->asXML($this->xmlFile);
    }

    protected function validate()
    {
        if(sort(array_keys($this->data)) == sort(array_values($this->fields))){
            return true;
        } else
        {
            throw new RequestDataInvalidException("This request requires the following fields: ". implode(', ', $this->fields));
        }
    }

    protected function array_to_xml($data, SimpleXMLElement &$xml_data)
    {
        foreach( $data as $key => $value ) {
            if( is_array($value) ) {
                $subnode = $xml_data->addChild(constant("Tags::$key"));
                self::array_to_xml($value, $subnode);
            } else {
                $xml_data->addChild(constant("Tags::$key"), htmlspecialchars($this->fields[$key]));
            }
        }
    }
}