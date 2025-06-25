# GPay API PHP Client Library

A PHP client for the GPay Payment API. Handles authentication, request signing, response verification, and endpoint logic.

## Official GPay API Documentation

For full API details, see the [GPay API Documentation](https://gpay.ly/banking/doc/index.html).

## Installation

Install via Composer:

```
composer require libyaguide/gpay-api-client
```

## Usage

### Import and Create the Client

```php
require 'vendor/autoload.php';
use GPay\GPayApiClient;
use GPay\BaseUrl;

$client = new GPayApiClient(
    'your_api_key',
    'your_secret_key',
    'your_password',
    BaseUrl::STAGING // or BaseUrl::PRODUCTION
);
```

### Get Wallet Balance

```php
// type: Balance
$balance = $client->getBalance();
print_r($balance);
```

### Create a Payment Request

```php
// type: PaymentRequest
$paymentRequest = $client->createPaymentRequest('100.00', 'INV-123', 'Invoice Payment');
print_r($paymentRequest);
```

### Check Payment Status

```php
// type: PaymentStatus
$status = $client->checkPaymentStatus($paymentRequest->requestId);
print_r($status);
```

### Send Money

```php
// type: SendMoneyResult
$sendResult = $client->sendMoney('50.00', 'recipient_wallet_id', 'Gift', 'GIFT-001');
print_r($sendResult);
```

### Get Day Statement

```php
// type: Statement
$statement = $client->getDayStatement('2025-06-22');
print_r($statement);
foreach ($statement->dayStatement as $tx) {
    // tx: StatementTransaction
    print_r($tx);
}
```

### Check Wallet

```php
// type: WalletCheck
$walletInfo = $client->checkWallet('recipient_wallet_id');
print_r($walletInfo);
```

### Get Outstanding Transactions

```php
// type: OutstandingTransactions
$outstanding = $client->getOutstandingTransactions();
print_r($outstanding);
foreach ($outstanding->outstandingTransactions as $tx) {
    // tx: OutstandingTransaction
    print_r($tx);
}
```

## License
MIT
