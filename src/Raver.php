<?php

namespace Raver;

use Raver\Helpers\Helper;

class Raver extends Executor
{
    public $helper;

    public function __construct()
    {
        parent::__construct();
        $this->helper = new Helper();
    }

    /**
     * Json encode's and encrypts data into the required format.
     *
     * @param array $data
     * @return array payload
     */
    public function prepareData($data)
    {
        //requires an array to continue, converts array to json
        $new_data = is_array($data) ? json_encode($data) : exit('requires an array');

        //encrypts the card data with the key
        $new_data_final = $this->encryptCard($new_data);
        // var_dump($new_data_final);
        //prepares data into the right format
        $postdata = [
          'PBFPubKey' => $this->config_vars['public_key'],
          'client'    => $new_data_final,
          'alg'       => '3DES-24', ];

        //returns json encoded data
        return $postdata;
    }

    /**
     * Inititate payment process for a card
     *
     * @param array $data
     * @return json payload
     */
    public function initiateCardPayment($data)
    {
        $url = '/flwv3-pug/getpaidx/api/charge';
        //get and set data
        $option = $data;
        //prepare data
        $options = $this->prepareData($option);
        //  var_dump($options);
        //post data
        $res = $this->postCharge($options, $url);

        echo $res;
    }

    /**
     * Encrypts card data.
     *
     * @param object $data
     * @return string
     */
    public function encryptCard($data)
    {
        //get key from helper class
        $key = $this->helper->getKey();
        //encrypt card with key
        return $this->helper->encrypt3DES($data, $key);
    }

    public function testApi()
    {
    }
}
?>

