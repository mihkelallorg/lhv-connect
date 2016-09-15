<?php

namespace Mihkullorg\LhvConnect\Requests;

use Mihkullorg\LhvConnect\XMLGenerator;

class PaymentInitiationRequest extends FullRequest {

    protected $url = "payment";
    protected $method = "POST";

    /**
     * Return the xml as a string
     *
     * @return string
     */
    public function getXML()
    {
        return XMLGenerator::paymentInitiationXML($this->data, $this->configuration);
    }

}