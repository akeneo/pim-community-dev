<?php

namespace tests\integration\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * Test products have been correctly indexed after being saved.
 * They should be indexed in 2 indexes:
 *      - pim_catalog_product
 *      - pim_catalog_product_and_product_model
 *
 * TODO: test the second index
 */
class IndexingProductIntegration extends TestCase
{
    private const DOCUMENT_TYPE = 'pim_catalog_product';

    public function testIndexingProductsOnBulkSave()
    {
        $products = [];
        foreach (['foo', 'bar', 'baz'] as $identifier) {
            $products[] = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        }

        $this->get('pim_catalog.saver.product')->saveAll($products);

        $indexedProductModelFoo = $this->esClient->get(self::DOCUMENT_TYPE, 'foo');
        $this->assertTrue($indexedProductModelFoo['found']);

        $indexedProductModelBar = $this->esClient->get(self::DOCUMENT_TYPE, 'bar');
        $this->assertTrue($indexedProductModelBar['found']);

        $indexedProductModelBaz = $this->esClient->get(self::DOCUMENT_TYPE, 'baz');
        $this->assertTrue($indexedProductModelBaz['found']);
    }

    public function testIndexingProductOnUnitarySave()
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct('bat');
        $this->get('pim_catalog.saver.product')->save($product);

        $indexedProductModel = $this->esClient->get(self::DOCUMENT_TYPE, 'bat');
        $this->assertTrue($indexedProductModel['found']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getTechnicalCatalogPath()], false);
    }
}
