<?php
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
}
