<?php


namespace Omnipay\Raiffeisen\Message;


use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Raiffeisen\Signature;

abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    protected $liveEndpoint = 'https://secure.rbb.bg/rbbg';
    protected $testEndpoint = 'https://ecg.test.upc.ua/rbbg';

    /**
     * @inheritDoc
     */
    public function initialize(array $parameters = array())
    {
        parent::initialize($parameters);

        $this->setParameter('PurchaseTime', gmdate('ymdHis'));

        foreach ($parameters as $key => $value) {
            $this->setParameter($key, $value);
        }

        return $this;
    }

    /**
     * @return array|mixed
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('MerchantID', 'TerminalID', 'privateKey');
        $this->validatePrivateKey();

        return [
            'MerchantID' => $this->getParameter('MerchantID'),
            'TerminalID' => $this->getParameter('TerminalID'),
            'PurchaseTime' => $this->getParameter('PurchaseTime'),
            'Currency' => $this->getParameter('Currency'),
            'version' => $this->getParameter('version'),
            'locale' => $this->getParameter('locale'),
        ];
    }

    public function getPrivateKey()
    {
        return $this->getParameter('privateKey');
    }

    public function getEndpoint()
    {
        return $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;
    }

    /**
     * Get the payment currency code.
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->getParameter('Currency');
    }

    /**
     * Sets the payment currency code.
     *
     * @param string $value
     * @return $this
     */
    public function setCurrency($value)
    {
        return $this->setParameter('Currency', $value);
    }

    /**
     * @throws InvalidRequestException
     */
    public function validatePrivateKey()
    {
        $result = openssl_get_privatekey($this->getPrivateKey());
        if (!$result) {
            throw new InvalidRequestException("The privateKey parameter is invalid");
        }
    }

    /**
     * @param array $data
     * @throws InvalidRequestException
     */
    public function validateGatewaySignature(array $data)
    {
        if (!$this->getParameter('gatewayCertificate')) {
            return;
        }

        $valid = Signature::verify($data, $this->getParameter('gatewayCertificate'));
        if ($valid < 1) {
            throw new InvalidRequestException("Invalid gateway signature: " . $data['Signature']);
        }
    }

}