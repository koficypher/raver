<?php

/*
 * Raver - Unofficial Rave PHP SDK
 *
 * Copyright - 2019
 *
 * MIT
 *
 * Kofi Cypher <skcypher6@gmail.com>
 */

namespace Raver\Helpers;

use Raver\Config\Config;

class Helper
{
    public $config;

    public $config_var;

    public function __construct()
    {
        $this->config = new Config();
        $this->config_var = $this->config->getEnvVars();
    }

    /**
     * return an md5 formed key.
     *
     * @return string $encryptionkey
     */
    public function getKey()
    {
        $hashedkey = md5($this->config_var['secret_key']);
        $hashedkeylast12 = substr($hashedkey, -12);

        $seckeyadjusted = str_replace('FLWSECK-', '', $this->config_var['secret_key']);
        $seckeyadjustedfirst12 = substr($seckeyadjusted, 0, 12);

        $encryptionkey = $seckeyadjustedfirst12.$hashedkeylast12;

        return $encryptionkey;
    }

    /**
     * returns a base64 encoded encrypted payload.
     *
     * @param json   $data
     * @param string $key
     *
     * @return string payload
     */
    public function encrypt3DES($data, $key)
    {
        $encData = openssl_encrypt($data, 'DES-EDE3', $key, OPENSSL_RAW_DATA);

        return base64_encode($encData);
    }

    /**
     * Receive webhook payload and returns it.
     *
     * @return json-object payload or false on failure
     */
    public static function processWebHook()
    {
        $body = @file_get_contents('php://input');

        return $body ? json_decode($body) : false;
    }

    /**
     * Verifies the sent payload to a webhook by examining the HTTP_VERIFY_HASH.
     *
     * @param string $stored_signature - a signature generated from the request params
     *
     * @return bool true
     */
    public static function verifySignatureHash($stored_signature)
    {
        $signature = (isset($_SERVER['HTTP_VERIF_HASH']) ? $_SERVER['HTTP_VERIF_HASH'] : '');

        if (! $signature) {
            exit();
        }

        if ($signature !== $stored_signature) {
            exit();
        }

        http_response_code(200);

        return true;
    }

    /**
     * Generates a checksum to verify webhook payload.
     *
     * @param array  $data       - request params from card payment
     * @param string $secret_key - account secret key
     *
     * @return string $finalHash - checksum payload
     */
    public static function generateSignature($data, $secret_key)
    {
        $options = is_array($data) ? ksort($data) : false;

        if ($options === false) {
            exit(); //we have no business here if you are not an array
        }

        $payload = '';

        foreach ($options as $key => $value) {
            // code...
            $payload .= $value; //concatenate option values into one string
        }

        $catHash = $payload.$secret_key; //prepend concatenated string with secret_key from your rave dashboard

        $finalHash = hash('sha256', $catHash); // sha256 hash the resulting string

        return $finalHash;
    }

    /**
     * Returns a list of urls for the entire library
     *
     * @return array
     */
    public function getUrls()
    {
        return [
          'charge' => '/flwv3-pug/getpaidx/api/charge',
          'banks' => 'v2/banks/GH',
          'list-transactions' => 'v2/gpx/transactions/query',
          'validate-charge' => '/flwv3-pug/getpaidx/api/validatecharge',
          'verify-charge' => '/flwv3-pug/getpaidx/api/v2/verify',
          'refund' => 'gpx/merchant/transactions/refund',
          'token-charge' => 'flwv3-pug/getpaidx/api/tokenized/charge'
        ];
    }
}
