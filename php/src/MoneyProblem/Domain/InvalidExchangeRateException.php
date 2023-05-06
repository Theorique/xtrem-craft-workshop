<?php

namespace MoneyProblem\Domain;

class InvalidExchangeRateException extends \Exception
{
    public function __construct()
    {
        parent::__construct("The exchange rate should be greater than 0");
    }
}