<?php


namespace Omnipay\Raiffeisen\Message;


class NotifyResponse extends CompletePurchaseResponse
{
    public function getBody()
    {
        $body = '';
        foreach ($this->data as $key => $val) {
            $body .= $key . '="' . $val . '"' . "\n";
        }

        return $body;
    }
}