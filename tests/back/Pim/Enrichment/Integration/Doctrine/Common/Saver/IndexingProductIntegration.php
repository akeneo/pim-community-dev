<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Doctrine\Common\Saver;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * Test products have been correctly indexed after being saved.
 * They should be indexed in 1 index:
 *      - pim_catalog_product_and_product_model
 */
class IndexingProductIntegration extends TestCase
{
    /** @var Client */
    private $esProductAndProductModelClient;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->esProductAndProductModelClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
    }

    public function testIndexingProductsOnBulkSave()
    {
        $products = [];
        foreach (['foo', 'bar', 'baz'] as $identifier) {
            $products[] = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        }

        $this->get('pim_catalog.saver.product')->saveAll($products);

        $productFooUuid = $this->get('pim_catalog.repository.product')->findOneByIdentifier('foo')->getUuid()->toString();
        $productBarUuid = $this->get('pim_catalog.repository.product')->findOneByIdentifier('bar')->getUuid()->toString();
        $productBazUuid = $this->get('pim_catalog.repository.product')->findOneByIdentifier('baz')->getUuid()->toString();

        $indexedProductModelFoo = $this->esProductAndProductModelClient->get('product_' . $productFooUuid);
        $this->assertTrue($indexedProductModelFoo['found']);

        $indexedProductModelBar = $this->esProductAndProductModelClient->get('product_' . $productBarUuid);
        $this->assertTrue($indexedProductModelBar['found']);

        $indexedProductModelBaz = $this->esProductAndProductModelClient->get('product_' . $productBazUuid);
        $this->assertTrue($indexedProductModelBaz['found']);
    }

    public function testIndexingProductOnUnitarySave()
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct('bat');
        $this->get('pim_catalog.saver.product')->save($product);

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('bat');

        $productInProductAndProductModelIndex = $this->esProductAndProductModelClient->get(
            'product_' . $product->getUuid()->toString()
        );
        $this->assertTrue($productInProductAndProductModelIndex['found']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
