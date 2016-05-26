<?php

namespace Mihkullorg\LhvConnect\Tests;

class TestHelpers {

    public static function merchantReportFunction($message)
    {
        file_put_contents("tests/merchant-report.xml", (string) $message->getBody());
    }

    public static function accountStatementFunction($message)
    {
        file_put_contents("tests/account-statement.xml", (string) $message->getBody());
    }

}