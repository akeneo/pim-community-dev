<?php
declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\Adapter\Memory;

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\Adapter\DataProviderAdapterInterface;

/**
 * In memory implementation to connect to a data provider
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class InMemoryAdapter implements DataProviderAdapterInterface
{
    /** @var array */
    private $pushedProducts;

    /** @var array */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->pushedProducts = [];
        $this->configure($config);
    }

    /**
     * {@inheritdoc}
     */
    public function push(ProductInterface $product): string
    {
        $identifier = $product->getIdentifier(); // TODO get with mapping
        if (!in_array($identifier, $this->pushedProducts)) {
            $this->pushedProducts[] = $identifier;
        }

        $product = $this->getFakeProduct($identifier);

        return $this->formatToHal([$product]);
    }

    /**
     * {@inheritdoc}
     */
    public function bulkPush(array $products): string
    {
        $fakeProducts = [];
        foreach ($products as $product) {
            $identifier = $product->getIdentifier(); // TODO get with mapping
            if (!in_array($identifier, $this->pushedProducts)) {
                $this->pushedProducts[] = $identifier;
            }
            $fakeProducts[] = $this->getFakeProduct($identifier);
        }

        return $this->formatToHal($fakeProducts);
    }

    public function pull(ProductInterface $product)
    {
        throw new \Exception(
            sprintf('"%s" is not yet implemented'),
            __METHOD__
        );
    }

    public function bulkPull(array $products)
    {
        throw new \Exception(
            sprintf('"%s" is not yet implemented'),
            __METHOD__
        );
    }

    public function authenticate()
    {
        throw new \Exception(
            sprintf('"%s" is not yet implemented'),
            __METHOD__
        );
    }

    /**
     * @param array $config
     */
    public function configure(array $config): void
    {
        $this->config = $config;
    }

    private function formatToHal(array $products): string
    {
        $formattedProducts = [
            '_links' => [
                'product' => []
            ],
            '_embedded' => [
                'product' => [],
            ],
        ];

        foreach ($products as $product) {
            $formattedProducts['_links']['product'][] = $product['_links']['self'];
            $formattedProducts['_embedded']['product'][] = $product;
        }

        return json_encode($formattedProducts);
    }

    private function getFakeProduct(string $identifier): array
    {
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

        return $product;
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
            ['mpn' => '408653063', 'brand' => 'cocola'],
        ];
        shuffle($brands);

        return $brands[0];
    }
}
