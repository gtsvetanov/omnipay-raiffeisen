<?php


namespace Omnipay\Raiffeisen\Message;


use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Raiffeisen\Signature;

class PayByTokenRequest extends AbstractRequest
{
    public function getData()
    {
        $data = parent::getData();

        $this->validate('OrderID', 'TotalAmount', 'UPCToken');

        $data = array_merge($data, [
            'TotalAmount' => $this->getParameter('TotalAmount'),
            'OrderID' => $this->getParameter('OrderID'),
            'UPCToken' => $this->getParameter('UPCToken'),
            'Recurrent' => $this->getParameter('Recurrent'),
        ]);

        if ($this->getParameter('cvc')) {
            $data['cvc'] = $this->getParameter('cvc');
        }

        return $data;
    }

    public function getSignedData()
    {
        $data = array_intersect_key($this->getData(), array_flip([
            'MerchantID',
            'TerminalID',
            'OrderID',
            'UPCToken',
            'TotalAmount',
            'Currency',
            'PurchaseTime',
            'PurchaseDesc',
            'Recurrent',
            'cvc',
        ]));

        return Signature::createJWS($data, $this->getPrivateKey());
    }

    /**
     * Send the request
     *
     * @return PayByTokenResponse
     */
    public function send()
    {
        return parent::send();
    }

    /**
     * @param mixed $data
     * @return CompletePurchaseResponse
     */
    public function sendData($data)
    {
        $response = $this->httpClient->request(
            'POST',
            $this->getEndpoint() . '/payByToken',
            [
                'Content-type' => 'application/json',
            ],
            json_encode($this->getSignedData())
        );

        $data = json_decode($response->getBody()->getContents(), true);

//        $valid = Signature::verifyJWS($data, $this->getParameter('gatewayCertificate'));
//        if ($valid < 1) {
//            throw new InvalidRequestException("Invalid gateway signature: " . $data['signature']);
//        }

        return $this->response = new PayByTokenResponse($this, $data);
    }
}