<?php

use Mihkullorg\LhvConnect\ResponseCode;

class ForbiddenException extends Exception{

    protected $code = ResponseCode::FORBIDDEN;

    protected $message = "
        Request could not be fulfilled due to authorization or authentication failure.
        Requested service is not stated in Connect agreement, Connect agreement is not
        valid or other failure.";
}