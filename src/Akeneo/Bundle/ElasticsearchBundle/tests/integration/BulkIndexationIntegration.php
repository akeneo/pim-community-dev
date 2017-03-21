<?php

namespace Akeneo\Bundle\ElasticsearchBundle\tests\integration;

use Akeneo\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Elasticsearch\Client;

class BulkIndexationIntegration extends TestCase
{
    const DOCUMENT_TYPE = 'pim_catalog_product';

    /** @var Client */
    private $esClient;

    /** @var Loader */
    private $esConfigurationLoader;

    public function testIndexationOnABulk()
    {
        $count = 5;
        $products = [];
        for ($i = 1; $i <= $count; $i++) {
            $products[] = ['identifier' => 'product_' . $i];
        }

        $indexedProducts = $this->esClient->bulkIndexes(self::DOCUMENT_TYPE, $products, 'identifier');
        $this->assertFalse($indexedProducts['errors']);
        $this->assertCount($count, $indexedProducts['items']);

        foreach ($indexedProducts['items'] as $index => $indexedProduct) {
            $this->assertSame('product_' . $index+=1, $indexedProduct['index']['_id']);

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
        return new Configuration([Configuration::getMinimalCatalogPath()], false);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->esClient = $this->get('akeneo_elasticsearch.client');
        $this->esConfigurationLoader = $this->get('akeneo_elasticsearch.index_configuration.loader');

        $this->resetIndex();

        $products = [
            [
                'identifier'       => 'product_1',
                'description-text' => 'My product description',
            ]
        ];

        $this->indexProducts($products);
    }

    /**
     * Indexes the given list of products
     *
     * @param array $products
     */
    private function indexProducts(array $products)
    {
        foreach ($products as $product) {
            $this->esClient->index(self::DOCUMENT_TYPE, $product['identifier'], $product);
        }

        $this->esClient->refreshIndex();
    }

    /**
     * Resets the index used for the integration tests query
     */
    private function resetIndex()
    {
        $conf = $this->esConfigurationLoader->load();

        if ($this->esClient->hasIndex()) {
            $this->esClient->deleteIndex();
        }

        $this->esClient->createIndex($conf->buildAggregated());
    }
}
