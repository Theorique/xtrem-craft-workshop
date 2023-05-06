<?php

namespace MoneyProblem\Domain;

class InvalidExchangeCurrencyException extends \Exception
{
    public function __construct($isSameAsPivot)
    {
        $message = "";
        if($isSameAsPivot) {
            $message = "Can't add an exchange rate with the pivot currency";
        } else {
            $message = "Can't add an exchange rate with invalid currency";
        }
        parent::__construct($message);
    }
}