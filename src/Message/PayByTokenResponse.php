<?php


namespace Omnipay\Raiffeisen\Message;


use Omnipay\Common\Message\RequestInterface;

class PayByTokenResponse extends NotifyResponse
{
    protected $body = [];

    /**
     * Constructor
     *
     * @param RequestInterface $request the initiating request.
     * @param mixed $data
     */
    public function __construct(RequestInterface $request, $data)
    {
        $this->request = $request;
        $this->body = $data;
        $this->data = json_decode(base64_decode($data['payload']), true);
    }

    public function getMessage()
    {
        return $this->data['Comment'] ?? null;
    }

    public function getBody()
    {
        return $this->body;
    }

}