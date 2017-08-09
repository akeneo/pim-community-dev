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

    /** @group todo */
    public function testIndexingProductsOnBulkSave()
    {
        $productModels = [];
        foreach (['foo', 'bar', 'baz'] as $identifier) {
            $productModels[] = $this->createProductModel($identifier);
        }

        $this->get('pim_catalog.saver.product_model')->saveAll($productModels);

        $productModelRepository = $this->get('pim_catalog.repository.product_model');
        $productFooId = $productModelRepository->findOneByIdentifier('foo')->getId();
        $productBarId = $productModelRepository->findOneByIdentifier('bar')->getId();
        $productBazId = $productModelRepository->findOneByIdentifier('baz')->getId();

        $indexedProductFoo = $this->esProductAndProductModelClient->get(self::DOCUMENT_TYPE, $productFooId);
        $this->assertTrue($indexedProductFoo['found']);

        $indexedProductBar = $this->esProductAndProductModelClient->get(self::DOCUMENT_TYPE, $productBarId);
        $this->assertTrue($indexedProductBar['found']);

        $indexedProductBaz = $this->esProductAndProductModelClient->get(self::DOCUMENT_TYPE, $productBazId);
        $this->assertTrue($indexedProductBaz['found']);
    }

    public function testIndexingProductOnUnitarySave()
    {
        $product = $this->createProductModel('bat');
        $this->get('pim_catalog.saver.product_model')->save($product);

        $indexedProduct = $this->esProductAndProductModelClient->get(self::DOCUMENT_TYPE, 'bat');
        $this->assertTrue($indexedProduct['found']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getTechnicalCatalogPath()]);
    }

    /**
     * @param string $identifier
     *
     * @return ProductModelInterface
     */
    private function createProductModel(string $identifier): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update(
            $productModel,
            [
                'code' => $identifier,
                'family_variant' => 'familyVariantA1'
            ]
        );

        return $productModel;
    }
}
