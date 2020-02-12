<?php


namespace Lantera\Safta\ApiService;

use Lantera\Safta\Base;

class Virtual extends Base
{
    protected $defaultOptions = [
        'postcode' => 'LE11 1RW',
        'filter' => [
            'suppliers' => [],
            'terms' => [],
            'bandwidths' => [],
            'bearers' => [],
            'accessTypes' => [],
        ]
    ];

    public function getData($type)
    {
        $this->defaultOptions['postcode'] = $this->postCode;
        if ($type == 'quoting') {
            $productVariation =  json_decode($this->getOptionData($this->defaultOptions, 'layer2-api/quoting'), true);
            if ($productVariation !== null && count($productVariation) > 0) {
                $accessProducts = $productVariation['accessProducts'];
                return json_encode($accessProducts);
//                return json_encode($this->formatData($accessProducts));
            }
        }
        return false;
    }

//    protected function formatData($data)
//    {
//        foreach ($data as $product) {
//            d($product);
//        }
//    }

    protected function getOptionData($options, $prefix)
    {
        $options = json_encode($this->defaultOptions);
        $url = $this->apiServiceUrl.$prefix;
        $curlInit = curl_init();
        curl_setopt($curlInit, CURLOPT_HEADER, 0);
        curl_setopt($curlInit, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlInit, CURLOPT_USERPWD, $this->login . ":" . $this->password);
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
        return false;
    }

}