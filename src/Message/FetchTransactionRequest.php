<?php


namespace Omnipay\Raiffeisen\Message;


class FetchTransactionRequest extends AbstractRequest
{
    public function getData()
    {
        $data = parent::getData();

        $this->validate('OrderID', 'TotalAmount');

        return array_merge($data, [
            'TotalAmount' => $this->getParameter('TotalAmount'),
            'OrderID' => $this->getParameter('OrderID'),
        ]);
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
     * @return CompletePurchaseResponse
     */
    public function sendData($data)
    {
        $response = $this->httpClient->request(
            'POST',
            $this->getEndpoint() . '/service/01',
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