<?php

namespace Mihkullorg\LhvConnect\Exceptions;

use Exception;
use Mihkullorg\LhvConnect\ResponseCode;

class ServiceUnavailableException extends Exception {
    
    protected $code = ResponseCode::SERVICE_UNAVAILABLE;
    
    protected $message = "Technical error. Try again later.";
    
}