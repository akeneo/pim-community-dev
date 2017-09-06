<?php

namespace tests\integration\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Elasticsearch\Common\Exceptions\Missing404Exception;

/**
 * Test products have been correctly removed from the index after a product has been removed.
 * They should be removed from 2 indexes:
 *      - pim_catalog_product
 *      - pim_catalog_product_and_product_model
 */
class RemovingProductIntegration extends TestCase
{
    private const DOCUMENT_TYPE = 'pim_catalog_product';

    /** @var Client */
    private $esProductClient;

    /** @var Client */
    private $esProductAndProductModelClient;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->esProductClient = $this->get('akeneo_elasticsearch.client.product');
        $this->esProductAndProductModelClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
    }

    public function testRemovingProductOnUnitaryRemove()
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct('bat');
        $this->get('pim_catalog.saver.product')->save($product);

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('bat');
        $productId = $product->getId();

        $this->get('pim_catalog.remover.product')->remove($product);

        $this->assertNotFoundInProductIndex($productId);
        $this->assertNotFoundInProductAndProductModelIndex($productId);
    }

    public function testRemovingProductsOnBulkRemove()
    {
        $products = [];
        foreach (['foo', 'bar', 'baz'] as $identifier) {
            $products[] = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        }

        $this->get('pim_catalog.saver.product')->saveAll($products);

        $productFoo = $this->get('pim_catalog.repository.product')->findOneByIdentifier('foo');
        $productBar = $this->get('pim_catalog.repository.product')->findOneByIdentifier('bar');
        $productBaz = $this->get('pim_catalog.repository.product')->findOneByIdentifier('baz');
        $productFooId = $productFoo->getId();
        $productBarId = $productBar->getId();
        $productBazId = $productBaz->getId();

        $this->get('pim_catalog.remover.product')->removeAll([$productFoo, $productBar, $productBaz]);

        $this->assertNotFoundInProductIndex($productFooId);
        $this->assertNotFoundInProductIndex($productBarId);
        $this->assertNotFoundInProductIndex($productBazId);

        $this->assertNotFoundInProductAndProductModelIndex($productFooId);
        $this->assertNotFoundInProductAndProductModelIndex($productBarId);
        $this->assertNotFoundInProductAndProductModelIndex($productBazId);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getTechnicalCatalogPath()]);
    }

    /**
     * Asserts the productId does not exists in the product index
     *
     * @param string $productId
     */
    private function assertNotFoundInProductIndex(string $productId)
    {
        $found = true;
        try {
            $this->esProductClient->get(self::DOCUMENT_TYPE, $productId);
        } catch (Missing404Exception $e) {
            $found = false;
        }
        $this->assertFalse($found);
    }

    /**
     * Asserts the given productId does not exists in the product and product model Index.
     *
     * @param string $productId
     */
    private function assertNotFoundInProductAndProductModelIndex(string $productId): void
    {
        $found = true;
        try {
            $this->esProductAndProductModelClient->get(self::DOCUMENT_TYPE, 'product_' . $productId);
        } catch (Missing404Exception $e) {
            $found = false;
        }
        $this->assertFalse($found);
    }
}
