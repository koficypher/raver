<?php

namespace Raver;

use Raver\Helpers\Helper;

class Raver extends Executor
{
    public $helper;

    public $res;

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

        //prepares data into the right format
        $postdata = [
          'PBFPubKey' => $this->config_vars['public_key'],
          'client'    => $new_data_final,
          'alg'       => '3DES-24', ];

        //returns json encoded data
        return $postdata;
    }

    /**
     * Inititate payment process for a card.
     *
     * @param array $data
     * @return json payload
     */
    public function initiateCardPayment($data)
    {
        $url = '/flwv3-pug/getpaidx/api/charge';
        //prepare data
        $options = $this->prepareData($data);
        
        //post data
        $response = $this->postCharge($options, $url);
        
        //assign response to the global scope
        $this->res = $response;
        
        return  $this->getStep($response, $data);
        //return $this;
    }

    /**
     * Simulated step of the various stages in a card transaction
     *
     * @param json $response from the initiateCard method
     * @param array $data - from the initiateCard method
     * @return void
     */
    public function getStep($response, $data)
    {
        $load = $response != null ? json_decode($response): exit('empty response');
         //var_dump($data->message);
        if($load->status === "success" && $load->message === "AUTH_SUGGESTION"){
             if($load->data->suggested_auth === 'PIN'){

                    $data['suggested_auth'] = 'pin';

                    $this->initiateCardPayment($data);
             }elseif ($load->data->suggested_auth === 'NOAUTH_INTERNATIONAL'){
                   echo 'Do nothing';
             }elseif ($load->data->suggested_auth === 'AVS_VBVSECURECODE'){
                 echo 'Add Address Details';
             }
        }elseif ($load->status === "success" && $load->message === "V-COMP") {
            # code...
              if($load->data->chargeResponseCode === "00"){
                  $verif = $this->verifyCharge($load->data->txRef);
                  echo $verif;
                  //echo 'Charge Complete... use this to verify:  '. $load->data->txRef;
              } elseif ($load->data->chargeResponseCode === "02"&& $load->data->authModelUsed === 'PIN') {
                  $flow = $this->validateCharge($load->data->flwRef,'12345');

                   sleep(3);
                   $flowy = json_decode($flow);
                   if($flowy->status === "success"){
                        $can = $this->verifyCharge($flowy->data->tx->txRef);

                        echo $can;
                   }

              } elseif($load->data->chargeResponseCode === "02" && $load->data->authModelUsed === 'VBVSECURECODE'){
                  echo 'Load this url '.$load->data->authurl.' to verify!';
              } elseif($load->data->chargeResponseCode === "02" && $load->data->authModelUsed === 'ACCESS_OTP') {
                  print_r($load->data->chargeResponseMessage);
              } else {
                  var_dump($load);
              }
        } else {
            echo 'Sorry we cant process your card, please try again with another card';
        }
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
        echo "Yes, am working";
    }
}
?>

