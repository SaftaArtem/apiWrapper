<?php


namespace Lantera\Safta;

use http\Exception\InvalidArgumentException;
use Lantera\Safta\Exceptions\MainException;

/**
 * Class Main
 * The main class of working with the API
 * @package Lantera\Safta
 */
class ApiKey
{
    /**
     * @var string API access key
     */
    public static $apiKey = null;

    /**
     * @var string Instance API key, set for each new entity
     */
    private $instanceApiKey;

    /**
     * Main constructor. API access key
     * @param string|null $apiKey
     * @throws MainException If no api is passing
     */
    public function __construct($apiKey)
    {
        if ($apiKey === null) {
            if (self::$apiKey === null) {
                $msg = 'The API key is not transferred or installed globally.';
                $msg .= 'Use Main :: setApiKey, or pass it to the constructor.';
                throw new MainException($msg);
            }
        } else {
            self::validateApiKey($apiKey);
            $this->instanceApiKey = $apiKey;
        }
    }

    /**
     * setting API key for all new instances
     * @param $apiKey string API access key
     * @return void
     */
    public static function setApiKey($apiKey)
    {
        self::validateApiKey($apiKey);
        self::$apiKey = $apiKey;
    }

    private static function validateApiKey($apiKey)
    {
        if (!is_string($apiKey)) {
            throw new \InvalidArgumentException('Api key is not a string.');
        }
        if (strlen($apiKey) < 4) {
            throw new InvalidArgumentException('API key"' . $apiKey . '" too short, and not valid.');
        }
    }

}