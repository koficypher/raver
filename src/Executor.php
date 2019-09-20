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

use Monolog\Logger;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use Raver\Config\Config;
use Monolog\Handler\StreamHandler;
use GuzzleHttp\Exception\TransferException;

class Executor
{
    public $client;

    public $config;

    public $config_vars;

    public $logger;

    protected $charge_url = '/flwv3-pug/getpaidx/api/charge';

    public function __construct()
    {
        $this->config = new Config();
        $this->logger = new Logger('RaverLogs');
        $this->logger->pushHandler(new StreamHandler(__DIR__.'../../logs/raver.log', Logger::DEBUG));
        $this->config_vars = $this->config->getEnvVars();
        $this->client = $this->config_vars['production_flag'] === true ? new Client(['base_uri' => 'https://api.ravepay.co', 'headers' => ['content-type' => 'application/json']]) : new Client(['base_uri' => 'https://ravesandboxapi.flutterwave.com', 'headers' => ['content-type' => 'application/json']]);
    }

    protected function postCharge($data, $url)
    {
        try {
            $res = $this->client->request('POST', $url, ['json' => $data]);

            return $res->getBody();
        } catch (TransferException $e) {
            $response = $e->getResponse();
            $this->logger->error('An error occurred during the transaction', ['request' => Psr7\str($e->getRequest()), 'response' => Psr7\str($e->getResponse())]);
            echo $response->getBody();
        }
    }

    /**
     * Validates a card charge transaction.
     *
     * @param string $flw_ref - transaction flow reference from initiate charge response
     * @param string $otp - one time code/pin sent to you via mail or mobile
     * @return json payload on successful transaction and error message on error
     */
    protected function validateCharge($flw_ref, $otp)
    {
        $url = '/flwv3-pug/getpaidx/api/validatecharge';
        try {
            $res = $this->client->request('POST', $url, ['json' => ['PBFPubKey'=>$this->config_vars['public_key'], 'transaction_reference' =>$flw_ref, 'otp'=>$otp]]);

            return $res->getBody();
        } catch (TransferException $e) {
            $response = $e->getResponse();
            $this->logger->error('An error occurred trying to validate the charge', ['request' => Psr7\str($e->getRequest()), 'response' => Psr7\str($e->getResponse())]);
            echo $response->getBody();
        }
    }

    /**
     * Verifies the state of a charge.
     *
     * @param string $tx_ref
     * @return json payload on successful transaction and error message on error
     */
    protected function verifyCharge($tx_ref)
    {
        $url = '/flwv3-pug/getpaidx/api/v2/verify';
        try {
            $res = $this->client->request('POST', $url, ['json' => ['txref'=>$tx_ref, 'SECKEY' =>$this->config_vars['secret_key']]]);

            return $res->getBody();
        } catch (TransferException $e) {
            $response = $e->getResponse();
            $this->logger->error('An error occurred trying to verify the charge', ['request' => Psr7\str($e->getRequest()), 'response' => Psr7\str($e->getResponse())]);
            echo $response->getBody();
        }
    }

    /**
     * refunds a charge to the customer, only successful rave transactions can be refunded.
     *
     * @param string $reference flwref returned from successfull transaction
     * @param string $amount amount to be refunded
     *
     * @return json a json payload telling the state of the refund
     */
    public function refundCharge($reference, $amount)
    {
        $url = 'gpx/merchant/transactions/refund';

        try {
            $res = $this->client->request('POST', $url, ['json' => ['ref'=>$reference, 'seckey' =>$this->config_vars['secret_key'], 'amount' => $amount]]);

            return $res->getBody();
        } catch (TransferException $e) {
            $response = $e->getResponse();
            $this->logger->error('An error occurred trying to refund the charge', ['request' => Psr7\str($e->getRequest()), 'response' => Psr7\str($e->getResponse())]);
            echo $response->getBody();
        }
    }

    /**
     * Performs a generic get request on the rave api.
     *
     * @param [string] $url
     * @param [array] $parameters
     * @return json payload
     */
    public function getRaveRequest($url, $parameters)
    {
        try {
            $res = $this->client->request('GET', $url, ['query' => $parameters]);

            return $res->getBody();
        } catch (TransferException $e) {
            $response = $e->getResponse();
            $this->logger->error('An error occurred trying to perform the get request', ['request' => Psr7\str($e->getRequest()), 'response' => Psr7\str($e->getResponse())]);
            echo $response->getBody();
        }
    }

    /**
     * Performs a generic post request on the rave api.
     *
     * @param [string] $url
     * @param [array] $data
     * @return json payload
     */
    public function postRaveRequest($url, $data)
    {
        try {
            $res = $this->client->request('POST', $url, ['json' => $data]);

            return $res->getBody();
        } catch (TransferException $e) {
            $response = $e->getResponse();
            $this->logger->error('An error occurred trying to perform the post request', ['request' => Psr7\str($e->getRequest()), 'response' => Psr7\str($e->getResponse())]);
            echo $response->getBody();
        }
    }
}
