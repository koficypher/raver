<?php
namespace Raver\Config;

use Dotenv\Dotenv;

class Config
{
    public $dotenv;

    public function __construct()
    {
        $this->dotenv = Dotenv::create(__DIR__.'/../../');
        $this->dotenv->load();
    }

    /**
     * returns the env variables set in .env file.
     *
     * @return array
     */
    public function getEnvVars()
    {
        return [

            'public_key' => getenv('PUBLIC_KEY'),

            'secret_key' => getenv('SECRET_KEY'),

            'production_flag' => getenv('PRODUCTION_FLAG'),

        ];
    }
}
