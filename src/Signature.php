<?php


namespace Omnipay\Raiffeisen;


use DateTime;

class Signature
{
    const ALGO = OPENSSL_ALGO_SHA1;

    public static $macFields = [
        'purchase' => [
            'MerchantID',
            'TerminalID',
            'PurchaseTime',
            'OrderID',
            'Currency',
            'TotalAmount',
            'SD',
        ],
        'notify' => [
            'MerchantID',
            'TerminalID',
            'PurchaseTime',
            'OrderID',
            'XID',
            'Currency',
            'TotalAmount',
            'SD',
            'TranCode',
            'ApprovalCode',
        ],
        'refund' => [
            'MerchantID',
            'TerminalID',
            'PurchaseTime',
            'OrderID',
            'Currency',
            'TotalAmount',
            'SD',
            'ApprovalCode',
            'Rrn',
            'RefundAmount',
        ],
    ];

    public static $csrFields = [
        'commonName',
        'countryName',
        'localityName',
        'emailAddress',
        'organizationName',
        'stateOrProvinceName',
        'organizationalUnitName',
    ];

    public static function create($message, $privateKey)
    {
        $privateKeyId = openssl_get_privatekey($privateKey);

        openssl_sign($message, $signature, $privateKeyId, self::ALGO);
        openssl_free_key($privateKeyId);

        return base64_encode($signature);
    }

    public static function verify(array $data, $certificate)
    {
        if (empty($data['Signature'])) {
            return -1;
        }

        $message = self::getMacSourceValue($data, 'notify');
        if (!empty($data['UPCToken']) && !empty($data['UPCTokenExp'])) {
            $message .= $data['UPCToken'] . ',' . $data['UPCTokenExp'] . ';';
        }

        $signature = base64_decode($data['Signature']);
        $publicKeyId = openssl_get_publickey($certificate);

        return openssl_verify($message, $signature, $publicKeyId, self::ALGO);
    }

    public static function getMacSourceValue(array $data, $dataType = 'purchase')
    {
        $macFields = self::$macFields[$dataType] ?? [];
        $message = '';

        foreach ($macFields as $field) {
            $message .= ($data[$field] ?? '') . ';';
        }

        if ($dataType == 'purchase' && !empty($data['Recurrent'])) {
            $message .= 'true;';
        }

        return $message;
    }

    public static function createJWS(array $data, $privateKey)
    {
        $header = base64_encode(json_encode(['alg' => 'RS512']));
        $payload = base64_encode(json_encode($data));
        $message = "$header.$payload";

        $privateKeyId = openssl_get_privatekey($privateKey);
        openssl_sign($message, $binarySignature, $privateKeyId, OPENSSL_ALGO_SHA512);
        openssl_free_key($privateKeyId);

        $signature = base64_encode($binarySignature);

        return [
            'header' => $header,
            'payload' => $payload,
            'signature' => $signature,
        ];
    }

    public static function verifyJWS(array $data, $certificate)
    {
        if (empty($data['header']) || empty($data['payload']) || empty($data['signature'])) {
            return -1;
        }

        $message = "{$data['header']}.{$data['payload']}";
        $signature = base64_decode($data['signature']);
        $publicKeyId = openssl_get_publickey($certificate);

        return openssl_verify($message, $signature, $publicKeyId, OPENSSL_ALGO_SHA512);
    }

    /**
     * Generate 2048 bit RSA private and public keys
     *
     * @return array
     */
    public static function generateKeyPair()
    {
        $key = openssl_pkey_new(array(
            "private_key_bits" => 1024,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ));

        $details = openssl_pkey_get_details($key);
        openssl_pkey_export($key, $privateKey);

        return [
            'bits' => $details['bits'],
            'type' => 'RSA-SHA1',
            'public' => $details['key'],
            'private' => $privateKey,
        ];
    }

    /**
     * Generate Certificate Signing Request
     *
     * @param $privateKey
     * @param array $subject
     * @return array
     */
    public static function generateCSR($privateKey, array $subject = [])
    {
        $dn = [
            'commonName' => 'Omnipay Raiffeisen',
            'organizationName' => 'Omnipay Raiffeisen',
            'organizationalUnitName' => 'Omnipay Raiffeisen',
            'countryName' => 'BG',
            'localityName' => 'BG',
            'stateOrProvinceName' => 'BG',
        ];

        foreach ($subject as $key => $value) {
            if (in_array($key, self::$csrFields)) {
                $dn[$key] = $value;
            }
        }

        $privkey = openssl_get_privatekey($privateKey);
        $resource = openssl_csr_new($dn, $privkey, ['digest_alg' => self::ALGO]);

        openssl_csr_export($resource, $csr);

        return array_merge(
            [
                'csr' => $csr,
            ],
            self::parseCSR($csr)
        );
    }

    /**
     * Sign Certificate Signing Request
     *
     * @param $privateKey
     * @param $csr
     * @param int $days
     * @param null $ca
     * @return array
     */
    public static function signCSR($privateKey, $csr, int $days = 365, $ca = null)
    {
        $x509 = openssl_csr_sign($csr, $ca, $privateKey, $days, ['digest_alg' => self::ALGO]);

        openssl_x509_export($x509, $crt);

        return array_merge(
            [
                'crt' => $crt,
            ],
            self::parseCertificate($crt)
        );
    }

    public static function parseCSR($certificate)
    {
        $subject = openssl_csr_get_subject($certificate, false);
        $publicKey = openssl_csr_get_public_key($certificate);
        $publicKey = openssl_pkey_get_details($publicKey);

        return [
            'subject' => $subject,
            'key' => [
                'bits' => $publicKey['bits'],
                'type' => $publicKey['type'] === 0 ? 'RSA-SHA1' : $publicKey['type'],
                'public' => $publicKey['key'],
            ],
        ];
    }

    public static function parseCertificate($certificate)
    {
//        $parsed = openssl_x509_read($certificate);
        $parsed = openssl_x509_parse($certificate, false);
        if ($parsed === false) {
            return false;
        }

        $publicKey = openssl_get_publickey($certificate);
        $details = openssl_pkey_get_details($publicKey);
        $publicKey = [
            'bits' => $details['bits'],
            'type' => $parsed['signatureTypeSN'],
            'public' => $details['key'],
        ];

        return [
//            'parsed' => $parsed,
            'subject' => $parsed['subject'],
            'issuer' => $parsed['issuer'],
            'validFrom' => date(DateTime::ISO8601, $parsed['validFrom_time_t']),
            'validTo' => date(DateTime::ISO8601, $parsed['validTo_time_t']),
            'isExpired' => $parsed['validTo_time_t'] < time(),
            'serialNumberHex' => $parsed['serialNumberHex'],
            'fingerprint' => [
                'md5' => wordwrap(strtoupper(openssl_x509_fingerprint($certificate, 'md5')), 2, ':', true),
                'sha1' => wordwrap(strtoupper(openssl_x509_fingerprint($certificate, 'sha1')), 2, ':', true),
                'sha256' => wordwrap(strtoupper(openssl_x509_fingerprint($certificate, 'sha256')), 2, ':', true),
            ],
            'key' => $publicKey,
        ];
    }

    public static function checkCertificatePrivateKey($certificate, $key)
    {
        return openssl_x509_check_private_key($certificate, $key);
    }
}