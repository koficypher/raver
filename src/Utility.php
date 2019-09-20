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

use Raver\Helpers\Helper;

class Utility extends Executor
{
    public $helper;

    public function __construct()
    {
        parent::__construct();
        $this->helper = new Helper();
    }

    /**
     * Get lists of banks for transfers in Ghana.
     *
     * @return json payload - list of all banks in Ghana available for transfers
     */
    public function getBanksList()
    {
        $url = 'v2/banks/GH';

        $param = ['public_key' => $this->config_vars['public_key']];

        $res = $this->getRaveRequest($url, $param);

        echo $res;
    }

    public function listTransactions($start, $end, $status)
    {
        $url = 'v2/gpx/transactions/query';
        $data['seckey'] = $this->config_vars['secret_key'];
        $data['currency'] = 'GHS';
        $data['status'] = $status;
        $data['from'] = $start;
        $data['to'] = $end;

        $res = $this->postRaveRequest($url, $data);

        echo $res;
    }
}
