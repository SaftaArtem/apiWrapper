<?php

namespace Lantera\Safta\ApiService;

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
             if ($type == 'quoting') {
                $groupOptions = $this->getProductsTermVariation($productCatalogue);
                $optionData = $this->getOptionData($groupOptions, 'price/product');
                return $this->formatData($optionData);
            }
        }
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
        return $groupData;
    }

    /**
     * @param $arrays
     * @param int $i
     * @return array
     */
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

    protected function formatData($data)
    {
        $data = json_decode($data, true);
        $result = [];
        foreach ($data as $key => $product) {
            if (isset($product['prices'])) {
                if (count($product['prices']) > 0) {
                    foreach ($product['prices'] as $item) {
                        if (!$item['hasPrice']) continue;
                        $attributes = $item['attributes'];
                        $uniqueId = md5($item['code'].'_'.$attributes['term'].'_'.$attributes['serviceBandwidth'].'_'.$attributes['bearerSize']);
                        $chunkName = explode('- ', $item['name']);
                        if (count($chunkName) <= 1) continue;
                        $supplier = explode('- ', $item['name'])[1];
                        $oneOfCost = $item['nonRecurring']['price'];
                        $monthlyCost = $item['monthly']['price'];
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
            }
        }
        return $result;
    }
}