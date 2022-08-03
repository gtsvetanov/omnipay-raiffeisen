<?php


namespace Omnipay\Raiffeisen\Message;


abstract class AbstractResponse extends \Omnipay\Common\Message\AbstractResponse
{
    const CODES = [
        '-' => '',
        '000' => 'Approved',
        '101' => 'Invalid card parameters',
        '105' => 'Not approved by emitent',
        '108' => 'Lost/stolen card',
        '111' => 'Non existent card',
        '116' => 'Insufficient funds',
        '130' => 'Limit is exceeded',
        '290' => 'Issuer is not accessible',
        '291' => 'Technical/Communication problem',
        '401' => 'Invalid format',
        '402' => 'Invalid Acquirer/Merchant data',
        '403' => 'Component communication failure',
        '404' => 'Authentication error',
        '405' => 'Signature is invalid',
        '406' => 'Quota of transactions exceeded',
        '407' => 'Merchant is not active',
        '408' => 'Transaction was not found',
        '409' => 'Too many transactions were found',
        '410' => 'The order was paid (possible replay)',
        '411' => 'The order request time is out-of-date',
        '412' => 'Replay order condition',
        '413' => 'Unknown card type',
        '414' => 'CVC required',
        '420' => 'The total amount of successful transactions per day is limited',
        '421' => 'Tran amount limit (non 3-D Secure full authenticated)',
        '430' => 'Transaction is prohibited by Gateway',
        '431' => 'Attempted 3D-Secure is not accepted',
        '432' => 'Card is in stop list',
        '433' => 'The number of transactions has exceeded the limit',
        '434' => 'The merchant does not accept cards from the country',
        '435' => 'Client IP address is on stop list',
        '436' => 'The sum of amount transactions has exceeded the limit',
        '437' => 'The limit of card number inputs has been exceeded',
        '438' => 'Unacceptable currency code',
        '439' => 'The time limit from request to authorization has been exceeded',
        '440' => 'The authorization time limit has been exceeded',
        '441' => 'MPI interaction problem',
        '442' => 'ACS communication problem',
        '450' => 'Recurrent payments are prohibited',
        '451' => 'MPI service not enabled',
        '452' => 'Card-to-Card Payment service not enabled',
        '454' => 'The gateway is prohibited from using Ref3',
        '455' => 'Refund request denied by gateway',
        '460' => 'Token service not enabled',
        '501' => 'Canceled by user',
        '502' => 'The web session is expired',
        '503' => 'Transaction was canceled by merchant',
        '504' => 'Transaction was canceled by gateway with reversal',
        '505' => 'Invalid sequence of operations',
        '506' => 'Preauthorized transaction is expired',
        '507' => 'Preauthorized transaction already processed with payment',
        '508' => 'Invalid amount to pay a preauthorized transaction',
        '509' => 'Not able to trace back to original transaction',
        '510' => 'Refund is expired',
        '511' => 'Transaction was canceled by settlement action',
        '512' => 'Repeated reversal or refund',
        '601' => 'Not completed',
        '602' => 'Waiting confirmation of instalment',
        '902' => 'Cannot process transaction',
        '909' => 'Cannot process transaction',
        '999' => 'Transaction in progress...',
    ];

    /**
     * Get the response data.
     *
     * @return mixed
     */
    public function getData()
    {
        unset($this->data['version']);
        unset($this->data['locale']);
        unset($this->data['privateKey']);
        unset($this->data['gatewayCertificate']);
        unset($this->data['testMode']);

        $this->data['TranMessage'] = self::CODES[$this->data['TranCode'] ?? '-'] ?? '';

        return $this->data;
    }

}