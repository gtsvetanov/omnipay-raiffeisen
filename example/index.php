<?php
require '../vendor/autoload.php';

use Omnipay\Omnipay;
use Omnipay\Raiffeisen\Gateway;
use Omnipay\Raiffeisen\Signature;

echo '<pre>';

$key = Signature::generateKeyPair();
print_r($key);

$csr = Signature::generateCSR($key['private']);
print_r($csr);

$crt = Signature::signCSR($key['private'], $csr['csr']);
print_r($crt);
//exit;

/** @var Gateway $gateway */
$gateway = Omnipay::create('Raiffeisen');
$gateway->setMerchantId('')
    ->setTerminalId('')
    ->setPrivateKey('')
    ->setTestMode(true);

//parse_str('', $notify);
//print_r($notify);
////exit;
//
//$response = $gateway->completePurchase($notify)->send();
//
//print_r($response->getData());
//print_r($response->isSuccessful());
//print_r($response->getCode());
//print_r($response->getMessage());
//exit;

$request = $gateway->purchase(
    [
        'TotalAmount' => 100,
        'OrderID' => date('His'),
    ]
);

$response = $request->send();

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
