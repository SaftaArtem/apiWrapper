<?php


namespace Lantera\Safta\ApiService;

use Lantera\Safta\Base;

class Exponential extends Base
{
    protected $params = [
        [100, 1000, 10000],
        [12, 24, 36],
        [10, 20, 50, 100, 200, 500, 1000, 2000, 5000, 10000]
    ];

    public function getData($type)
    {
        if ($productCatalogue = $this->getProductCatalogue()) {
            if ($type == 'quote') {
                $quoteOptions = $this->formatProductCatalogueQuote($productCatalogue);
                return $this->getOptionData($quoteOptions, 'price/quote');
            } elseif ($type == 'quoting') {
                $groupOptions = $this->getProductsTermVariation($productCatalogue);
                $optionData = $this->getOptionData($groupOptions, 'price/product');
                return $this->formatData($optionData);
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
        $url = $this->apiServiceUrl . "catalogue?domain=$this->login&apiKey=$this->password";
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
     * @param $productCatalogue
     * @return array|bool products variation array with key
     */
    public function getProductsTermVariation($productCatalogue)
    {
        $params = $this->params;
        $variants = $this->generateCombinations($params);
        $groupData = [];
        foreach ($productCatalogue as $product) {
            if ($product['orderFormConfigurations'] == 1) {
                foreach ($variants as $variant) {
                    $groupData['products'][] = [
                        'attributes' => [
                            'postcode' => $this->postCode,
                            'bearerSize' => $variant[0],
                            'term' => $variant[1],
                            'serviceBandwidth' => $variant[2]
                        ],
                        'code' => $product['code'],
                        'tag' => implode('_', $variant)
                    ];
                }
            }
        }
        if (count($groupData) > 0) {
            return $groupData;
        }
        return false;
    }

    /**
     * @param $serviceBandwidth
     * @return array All variant serviceBandwidth from min to max
     */
    public function generateBandWidth($serviceBandwidth)
    {
        $min = $serviceBandwidth['min'];
        $max = $serviceBandwidth['max'];
        $result = [];
        while ($min <= 60) {
            if (intval($min) === 0) $min += 10;
            $result[] = $min;
            $min += 10;
        }
        while ($min <= $max) {
            if ($min == 70) $min = 100;
            $result[] = $min;
            $min += 100;
        }
        return $result;
    }

    public function generateCombinations($arrays, $i = 0)
    {
        if (!isset($arrays[$i])) {
            return array();
        }
        if ($i == count($arrays) - 1) {
            return $arrays[$i];
        }

        // get combinations from subsequent arrays
        $tmp = $this->generateCombinations($arrays, $i + 1);

        $result = array();

        // concat each array from tmp with each element from $arrays[$i]
        foreach ($arrays[$i] as $v) {
            foreach ($tmp as $t) {
                $result[] = is_array($t) ?
                    array_merge(array($v), $t) :
                    array($v, $t);
            }
        }

        return $result;
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

    /**
     * List with all modification
     * @return array
     */
    protected function getAddonsList()
    {
        return true;
    }

    protected function formatData($data)
    {
        $data = json_decode($data, true);
        $result = [];
        foreach ($data as $product) {
            if (isset($product['prices']) && $product['prices']['hasPrice']) {
                $attributes = $product['attributes'];
                $uniqueId = md5($product['code'].'_'.$attributes['term'].'_'.$attributes['serviceBandwidth'].'_'.$attributes['bearerSize']);
                $supplier = explode('- ', $product['name'])[1];
                $oneOfCost = $product['prices'][0]['nonRecurring']['price'];
                $monthlyCost = $product['prices'][0]['monthly']['price'];
                $result[] = [
                    'unique_id' => $uniqueId,
                    'supplier' => $supplier,
                    'type' => 'lite',
                    'term' => $attributes['term'],
                    'bandwidth' => $attributes['serviceBandwidth'],
                    'bearer_size' => $attributes['bearerSize'],
                    'one_cost' => $oneOfCost,
                    'monthly_cost' => $monthlyCost,
                ];
            }
        }
        $adsd = '';
        return $result;
    }
}