<?php
namespace GPay\ResponseModels;

use DateTime;

class Statement {
    public $availableBalance;
    public $outstandingCredit;
    public $outstandingDebit;
    public $dayBalance;
    public $dayTotalIn;
    public $dayTotalOut;
    public $responseTime;
    public $dayStatement;
    public function __construct($availableBalance, $outstandingCredit, $outstandingDebit, $dayBalance, $dayTotalIn, $dayTotalOut, $responseTimestamp, $dayStatement) {
        $this->availableBalance = $availableBalance;
        $this->outstandingCredit = $outstandingCredit;
        $this->outstandingDebit = $outstandingDebit;
        $this->dayBalance = $dayBalance;
        $this->dayTotalIn = $dayTotalIn;
        $this->dayTotalOut = $dayTotalOut;
        $this->responseTime = DateTime::createFromFormat('U.u', sprintf('%.3f', $responseTimestamp / 1000));
        $this->dayStatement = $dayStatement;
    }
}
