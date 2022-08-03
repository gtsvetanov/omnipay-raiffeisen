<?php


namespace Omnipay\Raiffeisen\Message;


use Omnipay\Common\Message\ResponseInterface;
use Omnipay\Raiffeisen\Signature;

class PurchaseRequest extends AbstractRequest
{
    public function getData()
    {
        $data = parent::getData();

        $this->validate('OrderID', 'TotalAmount');

        $data = array_merge($data, [
            'TotalAmount' => $this->getParameter('TotalAmount'),
            'OrderID' => $this->getParameter('OrderID'),
            'PurchaseDesc' => $this->getParameter('PurchaseDesc'),
            'SD' => $this->getParameter('SD'),
        ]);

        if ($this->getParameter('Recurrent')) {
            $data['Recurrent'] = 'true';
        }

        $data['Signature'] = $this->sign($data);

        return $data;
    }

    protected function sign($data)
    {
        $message = Signature::getMacSourceValue($data);

        return Signature::create($message, $this->getPrivateKey());
    }

    /**
     * Send the request
     *
     * @return PurchaseResponse|ResponseInterface
     */
    public function send()
    {
        return parent::send();
    }

    /**
     * @param mixed $data
     * @return PurchaseResponse
     */
    public function sendData($data)
    {
        return $this->response = new PurchaseResponse($this, $data);
    }
}