<?php


namespace Lantera\Safta\ApiService;

use Lantera\Safta\Base as Base;

class Exponential extends Base
{

    private $apiServiceUrl = null;
    private $login = null;
    private $apiKey = null;
    public $postCode = null;

    public function __construct($apiKey, $login, $postCode, $apiServiceUrl)
    {
        $this->postCode = $postCode;
        $this->apiKey = $apiKey;
        $this->login = $login;
        $this->apiServiceUrl = $apiServiceUrl;
    }

    public function getData($type)
    {
        if ($productCatalogue = $this->getProductCatalogue()) {


            $this->getProductsTermVariation($productCatalogue);
            d('<h1>Fin</h1>');


            if ($type == 'quote') {
                $quoteOptions = $this->formatProductCatalogueQuote($productCatalogue);
                $this->getOptionData($quoteOptions, 'price/quote');
            } elseif ($type == 'price') {
                $groupOptions = $this->formatProductCataloguePrice($productCatalogue);
                $this->getOptionData($groupOptions, 'price/product');
            }
        }
        return false;
    }

    /**
     * Default GET request return all products
     * @return bool|string
     */
    private function getProductCatalogue()
    {
        $url = $this->apiServiceUrl . "catalogue?domain=$this->login&apiKey=$this->apiKey";
        $curlInit = curl_init();
        curl_setopt($curlInit, CURLOPT_HEADER, 0);
        curl_setopt($curlInit, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlInit, CURLOPT_URL, $url);
        $data = curl_exec($curlInit);
        curl_close($curlInit);
        if ($data != '') {
            return json_decode($data, true);
        }
        return false;
    }

    /**
     * Format Data for getProductPricing Request
     * @param $productCatalogue
     * @return bool|false|array
     */
    private function formatProductCataloguePrice($productCatalogue)
    {
        if (count($productCatalogue) > 0) {
            $groupData = [];
            foreach ($productCatalogue as $product) {
                if ($product['orderFormConfigurations'] == 1) {
                    $productAttributes = [];
                    foreach ($product['attributes'] as $attributes) {
                        if ($attributes['name'] == 'term') {
                            $productAttributes['postcode'] = $this->postCode;
                            $productAttributes['term'] = $attributes['defaultValue'];
                        }
                        if ($attributes['name'] == 'bearerSize') {
                            $productAttributes['bearerSize'] = $attributes['defaultValue'];
                        }
                        if ($attributes['name'] == 'serviceBandwidth') {
                            $productAttributes['serviceBandwidth'] = $attributes['defaultValue'];
                        }
                    }
                    $groupData['products'][] = [
                        'attributes' => $productAttributes,
                        'code' => $product['code'],
                        'tag' => '8b11c66e-1f18-4148-acf4-bb918bc0c7a6'
                    ];
                }
            }
            if (count($groupData) > 0) {
                return $groupData;
            }
        }
        return false;
    }

    /**
     * @param $productCatalogue
     * @return array|bool All products
     */
    private function formatProductCatalogueQuote($productCatalogue)
    {
        if (count($productCatalogue) > 0) {
            $groupData = [];
            $groupDataChunk = [];
            foreach ($productCatalogue as $product) {
                if ($product['orderFormConfigurations'] == 1) {
                    $productAttributes = [];
                    foreach ($product['attributes'] as $attributes) {
                        if ($attributes['name'] == 'term') {
                            $productAttributes['postcode'] = $this->postCode;
                            $productAttributes['term'] = $attributes['defaultValue'];
                            $term = $attributes['defaultValue'];
                        }
                        if ($attributes['name'] == 'bearerSize') {
                            $productAttributes['bearerSize'] = $attributes['defaultValue'];
                        }
                        if ($attributes['name'] == 'serviceBandwidth') {
                            $productAttributes['serviceBandwidth'] = $attributes['defaultValue'];
                        }
                    }


                    $groupDataChunk['type'] = 1;
                    $groupDataChunk['products'][] = [
                        'attributes' => $productAttributes,
                        'code' => $product['code'],
                        'tag' => '8b11c66e-1f18-4148-acf4-bb918bc0c7a6'
                    ];
                    $groupData[] = $groupDataChunk;
                }
            }
            $result = [
                'attributes' => ['term' => $term],
                'groups' => $groupData
            ];
            return $result;
        }
        return false;

    }

    /**
     * @param $options
     * @param $prefix
     * @return bool|string
     */
    private function getOptionData($options, $prefix)
    {
        $options = json_encode($options);
        $url = $this->apiServiceUrl . "$prefix?domain=$this->login&apiKey=$this->apiKey";
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
        dd($prefix);
        d(json_decode($data, true));

        if ($data != '') {
            return $data;
        }
        return false;
    }

    /**
     * @param $productCatalogue
     * @return array|bool products variation array with key equals variation of term [12, 24, 36...]
     */
    public function getProductsTermVariation($productCatalogue)
    {
        $products = [];
        foreach ($productCatalogue as $product) {
            if ($product['orderFormConfigurations'] == 1) {
//                d($product);
                foreach ($product['attributes'] as $attribute) {
                    if ($attribute['name'] == 'term') {
                        $min = $attribute['minimum'];
                        $max = $attribute['defaultValue'];

                        while ($min <= $max) {
                            $products[$min][] = $product;
                            $min += 12;
                        }
                    }
                    if ($attribute['name'] == 'bearerSize') {
                        dd($attribute['values']);
                        dd($attribute['unitType']);
                        }
                }
            }
        }
        d('-');
        if (count($products) > 0) {
            return $products;
        }
        return false;
    }
}