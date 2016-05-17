<?php

namespace LhvConnect\Exceptions;

use Exception;

class RequestDataInvalidException extends Exception {

    protected $code = 400;
    protected $message = "Not enough data provider for request";

}