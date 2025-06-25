<?php
/**
 * GPay API PHP Client Library
 * Equivalent to the Python/Node.js implementation
 * Handles authentication, request signing, response verification, and endpoint logic.
 *
 * @author Your Name
 * @license MIT
 */

namespace GPay;

use Exception;
use GPay\ResponseModels\Balance;
use GPay\ResponseModels\PaymentRequest;
use GPay\ResponseModels\PaymentStatus;
use GPay\ResponseModels\SendMoneyResult;
use GPay\ResponseModels\Statement;
use GPay\ResponseModels\WalletCheck;
use GPay\ResponseModels\OutstandingTransaction;
use GPay\ResponseModels\OutstandingTransactions;
use GPay\ResponseModels\StatementTransaction;

/**
 * Class HashTokenGenerator
 * Generates salt and hash token for requests.
 */
class HashTokenGenerator {
    public static function generateSalt() {
        return base64_encode(random_bytes(32));
    }
    public static function generateHashToken($salt, $password) {
        return $salt . $password;
    }
}

/**
 * Class VerificationHashGenerator
 * Generates the verification hash for requests and responses.
 */
class VerificationHashGenerator {
    public static function generateVerificationHash($hashToken, $parameters, $secretKey) {
        ksort($parameters);
        $queryString = '';
        
        foreach ($parameters as $k => $v) {
            if ($v === null) {
                $v = '';
            } elseif ($v === false) {
                $v = 'false';
            } elseif ($v === true) {
                $v = 'true';
            } elseif ($v === 0) {
                $v = '0';
            }

            $queryString .= $k . '=' . $v . '&';
        }
        $queryString = rtrim($queryString, '&');
        $verificationString = $hashToken . $queryString;
        return base64_encode(hash_hmac('sha256', $verificationString, $secretKey, true));
    }
}

/**
 * Class GPayApiClient
 * Main client for interacting with the GPay Payment API.
 */
class GPayApiClient {
    private $apiKey;
    private $secretKey;
    private $password;
    private $baseUrl;
    private $language;

    /**
     * GPayApiClient constructor.
     * @param string $apiKey The API key for authentication.
     * @param string $secretKey The secret key for signing requests.
     * @param string $password The password for hash token generation.
     * @param string $baseUrl The base URL (BaseUrl::STAGING or BaseUrl::PRODUCTION).
     * @param string $language The language for the response (default: 'en').
     * @throws Exception If the baseUrl is invalid.
     */
    public function __construct($apiKey, $secretKey, $password, $baseUrl, $language = 'en') {
        if ($baseUrl !== BaseUrl::STAGING && $baseUrl !== BaseUrl::PRODUCTION && $baseUrl !== BaseUrl::DEV) {
            throw new Exception('Invalid baseUrl. Use BaseUrl::STAGING or BaseUrl::PRODUCTION or BaseUrl::DEV.');
        }
        $this->apiKey = $apiKey;
        $this->secretKey = $secretKey;
        $this->password = $password;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->language = $language;
    }

