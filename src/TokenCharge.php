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

use Raver\Config\Config;
use Raver\Helpers\Helper;

class TokenCharge extends Executor
{
    public $helper;

    public function __construct()
    {
        $this->helper = new Helper();
        $this->config = new Config();
        $this->config_vars = $this->config->getEnvVars();
    }

    /**
     * Function to perform a tokenized charge.
     *
     * @param string $token
     * @param array $data
     * @return json payload
     */
    public function charge($token, $data)
    {
        is_array($data) ? $data['token'] = $token : exit('Data needs to be an array');

        $seckey = $this->config_vars['secret_key'];

        $data['SECKEY'] = $seckey;

        $url = $this->helper->getUrls()['token-charge'];

        return $this->postRaveRequest($url, $data);
    }

    /**
     * Updates an email address associated with a token.
     *
     * @param string $email
     * @param string $token
     * @param array $data - optional
     * @return json payload
     */
    public function updateEmail($email, $token, $data = null)
    {
      //prepare url
       $url = 'v2/gpx/tokens/'.$token.'/update_customer';

       //get secret key
       $seckey = $this->config_vars['secret_key'];

       //check data format and presence
       if($data) {
            if(is_array($data)){
              $data['token'] = $token;
              $data['secret_key'] = $seckey;
            } else {
              exit('Data needs to be an array');
            }
       } else {

         $load = ['email' => $email,'secret_key' => $seckey];
       }



       $response = $this->postRaveRequest($url,$data);

       return $response;
    }
}
