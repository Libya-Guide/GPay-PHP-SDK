<?php
namespace GPay\ResponseModels;

use DateTime;

class OutstandingTransactions {
    public $outstandingCredit;
    public $outstandingDebit;
    public $responseTime;
    public $outstandingTransactions;
    public function __construct($outstandingCredit, $outstandingDebit, $responseTimestamp, $outstandingTransactions) {
        $this->outstandingCredit = $outstandingCredit;
        $this->outstandingDebit = $outstandingDebit;
        $this->responseTime = DateTime::createFromFormat('U.u', sprintf('%.3f', $responseTimestamp / 1000));
        $this->outstandingTransactions = $outstandingTransactions;
    }
}
