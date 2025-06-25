<?php
namespace GPay\ResponseModels;

use DateTime;

class Balance {
    public $balance;
    public $responseTime;
    public function __construct($balance, $responseTimestamp) {
        $this->balance = $balance;
        $this->responseTime = DateTime::createFromFormat('U.u', sprintf('%.3f', $responseTimestamp / 1000));
    }
}
