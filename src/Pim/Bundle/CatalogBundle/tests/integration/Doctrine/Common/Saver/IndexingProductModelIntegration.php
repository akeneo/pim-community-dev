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

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->esProductAndProductModelClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
    }

    public function testIndexingProductModelsOnBulkSave()
    {
        $productModels = [];
        foreach (['foo', 'bar', 'baz'] as $identifier) {
            $productModels[] = $this->createProductModel($identifier);
        }

        $this->get('pim_catalog.saver.product_model')->saveAll($productModels);

        $productModelRepository = $this->get('pim_catalog.repository.product_model');
        $productModelFooESId = 'product_model_' . $productModelRepository->findOneByIdentifier('foo')->getId();
        $productModelBarESId = 'product_model_' . $productModelRepository->findOneByIdentifier('bar')->getId();
        $productModelBazESId = 'product_model_' . $productModelRepository->findOneByIdentifier('baz')->getId();

        $indexedProductModelFoo = $this->esProductAndProductModelClient->get(self::DOCUMENT_TYPE, $productModelFooESId);
        $this->assertTrue($indexedProductModelFoo['found']);

        $indexedProductModelBar = $this->esProductAndProductModelClient->get(self::DOCUMENT_TYPE, $productModelBarESId);
        $this->assertTrue($indexedProductModelBar['found']);

        $indexedProductModelBaz = $this->esProductAndProductModelClient->get(self::DOCUMENT_TYPE, $productModelBazESId);
        $this->assertTrue($indexedProductModelBaz['found']);
    }

    public function testIndexingProductModelOnUnitarySave()
    {
        $productModel = $this->createProductModel('bat');
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $productBatESId = 'product_model_' . $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('bat')->getId();

        $indexedProduct = $this->esProductAndProductModelClient->get(self::DOCUMENT_TYPE, $productBatESId);
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
