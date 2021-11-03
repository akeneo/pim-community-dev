<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Doctrine\ORM\Repository;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use AkeneoTest\Pim\Enrichment\Integration\Fixture\EntityBuilder;
use PHPUnit\Framework\Assert;

class ProductModelRepositoryIntegration extends TestCase
{
    public function testCanFindFirstCreatedVariantProductModel()
    {
        $productModel = $this->createProductModel();
        $this->createVariantProductModels($productModel);

        $productModelChild = $this->get('pim_catalog.repository.product_model')->findFirstCreatedVariantProductModel($productModel);
        $this->assertInstanceOf(ProductModelInterface::class, $productModelChild);
        $this->assertEquals('a_variant_product_model', $productModelChild->getCode());
    }

    public function testItReturnNullWhenProductModelDoesNotHaveChildren()
    {
        $productModel = $this->createProductModel();

        $productModelChild = $this->getProductModelRepository()->findFirstCreatedVariantProductModel($productModel);
        $this->assertNull($productModelChild);
    }

    /** @test */
    public function findProductModelsForFamilyVariantWithSearchAndPagination(): void
    {
        $productModel = $this->createProductModel();
        $this->createVariantProductModels($productModel);

        $familyVariant = $this->get('pim_catalog.repository.family_variant')->findOneByCode('familyVariantA2');

        $productModels = $this->getProductModelRepository()->findProductModelsForFamilyVariant(
            $familyVariant,
            null,
            3,
            1
        );
        Assert::assertCount(3, $productModels);
        $productModelCodes = array_map(
            fn (ProductModelInterface $productModel): string => $productModel->getCode(),
            $productModels
        );
        Assert::assertEqualsCanonicalizing(
            ['a_product_model', 'a_variant_product_model', 'another_variant_product_model'],
            $productModelCodes
        );

        $productModels = $this->getProductModelRepository()->findProductModelsForFamilyVariant(
            $familyVariant,
            'another',
            3,
            1
        );
        Assert::assertCount(1, $productModels);
        $productModelCodes = array_map(
            fn (ProductModelInterface $productModel): string => $productModel->getCode(),
            $productModels
        );
        Assert::assertEqualsCanonicalizing(['another_variant_product_model'], $productModelCodes);

        $productModels = $this->getProductModelRepository()->findProductModelsForFamilyVariant(
            $familyVariant,
            null,
            1,
            1
        );
        Assert::assertCount(1, $productModels);

        $productModels = $this->getProductModelRepository()->findProductModelsForFamilyVariant(
            $familyVariant,
            null,
            1,
            5
        );
        Assert::assertCount(0, $productModels);
    }

    public function getProductModelRepository(): ProductModelRepositoryInterface
    {
        return $this->get('pim_catalog.repository.product_model');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createProductModel(): ProductModelInterface
    {
        $entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');

        return $entityBuilder->createProductModel('a_product_model', 'familyVariantA2', null, []);
    }

    private function createVariantProductModels(ProductModelInterface $productModel): void
    {
        /** @var EntityBuilder $entityBuilder */
        $entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');
        $entityBuilder->createProductModel(
            'a_variant_product_model',
            'familyVariantA2',
            $productModel,
            [
                'values' => [
                    'a_simple_select' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'optionA',
                        ],
                    ],
                    'a_yes_no' =>  [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => true,
                        ],
                    ],
                ],
                'categories' => ['categoryA1'],
            ]
        );

        $entityBuilder->createProductModel(
            'another_variant_product_model',
            'familyVariantA2',
            $productModel,
            [
                'values' => [
                    'a_simple_select' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'optionA',
                        ],
                    ],
                    'a_yes_no' =>  [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => true,
                        ],
                    ],
                ],
                'categories' => ['categoryB'],
            ]
        );
    }
}
