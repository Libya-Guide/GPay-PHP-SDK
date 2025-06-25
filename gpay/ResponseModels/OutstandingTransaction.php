<?php
namespace GPay\ResponseModels;

class OutstandingTransaction {
    public $transactionId;
    public $datetime;
    public $timestamp;
    public $description;
    public $amount;
    public $balance;
    public $referenceNo;
    public $opTypeId;
    public $status;
    public $createdAt;
    public function __construct($transactionId, $datetime, $timestamp, $description, $amount, $balance, $referenceNo, $opTypeId, $status, $createdAt) {
        $this->transactionId = $transactionId;
        $this->datetime = $datetime;
        $this->timestamp = $timestamp;
        $this->description = $description;
        $this->amount = $amount;
        $this->balance = $balance;
        $this->referenceNo = $referenceNo;
        $this->opTypeId = $opTypeId;
        $this->status = $status;
        $this->createdAt = $createdAt;
    }
}
