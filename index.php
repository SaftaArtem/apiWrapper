<?php
if (!defined('DS')){
    define('DS',"/");
}

//if(defined('IS_DEVELOPER')===false && $_SERVER['REMOTE_ADDR'] == '194.29.63.73') {
//    define('IS_DEVELOPER', true);
//}
define('IS_DEVELOPER', true);

if (function_exists ('dd')===false) {
    function dd($object)
    {
        if (IS_DEVELOPER === false) return false;
        echo "<pre>";
        var_dump($object);
        echo "</pre>";
//		throw new \Exception("Error Processing Request", 1);

    }
}
if (function_exists ('dlog')===false) {
    function dlog($object)
    {
        if (IS_DEVELOPER) {
            //Mage::log($object, null, "dev.log");
            //dd($object);
        }
    }
}

if (function_exists ('d')===false) {
    function d($object)
    {

        dd($object);
        dbg();
        die("___END");
    }
}

if (function_exists ('ci')===false) {
    function ci($class)
    {
        dd(get_class($class));
        d(get_class_methods($class));
    }
}


if (function_exists ('dbg')===false) {
    function dbg(){
        $val = debug_backtrace();
        $dbg = array();
        //error_reporting(E_ALL ^ E_NOTICE);
        foreach($val as $d){

            $dbg[] = $d['file']."->".$d['function'].":".$d['line'];
        }

        echo "<pre>";
        var_dump($dbg);
        echo "</pre>";
        die('-');
    }}

if (function_exists ('ddbg')===false) {
    function ddbg(){
        $val = debug_backtrace();
        $dbg = array();
        //error_reporting(E_ALL ^ E_NOTICE);
        foreach($val as $d){

            $dbg[] = $d['file']."->".$d['function'].":".$d['line'];
        }

        echo "<pre>";
        var_dump($dbg);
        echo "</pre>";

    }}


require_once "vendor/autoload.php";

$apiKey = '0beb0298-779b-4da9-9e99-444ca691daf6';
$login = 'luminet.co.uk';
$postCode = "LE11 1RW";
$apiServiceUrl = 'https://qe2.exponential-e.com/cpq/api/v1/';

$obj = new \Lantera\Safta\ApiService\Exponential($apiKey, $login, $postCode, $apiServiceUrl);

$obj->getData('price');



















