<?php


namespace Omnipay\Raiffeisen\Message;


use Omnipay\Raiffeisen\Signature;

class RefundRequest extends AbstractRequest
{
    public function getData()
    {
        $data = parent::getData();

        $this->validate('OrderID', 'TotalAmount', 'ApprovalCode', 'Rrn');

        $data = array_merge($data, [
            'TotalAmount' => $this->getParameter('TotalAmount'),
            'RefundAmount' => $this->getParameter('RefundAmount') ?? $this->getParameter('TotalAmount'),
            'OrderID' => $this->getParameter('OrderID'),
            'ApprovalCode' => $this->getParameter('ApprovalCode'),
            'Rrn' => $this->getParameter('Rrn'),
            'SD' => $this->getParameter('SD'),
        ]);

        $data['Signature'] = $this->sign($data);

        return $data;
    }

    protected function sign($data)
    {
        $message = Signature::getMacSourceValue($data, 'refund');

        return Signature::create($message, $this->getPrivateKey());
    }

    /**
     * Send the request
     *
     * @return CompletePurchaseResponse
     */
    public function send()
    {
        return parent::send();
    }

    /**
     * @param mixed $data
     */
    public function sendData($data)
    {
        $response = $this->httpClient->request(
            'POST',
            $this->getEndpoint() . '/repayment',
            [
                'Content-type' => 'application/x-www-form-urlencoded',
            ],
            http_build_query($data)
        );

        $contents = str_replace("\n", '&', rtrim($response->getBody()->getContents(), "\n"));
        parse_str($contents, $data);

        return $this->response = new CompletePurchaseResponse($this, $data);
    }
}