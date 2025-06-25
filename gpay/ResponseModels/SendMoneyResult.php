<?php
namespace GPay\ResponseModels;

use DateTime;

class SendMoneyResult {
    public $amount;
    public $senderFee;
    public $transactionId;
    public $oldBalance;
    public $newBalance;
    public $timestamp;
    public $referenceNo;
    public $responseTime;
    public function __construct($amount, $senderFee, $transactionId, $oldBalance, $newBalance, $timestamp, $referenceNo, $responseTimestamp) {
        $this->amount = $amount;
        $this->senderFee = $senderFee;
        $this->transactionId = $transactionId;
        $this->oldBalance = $oldBalance;
        $this->newBalance = $newBalance;
        $this->timestamp = $timestamp;
        $this->referenceNo = $referenceNo;
        $this->responseTime = DateTime::createFromFormat('U.u', sprintf('%.3f', $responseTimestamp / 1000));
    }
}
