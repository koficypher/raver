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

namespace Raver;

use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use Raver\Config\Config;
use GuzzleHttp\Exception\TransferException;

class Executor
{
    public $client;

    public $config;

    public $config_vars;

    public function __construct()
    {
        $this->config = new Config();
        $this->config_vars = $this->config->getEnvVars();
        $this->client = $this->config_vars['production_flag'] === true ? new Client(['base_uri' => 'https://api.ravepay.co', 'headers' => ['content-type' => 'application/json']]) : new Client(['base_uri' => 'https://ravesandboxapi.flutterwave.com', 'headers' => ['content-type' => 'application/json']]);
    }

    public function postCharge($data, $url)
    {
        try {
            $res = $this->client->request('POST', $url, ['json' => $data]);

            return $res->getBody();
        } catch (TransferException $e) {
            echo Psr7\str($e->getRequest());
            echo Psr7\str($e->getResponse());
        }
    }

    /**
     * Validates a card charge transaction.
     *
     * @param string $flw_ref - transaction flow reference from initiate charge response
     * @param string $otp - one time code/pin sent to you via mail or mobile
     * @return json payload on successful transaction and error message on error
     */
    public function validateCharge($flw_ref, $otp)
    {
        $url = '/flwv3-pug/getpaidx/api/validatecharge';
        try {
            $res = $this->client->request('POST', $url, ['json' => ['PBFPubKey'=>$this->config_vars['public_key'], 'transaction_reference' =>$flw_ref, 'otp'=>$otp]]);

            return $res->getBody();
        } catch (TransferException $e) {
            echo Psr7\str($e->getRequest());
            echo Psr7\str($e->getResponse());
        }
    }

    /**
     * Verifies the state of a charge.
     *
     * @param string $tx_ref
     * @return json payload on successful transaction and error message on error
     */
    public function verifyCharge($tx_ref)
    {
        $url = '/flwv3-pug/getpaidx/api/v2/verify';
        try {
            $res = $this->client->request('POST', $url, ['json' => ['txref'=>$tx_ref, 'SECKEY' =>$this->config_vars['secret_key']]]);

            return $res->getBody();
        } catch (TransferException $e) {
            echo Psr7\str($e->getRequest());
            echo Psr7\str($e->getResponse());
        }
    }
}
