<?php

namespace tests\integration\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Elasticsearch\Client;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;

class IndexingProductIntegration extends TestCase
{
    const DOCUMENT_TYPE = 'pim_catalog_product';

    /** @var Client */
    private $esClient;

    /** @var Loader */
    private $esConfigurationLoader;

    /** @var ProductBuilderInterface */
    private $productBuilder;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    public function testIndexingProductsOnBulkSave()
    {
        $products = [];
        foreach (['foo', 'bar', 'baz'] as $identifier) {
            $products[] = $this->productBuilder->createProduct($identifier);
        }

        $this->get('pim_catalog.saver.product')->saveAll($products);

        $indexedProductFoo = $this->esClient->get(self::DOCUMENT_TYPE, 'foo');
        $this->assertTrue($indexedProductFoo['found']);

        $indexedProductBar = $this->esClient->get(self::DOCUMENT_TYPE, 'bar');
        $this->assertTrue($indexedProductBar['found']);

        $indexedProductBaz = $this->esClient->get(self::DOCUMENT_TYPE, 'baz');
        $this->assertTrue($indexedProductBaz['found']);
    }

    public function testIndexingProductOnUnitarySave()
    {
        $product = $this->productBuilder->createProduct('bat');
        $this->get('pim_catalog.saver.product')->save($product);

        $indexedProduct = $this->esClient->get(self::DOCUMENT_TYPE, 'bat');
        $this->assertTrue($indexedProduct['found']);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->esClient = $this->get('akeneo_elasticsearch.client');
        $this->esConfigurationLoader = $this->get('akeneo_elasticsearch.index_configuration.loader');
        $this->productBuilder = $this->get('pim_catalog.builder.product');
        $this->productUpdater = $this->get('pim_catalog.updater.product');

        $this->resetIndex();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getTechnicalCatalogPath()], false);
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
