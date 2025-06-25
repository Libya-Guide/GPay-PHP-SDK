<?php
namespace GPay\ResponseModels;

use DateTime;

class WalletCheck {
    public $exists;
    public $walletGatewayId;
    public $walletName;
    public $userAccountName;
    public $canReceiveMoney;
    public $responseTime;
    public function __construct($exists, $walletGatewayId, $walletName, $userAccountName, $canReceiveMoney, $responseTimestamp) {
        $this->exists = $exists;
        $this->walletGatewayId = $walletGatewayId;
        $this->walletName = $walletName;
        $this->userAccountName = $userAccountName;
        $this->canReceiveMoney = $canReceiveMoney;
        $this->responseTime = DateTime::createFromFormat('U.u', sprintf('%.3f', $responseTimestamp / 1000));
    }
}
