<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\ProductGrid;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ProductGridFixturesLoader
{
    public function __construct(
        private readonly SimpleFactoryInterface $productModelFactory,
        private readonly ObjectUpdaterInterface $productModelUpdater,
        private readonly ValidatorInterface $productValidator,
        private readonly SaverInterface $productModelSaver,
        private readonly ProductBuilderInterface $productBuilder,
        private readonly ObjectUpdaterInterface $productUpdater,
        private readonly SaverInterface $productSaver,
        private readonly BulkSaverInterface $productsSaver,
        private readonly SimpleFactoryInterface $familyFactory,
        private readonly ObjectUpdaterInterface $familyUpdater,
        private readonly SaverInterface $familySaver,
        private readonly ValidatorInterface $validator,
        private readonly SimpleFactoryInterface $familyVariantFactory,
        private readonly ObjectUpdaterInterface $familyVariantUpdater,
        private readonly SaverInterface $familyVariantSaver,
        private readonly Client $esClient
    ) {
    }

    public function createProductModelsWithLabelInProduct(string $akeneoImagePath): ProductModelInterface
    {
        $this->createFamilyVariant();
        $rootProductModelWithoutSubProductModel = $this->productModelFactory->create();
        $this->productModelUpdater->update($rootProductModelWithoutSubProductModel, [
            'code' => 'root_product_model_without_sub_product_model',
            'family_variant' => 'family_variant_image_in_product',
            'values' => [
                'a_localizable_image' => [
                    ['data' => $akeneoImagePath, 'locale' => 'en_US', 'scope' => null],
                    ['data' => $akeneoImagePath, 'locale' => 'fr_FR', 'scope' => null],
                ],
                'a_scopable_image' => [
                    ['data' => $akeneoImagePath, 'locale' => null, 'scope' => 'ecommerce'],
                    ['data' => $akeneoImagePath, 'locale' => null, 'scope' => 'tablet'],
                ],
            ]
        ]);

        $errors = $this->productValidator->validate($rootProductModelWithoutSubProductModel);
        Assert::assertCount(0, $errors);
        $this->productModelSaver->save($rootProductModelWithoutSubProductModel);

        $product = $this->productBuilder->createProduct('product_with_image', 'test_family');
        $this->productUpdater->update($product, [
            'groups' => ['groupA', 'groupB'],
            'parent' => 'root_product_model_without_sub_product_model',
            'values' => [
                'a_yes_no' => [
                    ['data' => true, 'locale' => null, 'scope' => null]
                ],
                'an_image' => [
                    ['data' => $akeneoImagePath, 'locale' => null, 'scope' => null],
                ],
            ]
        ]);

        $errors = $this->productValidator->validate($product);
        Assert::assertCount(0, $errors);

        $this->productSaver->save($product);
        $this->refreshEsIndex();

        return $rootProductModelWithoutSubProductModel;
    }

    public function createProductModelsWithLabelInParentProductModel(string $akeneoImagePath)
    {
        $rootProductModelWithSubProductModel = $this->productModelFactory->create();
        $this->productModelUpdater->update($rootProductModelWithSubProductModel, [
            'code' => 'root_product_model_with_sub_product_model',
            'family_variant' => 'family_variant_image_in_parent_product_model',
            'values' => [
                'an_image' => [
                    ['data' => $akeneoImagePath, 'locale' => null, 'scope' => null],
                ],
            ]
        ]);

        $errors = $this->productValidator->validate($rootProductModelWithSubProductModel);
        Assert::assertCount(0, $errors);
        $this->productModelSaver->save($rootProductModelWithSubProductModel);

        $subProductModel = $this->productModelFactory->create();
        $this->productModelUpdater->update($subProductModel, [
            'code' => 'sub_product_model',
            'parent' => 'root_product_model_with_sub_product_model',
            'family_variant' => 'family_variant_image_in_parent_product_model',
            'values' => [
                'a_yes_no' => [
                    ['data' => true, 'locale' => null, 'scope' => null],
                ],
            ]
        ]);

        $errors = $this->productValidator->validate($subProductModel);
        Assert::assertCount(0, $errors);
        $this->productModelSaver->save($subProductModel);
        $this->refreshEsIndex();

        return $subProductModel;
    }

    public function createProductModelsWithLabelInSubProductModel(string $akeneoImagePath)
    {
        $rootProductModelWithoutSubProductModel = $this->productModelFactory->create();
        $this->productModelUpdater->update($rootProductModelWithoutSubProductModel, [
            'code' => 'root_product_model_with_image_in_sub_product_model',
            'family_variant' => 'family_variant_image_in_sub_product_model',
            'values' => []
        ]);

        $errors = $this->productValidator->validate($rootProductModelWithoutSubProductModel);
        Assert::assertCount(0, $errors);
        $this->productModelSaver->save($rootProductModelWithoutSubProductModel);

        $subProductModel = $this->productModelFactory->create();
        $this->productModelUpdater->update($subProductModel, [
            'code' => 'sub_product_model_with_image_in_sub_product_model',
            'parent' => 'root_product_model_with_image_in_sub_product_model',
            'family_variant' => 'family_variant_image_in_sub_product_model',
            'values' => [
                'a_yes_no' => [
                    ['data' => true, 'locale' => null, 'scope' => null],
                ],
                'an_image' => [
                    ['data' => $akeneoImagePath, 'locale' => null, 'scope' => null],
                ],
            ]
        ]);

        $errors = $this->productValidator->validate($subProductModel);
        Assert::assertCount(0, $errors);
        $this->productModelSaver->save($subProductModel);
        $this->refreshEsIndex();

        return $rootProductModelWithoutSubProductModel;
    }

    public function createProductAndProductModels(string $akeneoImagePath)
    {
        $fixtures = [
            'product_models' => $this->createProductModels($akeneoImagePath),
            'products' => $this->createProducts($akeneoImagePath)
        ];

        $this->refreshEsIndex();

        return $fixtures;
    }

    private function createProductModels(string $akeneoImagePath) : array
    {
        $rootProductModel = $this->productModelFactory->create();
        $this->productModelUpdater->update($rootProductModel, [
            'code' => 'root_product_model',
            'family_variant' => 'familyVariantA1',
            'values' => [
                'an_image' => [
                    ['data' => $akeneoImagePath, 'locale' => null, 'scope' => null],
                ],
                'a_number_integer' => [
                    ['locale' => null, 'scope'  => null, 'data'   => 10],
                ],
            ]
        ]);

        $errors = $this->productValidator->validate($rootProductModel);
        Assert::assertCount(0, $errors);
        $this->productModelSaver->save($rootProductModel);

        $subProductModel = $this->productModelFactory->create();
        $this->productModelUpdater->update($subProductModel, [
            'code' => 'sub_product_model',
            'parent' => 'root_product_model',
            'family_variant' => 'familyVariantA1',
            'values' => [
                'a_text' => [
                    ['locale' => null, 'scope'  => null, 'data'   => 'text'],
                ],
                'a_simple_select' => [
                    ['locale' => null, 'scope'  => null, 'data'   => 'optionB'],
                ],
            ]
        ]);

        $errors = $this->productValidator->validate($subProductModel);
        Assert::assertCount(0, $errors);
        $this->productModelSaver->save($subProductModel);

        return [$rootProductModel, $subProductModel];
    }

    private function createProducts(string $akeneoImagePath): array
    {
        $product1 = $this->productBuilder->createProduct('foo', 'familyA');
        $this->productUpdater->update($product1, [
            'groups' => ['groupA', 'groupB'],
            'parent' => 'sub_product_model',
            'values' => [
                'a_yes_no' => [
                    ['data' => true, 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $product2 = $this->productBuilder->createProduct('baz', null);
        $this->productUpdater->update($product2, [
            'values' => [
                'a_localizable_image' => [
                    ['data' => $akeneoImagePath, 'locale' => 'en_US', 'scope' => null],
                    ['data' => $akeneoImagePath, 'locale' => 'fr_FR', 'scope' => null],
                ],
                'a_scopable_image' => [
                    ['data' => $akeneoImagePath, 'locale' => null, 'scope' => 'ecommerce'],
                    ['data' => $akeneoImagePath, 'locale' => null, 'scope' => 'tablet'],
                ],
                'a_yes_no' => [
                    ['data' => true, 'locale' => null, 'scope' => null]
                ]
            ]
        ]);


        $product3 = $this->productBuilder->createProduct(null, null, '2c29af3c-625b-4ee0-9ba4-c2783c300b92');
        $this->productUpdater->update($product3, [
            'values' => [
                'a_yes_no' => [
                    ['data' => true, 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $errors = $this->productValidator->validate($product1);
        Assert::assertCount(0, $errors);
        $errors = $this->productValidator->validate($product2);
        Assert::assertCount(0, $errors);
        $errors = $this->productValidator->validate($product3);
        Assert::assertCount(0, $errors);

        $this->productsSaver->saveAll([$product1, $product2, $product3]);

        return [$product1, $product2, $product3];
    }

    private function createFamilyVariant(): void
    {
        $family = $this->familyFactory->create();
        $this->familyUpdater->update($family, [
            'code' => 'test_family',
            'attributes'  => [
                'sku',
                'an_image',
                'a_yes_no',
                'a_simple_select_size',
                'a_localizable_image',
                'a_scopable_image',
            ],
            "attribute_as_image" => "an_image"
        ]);

        $errors = $this->validator->validate($family);
        Assert::assertCount(0, $errors);
        $this->familySaver->save($family);

        $familyVariant = $this->familyVariantFactory->create();
        $this->familyVariantUpdater->update($familyVariant, [
            'code' => 'family_variant_image_in_product',
            'family' => 'test_family',
            'variant_attribute_sets' => [
                [
                    'axes' => ['a_yes_no'],
                    'attributes' => ['an_image'],
                    'level'=> 1,
                ]
            ],
        ]);

        $errors = $this->validator->validate($familyVariant);
        Assert::assertCount(0, $errors);
        $this->familyVariantSaver->save($familyVariant);

        $familyVariant = $this->familyVariantFactory->create();
        $this->familyVariantUpdater->update($familyVariant, [
            'code' => 'family_variant_image_in_parent_product_model',
            'family' => 'test_family',
            'variant_attribute_sets' => [
                [
                    'axes' => ['a_yes_no'],
                    'attributes' => [],
                    'level'=> 1
                ],
                [
                    'axes' => ['a_simple_select_size'],
                    'attributes' => [],
                    'level'=> 2
                ]
            ],
        ]);

        $errors = $this->validator->validate($familyVariant);
        Assert::assertCount(0, $errors);
        $this->familyVariantSaver->save($familyVariant);

        $familyVariant = $this->familyVariantFactory->create();
        $this->familyVariantUpdater->update($familyVariant, [
            'code' => 'family_variant_image_in_sub_product_model',
            'family' => 'test_family',
            'variant_attribute_sets' => [
                [
                    'axes' => ['a_yes_no'],
                    'attributes' => ['an_image'],
                    'level'=> 1
                ],
                [
                    'axes' => ['a_simple_select_size'],
                    'attributes' => [],
                    'level'=> 2
                ]
            ],
        ]);

        $errors = $this->validator->validate($familyVariant);
        Assert::assertCount(0, $errors);
        $this->familyVariantSaver->save($familyVariant);
    }

    private function refreshEsIndex(): void
    {
        $this->esClient->refreshIndex();
    }
}
