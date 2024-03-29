<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Classification;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Test\Integration\TestCase;

class ClassifyProductModelIntegration extends TestCase
{
    public function testClassify()
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('model-tee');
        $this->assertCount(1, $productModel->getCategories());
        $category = $productModel->getCategories()->first();
        $this->assertEquals($category->getCode(), 'supplier_zaro');
    }

    public function testClassifyProductModelAlsoClassifiesChildrenProductModels()
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('model-tee-red');
        $this->assertCount(1, $productModel->getCategories());
        $category = $productModel->getCategories()->first();
        $this->assertEquals($category->getCode(), 'supplier_zaro');
    }

    public function testClassifyChildrenProductModelDoesNotImpactItsParent()
    {
        $childrenProductModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('model-tee-red');
        $this->get('pim_catalog.updater.product_model')->update($childrenProductModel, ['categories' => ['master_women_shirts']]);
        $this->get('pim_catalog.validator.product_model')->validate($childrenProductModel);
        $this->get('pim_catalog.saver.product_model')->save($childrenProductModel);

        $parentProductModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('model-tee');
        $this->assertCount(1, $parentProductModel->getCategories());
        $category = $parentProductModel->getCategories()->first();
        $this->assertEquals($category->getCode(), 'supplier_zaro');
    }

    public function testClassifyWithSameCategoryThanParentHasNoImpactOnChldrenProductModel()
    {
        $childrenProductModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('model-tee-red');
        $this->get('pim_catalog.updater.product_model')->update($childrenProductModel, ['categories' => ['supplier_zaro']]);
        $this->get('pim_catalog.validator.product_model')->validate($childrenProductModel);
        $this->get('pim_catalog.saver.product_model')->save($childrenProductModel);

        $this->assertCount(1, $childrenProductModel->getCategories());
        $category = $childrenProductModel->getCategories()->first();
        $this->assertEquals($category->getCode(), 'supplier_zaro');
    }

    public function testUnclassifyChildrenProductModelDoesNotImpactItsParent()
    {
        $childrenProductModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('model-tee-red');
        $this->get('pim_catalog.updater.product_model')->update($childrenProductModel, ['categories' => []]);
        $this->get('pim_catalog.validator.product_model')->validate($childrenProductModel);
        $this->get('pim_catalog.saver.product_model')->save($childrenProductModel);

        $parentProductModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('model-tee');
        $this->assertCount(1, $parentProductModel->getCategories());
        $category = $parentProductModel->getCategories()->first();
        $this->assertEquals($category->getCode(), 'supplier_zaro');
    }

    public function testClassifyProductModelAlsoClassifiesChildrenProducts()
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('tee-red-s');
        $this->assertCount(1, $product->getCategories());
        $category = $product->getCategories()->first();
        $this->assertEquals($category->getCode(), 'supplier_zaro');
    }

    public function testClassifyChildrenProductDoesNotImpactItsParent()
    {
        $this->createProduct('tee-red-s', [new SetCategories(['master_women_shirts'])]);

        $parentProductModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('model-tee-red');
        $this->assertCount(1, $parentProductModel->getCategories());
        $category = $parentProductModel->getCategories()->first();
        $this->assertEquals($category->getCode(), 'supplier_zaro');
    }

    public function testClassifyWithSameCategoryThanParentHasNoImpactOnChildrenProduct()
    {
        $this->createProduct('tee-red-s', [new SetCategories(['supplier_zaro'])]);
        $childrenProduct = $this->get('pim_catalog.repository.product')->findOneByIdentifier('tee-red-s');

        $this->assertCount(1, $childrenProduct->getCategories());
        $category = $childrenProduct->getCategories()->first();
        $this->assertEquals($category->getCode(), 'supplier_zaro');
    }

    public function testUnclassifyChildrenProductDoesNotImpactItsParents()
    {
        $this->createProduct('tee-red-s', [new SetCategories([])]);

        $parentProductModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('model-tee');
        $this->assertCount(1, $parentProductModel->getCategories());
        $category = $parentProductModel->getCategories()->first();
        $this->assertEquals($category->getCode(), 'supplier_zaro');

        $grandParentProductModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('model-tee-red');
        $this->assertCount(1, $grandParentProductModel->getCategories());
        $category = $grandParentProductModel->getCategories()->first();
        $this->assertEquals($category->getCode(), 'supplier_zaro');
    }

    public function setUp(): void
    {
        parent::setUp();

        $builder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');

        $productModel = $builder->createProductModel('model-tee', 'clothing_color_size', null, []);
        $this->get('pim_catalog.updater.product_model')->update($productModel, ['categories' => ['supplier_zaro']]);
        $this->get('pim_catalog.validator.product_model')->validate($productModel);
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $subProductModel = $builder->createProductModel(
            'model-tee-red',
            'clothing_color_size',
            $productModel,
            ['values' => ['color' => [['data' => 'red', 'locale' => null, 'scope' => null]]]]
        );
        $this->get('pim_catalog.validator.product_model')->validate($subProductModel);
        $this->get('pim_catalog.saver.product_model')->save($subProductModel);

        $variantProduct = $builder->createVariantProduct(
            'tee-red-s',
            'clothing',
            'clothing_color_size',
            $subProductModel,
            ['values' => ['size' => [['data' => 's', 'locale' => null, 'scope' => null]]]]
        );
        $this->get('pim_catalog.validator.product')->validate($variantProduct);
        $this->get('pim_catalog.saver.product')->save($variantProduct);

        $this->get('akeneo_integration_tests.launcher.job_launcher')->launchConsumerUntilQueueIsEmpty();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    /**
     * @param UserIntent[] $userIntents
     */
    protected function createProduct(string $identifier, array $userIntents): ProductInterface
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: $identifier,
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }
}
