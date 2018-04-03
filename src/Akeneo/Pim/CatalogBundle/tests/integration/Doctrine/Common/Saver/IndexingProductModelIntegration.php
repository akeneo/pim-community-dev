<?php

namespace tests\integration\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Model\ProductModelInterface;

/**
 * Test product models have been correctly indexed after being saved.
 */
class IndexingProductModelIntegration extends TestCase
{
    private const DOCUMENT_TYPE = 'pim_catalog_product';

    /** @var Client */
    private $esProductAndProductModelClient;

    /** @var Client */
    private $esProductModelClient;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->esProductAndProductModelClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
        $this->esProductModelClient = $this->get('akeneo_elasticsearch.client.product_model');
    }

    public function testIndexingProductModelsOnBulkSave()
    {
        $productModels = [];
        foreach (['foo', 'bar', 'baz'] as $identifier) {
            $productModels[] = $this->createProductModel($identifier);
        }

        $this->get('pim_catalog.saver.product_model')->saveAll($productModels);

        $productModelRepository = $this->get('pim_catalog.repository.product_model');
        $productModelFooESId = $productModelRepository->findOneByIdentifier('foo')->getId();
        $productModelBarESId = $productModelRepository->findOneByIdentifier('bar')->getId();
        $productModelBazESId = $productModelRepository->findOneByIdentifier('baz')->getId();

        $indexedProductModelFoo = $this->esProductModelClient->get(self::DOCUMENT_TYPE, $productModelFooESId);
        $this->assertTrue($indexedProductModelFoo['found']);

        $indexedProductModelBar = $this->esProductModelClient->get(self::DOCUMENT_TYPE, $productModelBarESId);
        $this->assertTrue($indexedProductModelBar['found']);

        $indexedProductModelBaz = $this->esProductModelClient->get(self::DOCUMENT_TYPE, $productModelBazESId);
        $this->assertTrue($indexedProductModelBaz['found']);

        $indexedProductModelFoo = $this->esProductAndProductModelClient->get(self::DOCUMENT_TYPE, 'product_model_' . $productModelFooESId);
        $this->assertTrue($indexedProductModelFoo['found']);

        $indexedProductModelBar = $this->esProductAndProductModelClient->get(self::DOCUMENT_TYPE, 'product_model_' . $productModelBarESId);
        $this->assertTrue($indexedProductModelBar['found']);

        $indexedProductModelBaz = $this->esProductAndProductModelClient->get(self::DOCUMENT_TYPE, 'product_model_' . $productModelBazESId);
        $this->assertTrue($indexedProductModelBaz['found']);
    }

    public function testIndexingProductModelOnUnitarySave()
    {
        $productModel = $this->createProductModel('bat');
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $productBatESId = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('bat')->getId();

        $indexedProductModelFoo = $this->esProductModelClient->get(self::DOCUMENT_TYPE, $productBatESId);
        $this->assertTrue($indexedProductModelFoo['found']);

        $indexedProduct = $this->esProductAndProductModelClient->get(self::DOCUMENT_TYPE, 'product_model_' . $productBatESId);
        $this->assertTrue($indexedProduct['found']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
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
