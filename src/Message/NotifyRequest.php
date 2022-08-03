<?php


namespace Omnipay\Raiffeisen\Message;


use Omnipay\Common\Exception\InvalidRequestException;

class NotifyRequest extends AbstractRequest
{
    /**
     * @return array
     */
    public function getData()
    {
        parse_str($this->httpRequest->getContent(), $data);
//        $data = $this->getParameters();
//
//        unset($data['privateKey']);
//        unset($data['gatewayCertificate']);
//        unset($data['testMode']);

        return $data;
    }

    /**
     * Send the request
     *
     * @return NotifyResponse
     */
    public function send()
    {
        return parent::send();
    }

    /**
     * @param mixed $data
     * @return NotifyResponse
     * @throws InvalidRequestException
     */
    public function sendData($data)
    {
        $this->validateGatewaySignature($data);

        return $this->response = new NotifyResponse($this, $data);
    }
}