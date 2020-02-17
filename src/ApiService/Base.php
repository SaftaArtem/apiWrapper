<?php


namespace Lantera\Safta\ApiService;


abstract class Base
{
    protected $apiServiceUrl;
    protected $login;
    protected $password;
    protected $postCode;
    protected $connection = [];

    abstract public function getData($type);
//    abstract protected function formatData($data);

    /**
     * @param $options
     * @param $prefix
     * @return bool|string
     */
    protected function getOptionData($options, $prefix)
    {
        $options = json_encode($options);
        $url = $this->apiServiceUrl . "$prefix?domain=$this->login&apiKey=$this->password";
        $curlInit = curl_init();
        curl_setopt($curlInit, CURLOPT_HEADER, 0);
        curl_setopt($curlInit, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlInit, CURLOPT_URL, $url);
        curl_setopt($curlInit, CURLOPT_POST, 1);
        curl_setopt($curlInit, CURLOPT_POSTFIELDS, $options);
        curl_setopt($curlInit, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($options))
        );
        $data = curl_exec($curlInit);
        curl_close($curlInit);
        if ($data != '') {
            return $data;
        }
    }

    public function setData(array $connection)
    {
        foreach ($connection as $name => $value) {
            $this->{$name} = $value;
        }
        $this->connection = $connection;
        $this->validate();
    }

    protected function validate()
    {
        $connection = $this->connection;
        if (count($connection) > 0) {
            foreach ($connection as $key => $param) {
                if (!is_string($param)) {
                    throw new \InvalidArgumentException("$key is not a string.");
                }
                if (strlen($param) < 3) {
                    throw new \InvalidArgumentException("$key \"" . $param . "\" is too short, and thus invalid.");
                }
            }
            return true;
        }

    }


}