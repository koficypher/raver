<?php

namespace Raver;

use Raver\Helpers\Helper;

class Raver extends Executor
{
    public $helper;
    public $utility;

    public function __construct()
    {
        parent::__construct();
        $this->helper = new Helper();
        $this->utility = new Utility();
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
        $new_data_final = $this->encryptData($new_data);

        //prepares data into the right format
        $postdata = [
          'PBFPubKey' => $this->config_vars['public_key'],
          'client'    => $new_data_final,
          'alg'       => '3DES-24', ];

        //returns an array of the prepared data
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
        $url = $this->charge_url;
        //prepare data
        $options = $this->prepareData($data);

        //post data
        $response = $this->postCharge($options, $url);

        //return json response
        return  $this->getCardStep($response, $data);
    }

    /**
     * Simulated step of the various stages in a card transaction.
     *
     * @param json $response from the initiateCardPayment method
     * @param array $data - from the initiateCardData method
     * @return void
     */
    public function getCardStep($response, $data)
    {
        $load = $response != null ? json_decode($response) : exit('empty response');
        //var_dump($data->message);
        if ($load->status === 'success' && $load->message === 'AUTH_SUGGESTION') {
            if ($load->data->suggested_auth === 'PIN') {
                $data['suggested_auth'] = 'pin';

                $this->initiateCardPayment($data);
            } elseif ($load->data->suggested_auth === 'NOAUTH_INTERNATIONAL') {
                return 'Do nothing';
            } elseif ($load->data->suggested_auth === 'AVS_VBVSECURECODE') {
                return 'Add Address Details';
            }
        } elseif ($load->status === 'success' && $load->message === 'V-COMP') {
            // code...
            if ($load->data->chargeResponseCode === '00') {
                // echo 'Charge Complete... use this to verify:  '.$load->data->txRef;
                $verif = $this->verifyCharge($load->data->txRef);

                return $verif;
            } elseif ($load->data->chargeResponseCode === '02' && $load->data->authModelUsed === 'PIN') {
                $flow = $this->validateCharge($load->data->flwRef, '12345');

                sleep(3);
                $flowy = json_decode($flow);
                if ($flowy->status === 'success') {
                    $can = $this->verifyCharge($flowy->data->tx->txRef);

                    return $can;
                }
            } elseif ($load->data->chargeResponseCode === '02' && $load->data->authModelUsed === 'VBVSECURECODE') {
                return 'Load this url '.$load->data->authurl.' to verify!';
            } elseif ($load->data->chargeResponseCode === '02' && $load->data->authModelUsed === 'ACCESS_OTP') {
                return $load->data->chargeResponseMessage;
            } else {
                return $load;
            }
        } else {
            return 'Sorry we cant process your card, please try again with another card';
        }
    }

    /**
     * @param array $data momo payment details
     *
     * @return json payload
     */
    public function initiateMomoPayment($data)
    {
        $url = $this->charge_url;

        //prepare data
        $options = $this->prepareData($data);

        //post data
        $response = $this->postCharge($options, $url);

        //return json response
        return  $this->getMomoStep($response);
    }

    /**
     * @param json $reponse response from initiateMomoCharge method
     *
     * @return json payload
     */
    public function getMomoStep($response)
    {
        $load = $response != null ? json_decode($response) : exit('empty response');

        if ($load->status === 'success' && $load->message === 'V-COMP') {
            sleep(2);

            $verif = $this->verifyCharge($load->data->txRef);

            return $verif;
        } else {
            return 'Sorry mobile money request could not be completed';
        }
    }

    /**
     * Encrypts payload.
     *
     * @param object $data
     * @return string
     */
    public function encryptData($data)
    {
        //get key from helper class
        $key = $this->helper->getKey();
        //encrypt card with key
        return $this->helper->encrypt3DES($data, $key);
    }

    public function testApi()
    {
        return 'Yes, am working now';
    }
}
?>

