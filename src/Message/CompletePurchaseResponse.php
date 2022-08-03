<?php


namespace Omnipay\Raiffeisen\Message;


use Omnipay\Common\Message\NotificationInterface;

class CompletePurchaseResponse extends AbstractResponse implements NotificationInterface
{
    public function isSuccessful()
    {
        return $this->getCode() === '000';
    }

    public function getCode()
    {
        return $this->data['TranCode'] ?? null;
    }

    public function getTransactionId()
    {
        return $this->data['OrderID'] ?? null;
    }

    public function getTransactionReference()
    {
        return $this->data['ApprovalCode'] ?? null;
    }

    public function getTransactionStatus()
    {
        if ($this->isSuccessful()) {
            return self::STATUS_COMPLETED;
        }

        if ($this->getCode() === '999') {
            return self::STATUS_PENDING;
        }

        return self::STATUS_FAILED;
    }

    public function getMessage()
    {
        return $this->data['ERROR'] ?? null;
    }

}