    /**
     * Internal method to send a signed POST request to the GPay API.
     *
     * @param string $path The endpoint path (e.g., '/info/balance').
     * @param array $parameters The request parameters.
     * @return array The response JSON and headers.
     * @throws Exception On HTTP or cURL error.
     */
    private function sendRequest($path, $parameters) {
        $salt = HashTokenGenerator::generateSalt();
        $hashToken = HashTokenGenerator::generateHashToken($salt, $this->password);
        $verificationHash = VerificationHashGenerator::generateVerificationHash($hashToken, $parameters, $this->secretKey);
        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Accept-Language: ' . $this->language,
            'X-Signature-Salt: ' . $salt,
            'X-Signature-Hash: ' . $verificationHash,
            'Content-Type: application/json',
        ];
        $url = $this->baseUrl . $path;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $response = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerStr = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode >= 400) {
            throw new Exception('HTTP error: ' . $httpCode . ' ' . $body);
        }
        $headersArr = [];
        foreach (explode("\r\n", $headerStr) as $header) {
            $parts = explode(': ', $header, 2);
            if (count($parts) == 2) {
                $headersArr[$parts[0]] = $parts[1];
            }
        }

        return [
            'data' => json_decode($body, true),
            'headers' => $headersArr
        ];
    }

    /**
     * Retrieves the current wallet balance.
     *
     * @return Balance An object containing the current available balance and response time.
     * @throws Exception If response verification fails or HTTP error occurs.
     */
    public function getBalance() {
        $path = '/info/balance';
        $parameters = [
            'request_timestamp' => (string) round(microtime(true) * 1000)
        ];
        $result = $this->sendRequest($path, $parameters);
        $data = $result['data']['data'];
        $headers = $result['headers'];
        $this->verifyResponse($headers, [
            'balance' => $data['balance'],
            'response_timestamp' => $data['response_timestamp'],
        ]);
        return new Balance($data['balance'], $data['response_timestamp']);
    }

    /**
     * Creates a payment request for a specified amount.
     *
     * @param string $amount The amount to request.
     * @param string|null $referenceNo Optional reference number.
     * @param string|null $description Optional description.
     * @return PaymentRequest An object with details of the created payment request.
     * @throws Exception If response verification fails or HTTP error occurs.
     */
    public function createPaymentRequest($amount, $referenceNo = '', $description = '') {
        $path = '/payment/create-payment-request';
        $parameters = [
            'request_timestamp' => (string) round(microtime(true) * 1000),
            'amount' => $amount,
            'reference_no' => $referenceNo,
            'description' => $description
        ];
        $result = $this->sendRequest($path, $parameters);
        $data = $result['data']['data'];
        $headers = $result['headers'];
        $this->verifyResponse($headers, [
            'requester_username' => $data['requester_username'],
            'request_id' => $data['request_id'],
            'request_time' => $data['request_time'],
            'amount' => $data['amount'],
            'reference_no' => $data['reference_no'],
            'response_timestamp' => $data['response_timestamp'],
        ]);
        return new PaymentRequest(
            $data['requester_username'],
            $data['request_id'],
            $data['request_time'],
            $data['amount'],
            $data['reference_no'],
            $data['response_timestamp']
        );
    }

    /**
     * Checks the status of a payment request by its request ID.
     *
     * @param string $requestId The payment request ID.
     * @return PaymentStatus An object with the status of the payment request.
     * @throws Exception If response verification fails or HTTP error occurs.
     */
    public function checkPaymentStatus($requestId) {
        $path = '/payment/check-payment-status';
        $parameters = [
            'request_timestamp' => (string) round(microtime(true) * 1000),
            'request_id' => $requestId
        ];
        $result = $this->sendRequest($path, $parameters);
        $data = $result['data']['data'];
        $headers = $result['headers'];
        $this->verifyResponse($headers, [
            'request_id' => $data['request_id'],
            'transaction_id' => $data['transaction_id'],
            'amount' => $data['amount'],
            'payment_timestamp' => $data['payment_timestamp'],
            'reference_no' => $data['reference_no'],
            'description' => $data['description'],
            'is_paid' => $data['is_paid'],
            'response_timestamp' => $data['response_timestamp'],
        ]);
        return new PaymentStatus(
            $data['request_id'],
            $data['transaction_id'],
            $data['amount'],
            $data['payment_timestamp'],
            $data['reference_no'],
            $data['description'],
            $data['is_paid'],
            $data['response_timestamp']
        );
    }

    /**
     * Sends money to another wallet.
     *
     * @param string $amount The amount to send.
     * @param string $walletGatewayId The recipient's wallet gateway ID.
     * @param string|null $description Optional description.
     * @param string|null $referenceNo Optional reference number.
     * @return SendMoneyResult An object with details of the transaction.
     * @throws Exception If response verification fails or HTTP error occurs.
     */
    public function sendMoney($amount, $walletGatewayId, $description = '', $referenceNo = '') {
        $path = '/payment/send-money';
        $parameters = [
            'request_timestamp' => (string) round(microtime(true) * 1000),
            'amount' => $amount,
            'wallet_gateway_id' => $walletGatewayId,
            'description' => $description,
            'reference_no' => $referenceNo
        ];
        $result = $this->sendRequest($path, $parameters);
        $data = $result['data']['data'];
        $headers = $result['headers'];
        $this->verifyResponse($headers, [
            'amount' => $data['amount'],
            'sender_fee' => $data['sender_fee'],
            'transaction_id' => $data['transaction_id'],
            'old_balance' => $data['old_balance'],
            'new_balance' => $data['new_balance'],
            'timestamp' => $data['timestamp'],
            'reference_no' => $data['reference_no'],
            'response_timestamp' => $data['response_timestamp'],
        ]);
        return new SendMoneyResult(
            $data['amount'],
            $data['sender_fee'],
            $data['transaction_id'],
            $data['old_balance'],
            $data['new_balance'],
            $data['timestamp'],
            $data['reference_no'],
            $data['response_timestamp']
        );
    }

    /**
     * Retrieves the wallet's transaction statement for a specific day.
     *
     * @param string $date The date in YYYY-MM-DD format.
     * @return Statement An object containing the day's transactions and balances.
     * @throws Exception If response verification fails or HTTP error occurs.
     */
    public function getDayStatement($date) {
        $path = '/info/statement';
        $parameters = [
            'request_timestamp' => (string) round(microtime(true) * 1000),
            'date' => $date
        ];
        $result = $this->sendRequest($path, $parameters);
        $data = $result['data']['data'];
        $headers = $result['headers'];
        $this->verifyResponse($headers, [
            'available_balance' => $data['available_balance'],
            'outstanding_credit' => $data['outstanding_credit'],
            'outstanding_debit' => $data['outstanding_debit'],
            'day_balance' => $data['day_balance'],
            'day_total_in' => $data['day_total_in'],
            'day_total_out' => $data['day_total_out'],
            'response_timestamp' => $data['response_timestamp'],
        ]);
        $dayStatement = [];
        foreach ($data['day_statement'] as $tx) {
            $dayStatement[] = new StatementTransaction(
                $tx['transaction_id'],
                $tx['datetime'],
                $tx['timestamp'],
                $tx['description'],
                $tx['amount'],
                $tx['balance'],
                $tx['reference_no'],
                $tx['op_type_id'],
                $tx['status'],
                $tx['created_at']
            );
        }
        return new Statement(
            $data['available_balance'],
            $data['outstanding_credit'],
            $data['outstanding_debit'],
            $data['day_balance'],
            $data['day_total_in'],
            $data['day_total_out'],
            $data['response_timestamp'],
            $dayStatement
        );
    }

    /**
     * Checks if a wallet exists and retrieves its details.
     *
     * @param string $walletGatewayId The wallet gateway ID to check.
     * @return WalletCheck An object with wallet details.
     * @throws Exception If response verification fails or HTTP error occurs.
     */
    public function checkWallet($walletGatewayId) {
        $path = '/info/check-wallet';
        $parameters = [
            'request_timestamp' => (string) round(microtime(true) * 1000),
            'wallet_gateway_id' => $walletGatewayId
        ];
        $result = $this->sendRequest($path, $parameters);
        $data = $result['data']['data'];
        $headers = $result['headers'];
        $this->verifyResponse($headers, [
            'exists' => $data['exists'],
            'wallet_gateway_id' => $data['wallet_gateway_id'],
            'wallet_name' => $data['wallet_name'],
            'user_account_name' => $data['user_account_name'],
            'can_receive_money' => $data['can_receive_money'],
            'response_timestamp' => $data['response_timestamp'],
        ]);
        return new WalletCheck(
            $data['exists'],
            $data['wallet_gateway_id'],
            $data['wallet_name'],
            $data['user_account_name'],
            $data['can_receive_money'],
            $data['response_timestamp']
        );
    }

    /**
     * Retrieves a list of outstanding transactions.
     *
     * @return OutstandingTransactions An object containing outstanding credits, debits, and transactions.
     * @throws Exception If response verification fails or HTTP error occurs.
     */
    public function getOutstandingTransactions() {
        $path = '/info/outstanding-transactions';
        $parameters = [
            'request_timestamp' => (string) round(microtime(true) * 1000)
        ];
        $result = $this->sendRequest($path, $parameters);
        $data = $result['data']['data'];
        $headers = $result['headers'];
        $this->verifyResponse($headers, [
            'outstanding_credit' => $data['outstanding_credit'],
            'outstanding_debit' => $data['outstanding_debit'],
            'response_timestamp' => $data['response_timestamp'],
        ]);
        $outstandingTransactions = [];
        foreach ($data['outstanding_transactions'] as $tx) {
            $outstandingTransactions[] = new OutstandingTransaction(
                $tx['transaction_id'],
                $tx['datetime'],
                $tx['timestamp'],
                $tx['description'],
                $tx['amount'],
                $tx['balance'],
                $tx['reference_no'],
                $tx['op_type_id'],
                $tx['status'],
                $tx['created_at']
            );
        }
        return new OutstandingTransactions(
            $data['outstanding_credit'],
            $data['outstanding_debit'],
            $data['response_timestamp'],
            $outstandingTransactions
        );
    }

    /**
     * Verifies the authenticity of a response using the response headers and response fields.
     * Throws an exception if verification fails.
     *
     * @param array $headers The response headers.
     * @param array $responseFields The response fields to use for verification.
     * @throws Exception If verification fails.
     */
    private function verifyResponse($headers, $responseFields) {
        $receivedHash = $headers['X-Signature-Hash'] ?? $headers['x-signature-hash'] ?? null;
        $receivedSalt = $headers['X-Signature-Salt'] ?? $headers['x-signature-salt'] ?? null;
        if (!$receivedHash || !$receivedSalt) {
            throw new Exception('Missing X-Signature-Hash or X-Signature-Salt in response headers');
        }
        $hashToken = HashTokenGenerator::generateHashToken($receivedSalt, $this->password);
        $verificationHash = VerificationHashGenerator::generateVerificationHash($hashToken, $responseFields, $this->secretKey);
        if ($verificationHash !== $receivedHash) {
            throw new Exception('Response verification failed: hash mismatch');
        }
    }
}
