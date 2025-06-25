<?php
namespace GPay\ResponseModels;

use DateTime;

class PaymentStatus {
    public $requestId;
    public $transactionId;
    public $amount;
    public $paymentTimestamp;
    public $referenceNo;
    public $description;
    public $isPaid;
    public $responseTime;
    public function __construct($requestId, $transactionId, $amount, $paymentTimestamp, $referenceNo, $description, $isPaid, $responseTimestamp) {
        $this->requestId = $requestId;
        $this->transactionId = $transactionId;
        $this->amount = $amount;
        $this->paymentTimestamp = $paymentTimestamp;
        $this->referenceNo = $referenceNo;
        $this->description = $description;
        $this->isPaid = $isPaid;
        $this->responseTime = DateTime::createFromFormat('U.u', sprintf('%.3f', $responseTimestamp / 1000));
    }
}
