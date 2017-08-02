<?php

namespace tests\integration\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Model\ProductModelInterface;

/**
 * Test product models have been correctly indexed after being saved.
 */
class IndexingProductModelIntegration extends TestCase
{
    private const DOCUMENT_TYPE = 'pim_catalog_product';

    public function testIndexingProductsOnBulkSave()
    {
        $productModels = [];
        foreach (['foo', 'bar', 'baz'] as $identifier) {
            $productModels[] = $this->createProductModel($identifier);
        }

        $this->get('pim_catalog.saver.product_model')->saveAll($productModels);

        $indexedProductFoo = $this->esClient->get(self::DOCUMENT_TYPE, 'foo');
        $this->assertTrue($indexedProductFoo['found']);

        $indexedProductBar = $this->esClient->get(self::DOCUMENT_TYPE, 'bar');
        $this->assertTrue($indexedProductBar['found']);

        $indexedProductBaz = $this->esClient->get(self::DOCUMENT_TYPE, 'baz');
        $this->assertTrue($indexedProductBaz['found']);
    }

    public function testIndexingProductOnUnitarySave()
    {
        $product = $this->createProductModel('bat');
        $this->get('pim_catalog.saver.product_model')->save($product);

        $indexedProduct = $this->esClient->get(self::DOCUMENT_TYPE, 'bat');
        $this->assertTrue($indexedProduct['found']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getTechnicalCatalogPath()], false);
    }

    /**
     * @param string $identifier
     *
     * @return ProductModelInterface
     */
    private function createProductModel(string $identifier): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, ['identifier' => $identifier]);

        return $productModel;
    }
}
