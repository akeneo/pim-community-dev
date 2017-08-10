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

    public function testIndexingProductModelsOnBulkSave()
    {
        $productModels = [];
        foreach (['foo', 'bar', 'baz'] as $identifier) {
            $productModels[] = $this->createProductModel($identifier);
        }

        $this->get('pim_catalog.saver.product_model')->saveAll($productModels);

        $productModelRepository = $this->get('pim_catalog.repository.product_model');
        $productModelFooId = $productModelRepository->findOneByIdentifier('foo')->getId();
        $productModelBarId = $productModelRepository->findOneByIdentifier('bar')->getId();
        $productModelBazId = $productModelRepository->findOneByIdentifier('baz')->getId();

        $indexedProductModelFoo = $this->esProductAndProductModelClient->get(self::DOCUMENT_TYPE, $productModelFooId);
        $this->assertTrue($indexedProductModelFoo['found']);

        $indexedProductModelBar = $this->esProductAndProductModelClient->get(self::DOCUMENT_TYPE, $productModelBarId);
        $this->assertTrue($indexedProductModelBar['found']);

        $indexedProductModelBaz = $this->esProductAndProductModelClient->get(self::DOCUMENT_TYPE, $productModelBazId);
        $this->assertTrue($indexedProductModelBaz['found']);
    }

    public function testIndexingProductModelOnUnitarySave()
    {
        $product = $this->createProductModel('bat');
        $this->get('pim_catalog.saver.product_model')->save($product);

        $productBatId = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('bat')->getId();

        $indexedProduct = $this->esProductAndProductModelClient->get(self::DOCUMENT_TYPE, $productBatId);
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
