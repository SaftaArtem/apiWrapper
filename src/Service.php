<?php


namespace Lantera\Safta;

//use Lantera\Safta\ApiService\Exponential as Exponential;
//use Lantera\Safta\ApiService\Virtual;

class Service
{
    const APIS = ['Exponential', 'Virtual'];
    const CLASSNAMESPACE = '\Lantera\Safta\ApiService\\';

    public static function getData($connection)
    {
        $result = [];
        foreach ($connection as $apiName => $apiConnection) {
            if (in_array($apiName, self::APIS)) {
                $className = self::CLASSNAMESPACE.$apiName;
                $apiObject = new $className();
                $apiObject->setData($apiConnection);
                $apiData = json_decode($apiObject->getData('quoting'), true);
                if ($apiData !== null && count($apiData) > 0) {
                    $result = array_merge($result, $apiData);
                }
            }
        }
        if (count($result) > 0) {
            return $result;
        }
        return false;
    }

}