<?php


namespace Lantera\Safta;

use Lantera\Safta\ApiService\Exponential as Exponential;

class BaseApi
{

    public function __construct($apiKey = null, $login = null, $apiServiceType = null)
    {
        if (self::validateApiKey($apiKey) && self::validateApiServiceType($apiServiceType)) {
            if ($apiServiceType == "exponential") {
               return new Exponential($apiKey, $login);
            }
        }
    }

    private static function validateApiKey($apiKey)
    {
        return true;

    }

    private static function validateApiServiceType($apiServiceType)
    {
        return true;
    }

}