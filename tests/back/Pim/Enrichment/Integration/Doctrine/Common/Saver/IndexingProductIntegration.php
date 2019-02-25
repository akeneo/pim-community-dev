<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Doctrine\Common\Saver;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * Test products have been correctly indexed after being saved.
 * They should be indexed in 2 indexes:
 *      - pim_catalog_product
 *      - pim_catalog_product_and_product_model
 */
class IndexingProductIntegration extends TestCase
{
    private const DOCUMENT_TYPE = 'pim_catalog_product';

    /** @var Client */
    private $esProductClient;

    /** @var Client */
    private $esProductAndProductModelClient;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->esProductClient = $this->get('akeneo_elasticsearch.client.product');
        $this->esProductAndProductModelClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
    }

    public function testIndexingProductsOnBulkSave()
    {
        $products = [];
        foreach (['foo', 'bar', 'baz'] as $identifier) {
            $products[] = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        }

        $this->get('pim_catalog.saver.product')->saveAll($products);

        $productFooId = $this->get('pim_catalog.repository.product')->findOneByIdentifier('foo')->getId();
        $productBarId = $this->get('pim_catalog.repository.product')->findOneByIdentifier('bar')->getId();
        $productBazId = $this->get('pim_catalog.repository.product')->findOneByIdentifier('baz')->getId();

        $indexedProductFoo = $this->esProductClient->get(self::DOCUMENT_TYPE, $productFooId);
        $this->assertTrue($indexedProductFoo['found']);

        $indexedProductBar = $this->esProductClient->get(self::DOCUMENT_TYPE, $productBarId);
        $this->assertTrue($indexedProductBar['found']);

        $indexedProductBaz = $this->esProductClient->get(self::DOCUMENT_TYPE, $productBazId);
        $this->assertTrue($indexedProductBaz['found']);

        $indexedProductModelFoo = $this->esProductAndProductModelClient->get(
            self::DOCUMENT_TYPE,
            'product_' . $productFooId
        );
        $this->assertTrue($indexedProductModelFoo['found']);

        $indexedProductModelBar = $this->esProductAndProductModelClient->get(
            self::DOCUMENT_TYPE,
            'product_' . $productBarId
        );
        $this->assertTrue($indexedProductModelBar['found']);

        $indexedProductModelBaz = $this->esProductAndProductModelClient->get(
            self::DOCUMENT_TYPE,
            'product_' . $productBazId
        );
        $this->assertTrue($indexedProductModelBaz['found']);
    }

    public function testIndexingProductOnUnitarySave()
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct('bat');
        $this->get('pim_catalog.saver.product')->save($product);

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('bat');

        $productInProductIndex = $this->esProductClient->get(self::DOCUMENT_TYPE, $product->getId());
        $this->assertTrue($productInProductIndex['found']);

        $productInProductAndProductModelIndex = $this->esProductAndProductModelClient->get(
            self::DOCUMENT_TYPE,
            'product_' . $product->getId()
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
