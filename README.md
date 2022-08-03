# Omnipay: Raiffeisen

**[Raiffeisen Bank E-commerce](https://ecg.test.upc.ua/rbbg/merchant) gateway for Omnipay payment processing library**

[Omnipay](https://github.com/thephpleague/omnipay) is a framework agnostic, multi-gateway payment processing library for
PHP. This package implements Raiffeisen Bank BG support for Omnipay.

## Installation

Omnipay is installed via [Composer](http://getcomposer.org/). To install, simply require `league/omnipay`
and `gentor/omnipay-raiffeisen` with Composer:

```
composer require league/omnipay gentor/omnipay-raiffeisen
```

## Basic Usage

### Purchase

```php
$gateway = Omnipay::create('Raiffeisen');

$gateway->setMerchantId($config['merchantId'])
    ->setTerminalId($config['terminalId'])
    ->setPrivateKey($config['privateKey'])
    ->setCurrency($config['currency'])
    ->setTestMode($config['testMode'])
    ->setGatewayCertificate($config['production_gateway_certificate']);

$response = $gateway->purchase(
    [
        'TotalAmount' => 100,
        'OrderID' => 'OrderID',
    ]
)->send();

// Process response
if ($response->isSuccessful()) {
    // Payment was successful
    print_r($response);
} elseif ($response->isRedirect()) {
    // Redirect to offsite payment gateway
    $response->redirect();
} else {
    // Payment failed
    echo $response->getMessage();
}
```

### Complete Purchase

```php
$response = $gateway->completePurchase()->send();

print_r($response->getData());
print_r($response->isSuccessful());
print_r($response->getCode());
print_r($response->getTransactionReference());

```

### Refund

```php
$response = $gateway->refund([
    'TotalAmount' => 100,
    'RefundAmount' => 100,
    'OrderID' => 'OrderID',
    'Rrn' => 'Rrn',
    'ApprovalCode' => 'ApprovalCode',
])->send();

print_r($response->getData());
print_r($response->isSuccessful());
print_r($response->getCode());
print_r($response->getMessage());

```

### Fetch Transaction

```php
$response = $gateway->fetchTransaction([
    'TotalAmount' => 100,
    'OrderID' => 'OrderID',
    'PurchaseTime' => 'PurchaseTime',
])->send();

print_r($response->getData());
print_r($response->isSuccessful());
print_r($response->isReversal());
print_r($response->getCode());
print_r($response->getMessage());
print_r($response->getTransactionReference());

```

### Accept Notification

```php
$response = $gateway->acceptNotification()->send();

print_r($response->getData());
print_r($response->isSuccessful());
print_r($response->getCode());
print_r($response->getMessage());
print_r($response->getTransactionReference());
print_r($response->getTransactionStatus());
print_r($response->getBody());

```

### Pay By Token

```php
$response = $gateway->payByToken([
    'TotalAmount' => 100,
    'OrderID' => 'OrderID',
    'UPCToken' => 'UPCToken',
])->send();

print_r($response->getData());
print_r($response->isSuccessful());
print_r($response->getCode());
print_r($response->getMessage());
print_r($response->getTransactionReference());
print_r($response->getTransactionStatus());
print_r($response->getBody());

```
