<?php

namespace Akeneo\Bundle\ElasticsearchBundle\tests\integration;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BulkIndexationIntegration extends TestCase
{
    const DOCUMENT_TYPE = 'pim_catalog_product';

    /** @var Client */
    private $esProductClient;

    public function testIndexationOnABulk()
    {
        $count = 5;
        $products = [];
        for ($i = 1; $i <= $count; $i++) {
            $products[] = ['identifier' => 'product_' . $i];
        }

        $indexedProducts = $this->esProductClient->bulkIndexes(self::DOCUMENT_TYPE, $products, 'identifier');
        $this->assertFalse($indexedProducts['errors']);
        $this->assertCount($count, $indexedProducts['items']);

        foreach ($indexedProducts['items'] as $index => $indexedProduct) {
            $this->assertSame('product_' . ($index + 1), $indexedProduct['index']['_id']);

            $result = 'product_1' === $indexedProduct['index']['_id'] ? 'updated' : 'created';
            $version = 'product_1' === $indexedProduct['index']['_id'] ? 2 : 1;
            $this->assertSame($result, $indexedProduct['index']['result']);
            $this->assertSame($version, $indexedProduct['index']['_version']);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getMinimalCatalogPath()]);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->esProductClient = $this->get('akeneo_elasticsearch.client.product');
        $products = [
            [
                'identifier'           => 'product_1',
                'description-textarea' => 'My product description',
            ]
        ];

        $this->indexProductDocuments($products);
    }

    /**
     * Indexes the given list of products
     *
     * @param array $products
     */
    private function indexProductDocuments(array $products)
    {
        foreach ($products as $product) {
            $this->esProductClient->index(self::DOCUMENT_TYPE, $product['identifier'], $product);
        }

        $this->esProductClient->refreshIndex();
    }
}
