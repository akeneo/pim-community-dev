<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Doctrine\Common\Saver;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Ramsey\Uuid\UuidInterface;

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
    private $esProductAndProductModelClient;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->esProductAndProductModelClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
    }

    public function testRemovingProductOnUnitaryRemove()
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct('bat');
        $this->get('pim_catalog.saver.product')->save($product);

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('bat');
        $productUuid = $product->getUuid();

        $this->get('pim_catalog.remover.product')->remove($product);

        $this->assertNotFoundInProductAndProductModelIndex($productUuid);
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
        $productFooUuid = $productFoo->getUuid();
        $productBarUuid = $productBar->getUuid();
        $productBazUuid = $productBaz->getUuid();

        $this->get('pim_catalog.remover.product')->removeAll([$productFoo, $productBar, $productBaz]);

        $this->assertNotFoundInProductAndProductModelIndex($productFooUuid);
        $this->assertNotFoundInProductAndProductModelIndex($productBarUuid);
        $this->assertNotFoundInProductAndProductModelIndex($productBazUuid);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * Asserts the given productId does not exists in the product and product model Index.
     */
    private function assertNotFoundInProductAndProductModelIndex(UuidInterface $productUuid): void
    {
        $found = true;
        try {
            $this->esProductAndProductModelClient->get(self::DOCUMENT_TYPE, 'product_' . $productUuid->toString());
        } catch (Missing404Exception) {
            $found = false;
        }
        $this->assertFalse($found);
    }
}
