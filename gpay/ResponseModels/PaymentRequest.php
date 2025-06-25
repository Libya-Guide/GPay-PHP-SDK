<?php
namespace GPay\ResponseModels;

use DateTime;

class PaymentRequest {
    public $requesterUsername;
    public $requestId;
    public $requestTime;
    public $amount;
    public $referenceNo;
    public $responseTime;
    public function __construct($requesterUsername, $requestId, $requestTime, $amount, $referenceNo, $responseTimestamp) {
        $this->requesterUsername = $requesterUsername;
        $this->requestId = $requestId;
        $this->requestTime = $requestTime;
        $this->amount = $amount;
        $this->referenceNo = $referenceNo;
        $this->responseTime = DateTime::createFromFormat('U.u', sprintf('%.3f', $responseTimestamp / 1000));
    }
}
