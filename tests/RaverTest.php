<?php 
namespace Tests;

use PHPUnit\Framework\TestCase;

use Raver\Raver;
use Raver\Config\Config;

class RaverTest extends TestCase 
{
    public function testTruthy()
    {
        $this->assertTrue(true);
    }

    public function testPrepareData()
    {
      $config = new Config();
      $var_keys = $config->getEnvVars();

      $card  =  array(
                "PBFPubKey" => $var_keys['public_key'] ,
                "cardno" => "4242424242424242",
                "currency" => "GHS",
                "country" => "GH",
                "cvv" => "812",
                "amount" => "100",
                "expiryyear" => "21",
                "expirymonth" => "01",
                "email" => "skafui@gmail.com",
                "phonenumber" => "0201478963",
                "firstname" => "Selorm",
                "lastname" => "Kafui",
                "IP"=> "355426087298442",
                "txRef" => "MC-". time()
            );
      $this->assertIsArray($card);

    }


}


?>