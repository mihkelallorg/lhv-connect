<?php

namespace LhvConnect\Exceptions;

use Exception;
use LhvConnect\ResponseCode;

class ServiceUnavailableException extends Exception {
    
    protected $code = ResponseCode::SERVICE_UNAVAILABLE;
    
    protected $message = "Technical error. Try again later.";
    
}