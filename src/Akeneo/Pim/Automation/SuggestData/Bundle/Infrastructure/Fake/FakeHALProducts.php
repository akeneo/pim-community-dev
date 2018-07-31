<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\Fake;

/**
 * Allows to fake products with HAL format
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class FakeHALProducts
{
    /** @var array */
    private $productsToFake = [];

    public function getFakeHAL(): string
    {
        $formattedProducts = [
            '_links' => [
                'product' => []
            ],
            '_embedded' => [
                'product' => [],
            ],
        ];

        foreach ($this->productsToFake as $product) {
            $formattedProducts['_links']['product'][] = $product['_links']['self'];
            $formattedProducts['_embedded']['product'][] = $product;
        }

        return json_encode($formattedProducts);
    }

    public function addProduct(string $identifier): FakeHALProducts
    {
        if (array_key_exists($identifier, $this->productsToFake)) {
            return $this;
        }

        $product = [
            'id' => '',
            'codes' => [
                'sku' => '',
                'upc' => '',
                'asin' => '',
                'mpn_brand' => [],
            ],
            'attributes' => [],
            '_links' => [
                'self' => [
                    'href' => 'https://::ffff:172.20.0.10/api/subscription/'
                ],
                'subscribe' => [
                    'href' => 'https://::ffff:172.20.0.10/subscribe',
                    'type' => 'application/prs.hal-forms+json',
                ],
                'unsubscribe' => [
                    'href' => 'https://::ffff:172.20.0.10/unsubscribe',
                    'type' => 'application/prs.hal-forms+json',
                ],
                'server-sent-events' => [
                    'href' => 'https://::ffff:172.20.0.10/sse/',
                    'type' => 'text/event-stream',
                ],
                'websocket' => [
                    'href' => 'https://::ffff:172.20.0.10/ws/',
                ],
            ],
            '_embedded' => [
                'subscribe' => [
                    '_links' => [
                        'self' => ['href' => 'https://::ffff:172.20.0.10/subscribe'],
                    ],
                    '_templates' => [
                        'default' => [
                            'title'  => 'subscribe',
                            'method'  => 'POST',
                            'contentType' => 'application/x-www-form-urlencoded',
                            'properties' => [
                                ['name' => 'hub.callback', 'required' => true, 'type' => 'url'],
                                ['name' => 'hub.topic', 'required' => true, 'type' => 'url'],
                                ['name' => 'hub.mode', 'required' => true, 'type' => 'string', 'value' => 'subscribe'],
                            ],
                        ],
                    ],
                ],
                'unsubscribe' => [
                    '_links' => [
                        'self' => ['href' => 'https://::ffff:172.20.0.10/unsubscribe'],
                    ],
                    '_templates' => [
                        'default' => [
                            'title' => 'unsubscribe',
                            'method' => 'POST',
                            'contentType' => 'application/x-www-form-urlencoded',
                            'properties' => [
                                ['name' => 'hub.callback', 'required' => true, 'type' => 'url'],
                                ['name' => 'hub.topic', 'required' => true, 'type' => 'url'],
                                ['name' => 'hub.mode', 'required' => true, 'type' => 'string', 'value' => 'unsubscribe']
                            ]
                        ],
                    ],
                ],0
            ],
        ];

        $id = $this->getRandomId();
        $product['id'] = $id;
        $product['codes']['sku'] = $identifier;
        $product['codes']['upc'] = $this->getRandomUPC();
        $product['codes']['asin'] = $this->getRandomASIN();
        $product['codes']['mpn_brand'] = $this->getRandomBrand();
        $product['_links']['self']['href'] .= $id;

        $this->productsToFake[$identifier] = $product;

        return $this;
    }

    private function getRandomId(): string
    {
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr(dechex(mt_rand()), 0, 8),
            substr(dechex(mt_rand()), 0, 4),
            substr(dechex(mt_rand()), 0, 4),
            substr(dechex(mt_rand()), 0, 4),
            substr(dechex(mt_rand()), 0, 8)
        );
    }

    private function getRandomUPC(): string
    {
        return str_pad((string) mt_rand(), 12, '0', STR_PAD_LEFT);
    }

    private function getRandomASIN(): string
    {
        return strtoupper(str_pad(dechex(mt_rand()), 10, 'A'));
    }

    private function getRandomBrand(): array
    {
        $brands = [
            ['mpn' => '956983256', 'brand' => 'cocola'],
            ['mpn' => '360845296', 'brand' => 'messoubishi'],
            ['mpn' => '491929767', 'brand' => 'ibayème'],
            ['mpn' => '844712207', 'brand' => 'néscapé'],
            ['mpn' => '541113582', 'brand' => 'sansong'],
        ];
        shuffle($brands);

        return $brands[0];
    }
}
