<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\ProductGrid;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use PHPUnit\Framework\Assert;
use Psr\Container\ContainerInterface;

final class ProductGridFixturesLoader
{
    /** @var  ContainerInterface */
    private $container;

    /** @var string */
    private $akeneoImagePath;

    /**
     * @param ContainerInterface $container
     * @param string             $akeneoImageKey
     */
    public function __construct(ContainerInterface $container, string $akeneoImageKey)
    {
        $this->container = $container;
        $this->akeneoImagePath = $akeneoImageKey;
    }

    public function createProductModelsWithLabelInProduct(): ProductModelInterface
    {
        $this->createFamilyVariant();
        $rootProductModelWithoutSubProductModel = $this->container->get('pim_catalog.factory.product_model')->create();
        $this->container->get('pim_catalog.updater.product_model')->update($rootProductModelWithoutSubProductModel, [
            'code' => 'root_product_model_without_sub_product_model',
            'family_variant' => 'family_variant_image_in_product',
            'values' => [
                'a_localizable_image' => [
                    ['data' => $this->akeneoImagePath, 'locale' => 'en_US', 'scope' => null],
                    ['data' => $this->akeneoImagePath, 'locale' => 'fr_FR', 'scope' => null],
                ],
                'a_scopable_image' => [
                    ['data' => $this->akeneoImagePath, 'locale' => null, 'scope' => 'ecommerce'],
                    ['data' => $this->akeneoImagePath, 'locale' => null, 'scope' => 'tablet'],
                ],
            ]
        ]);

        $errors = $this->container->get('pim_catalog.validator.product')->validate($rootProductModelWithoutSubProductModel);
        Assert::assertCount(0, $errors);
        $this->container->get('pim_catalog.saver.product_model')->save($rootProductModelWithoutSubProductModel);

        $product = $this->container->get('pim_catalog.builder.product')->createProduct('product_with_image', 'test_family');
        $this->container->get('pim_catalog.updater.product')->update($product, [
            'groups' => ['groupA', 'groupB'],
            'parent' => 'root_product_model_without_sub_product_model',
            'values' => [
                'a_yes_no' => [
                    ['data' => true, 'locale' => null, 'scope' => null]
                ],
                'an_image' => [
                    ['data' => $this->akeneoImagePath, 'locale' => null, 'scope' => null],
                ],
            ]
        ]);

        $errors = $this->container->get('validator')->validate($product);
        Assert::assertCount(0, $errors);

        $this->container->get('pim_catalog.saver.product')->save($product);

        return $rootProductModelWithoutSubProductModel;
    }

    public function createProductModelsWithLabelInParentProductModel()
    {
        $rootProductModelWithSubProductModel = $this->container->get('pim_catalog.factory.product_model')->create();
        $this->container->get('pim_catalog.updater.product_model')->update($rootProductModelWithSubProductModel, [
            'code' => 'root_product_model_with_sub_product_model',
            'family_variant' => 'family_variant_image_in_parent_product_model',
            'values' => [
                'an_image' => [
                    ['data' => $this->akeneoImagePath, 'locale' => null, 'scope' => null],
                ],
            ]
        ]);

        $errors = $this->container->get('pim_catalog.validator.product')->validate($rootProductModelWithSubProductModel);
        Assert::assertCount(0, $errors);
        $this->container->get('pim_catalog.saver.product_model')->save($rootProductModelWithSubProductModel);

        $subProductModel = $this->container->get('pim_catalog.factory.product_model')->create();
        $this->container->get('pim_catalog.updater.product_model')->update($subProductModel, [
            'code' => 'sub_product_model',
            'parent' => 'root_product_model_with_sub_product_model',
            'family_variant' => 'family_variant_image_in_parent_product_model',
            'values' => [
                'a_yes_no' => [
                    ['data' => true, 'locale' => null, 'scope' => null],
                ],
            ]
        ]);

        $errors = $this->container->get('pim_catalog.validator.product')->validate($subProductModel);
        Assert::assertCount(0, $errors);
        $this->container->get('pim_catalog.saver.product_model')->save($subProductModel);

        return $subProductModel;
    }

    public function createProductModelsWithLabelInSubProductModel()
    {
        $rootProductModelWithoutSubProductModel = $this->container->get('pim_catalog.factory.product_model')->create();
        $this->container->get('pim_catalog.updater.product_model')->update($rootProductModelWithoutSubProductModel, [
            'code' => 'root_product_model_with_image_in_sub_product_model',
            'family_variant' => 'family_variant_image_in_sub_product_model',
            'values' => []
        ]);

        $errors = $this->container->get('pim_catalog.validator.product')->validate($rootProductModelWithoutSubProductModel);
        Assert::assertCount(0, $errors);
        $this->container->get('pim_catalog.saver.product_model')->save($rootProductModelWithoutSubProductModel);

        $subProductModel = $this->container->get('pim_catalog.factory.product_model')->create();
        $this->container->get('pim_catalog.updater.product_model')->update($subProductModel, [
            'code' => 'sub_product_model_with_image_in_sub_product_model',
            'parent' => 'root_product_model_with_image_in_sub_product_model',
            'family_variant' => 'family_variant_image_in_sub_product_model',
            'values' => [
                'a_yes_no' => [
                    ['data' => true, 'locale' => null, 'scope' => null],
                ],
                'an_image' => [
                    ['data' => $this->akeneoImagePath, 'locale' => null, 'scope' => null],
                ],
            ]
        ]);

        $errors = $this->container->get('pim_catalog.validator.product')->validate($subProductModel);
        Assert::assertCount(0, $errors);
        $this->container->get('pim_catalog.saver.product_model')->save($subProductModel);

        return $rootProductModelWithoutSubProductModel;
    }

    public function createProductAndProductModels()
    {
        return [
            'product_models' => $this->createProductModels(),
            'products' => $this->createProducts()
        ];
    }

    private function createProductModels() : array
    {
        $rootProductModel = $this->container->get('pim_catalog.factory.product_model')->create();
        $this->container->get('pim_catalog.updater.product_model')->update($rootProductModel, [
            'code' => 'root_product_model',
            'family_variant' => 'familyVariantA1',
            'values' => [
                'an_image' => [
                    ['data' => $this->akeneoImagePath, 'locale' => null, 'scope' => null],
                ],
                'a_number_integer' => [
                    ['locale' => null, 'scope'  => null, 'data'   => 10],
                ],
            ]
        ]);

        $errors = $this->container->get('pim_catalog.validator.product')->validate($rootProductModel);
        Assert::assertCount(0, $errors);
        $this->container->get('pim_catalog.saver.product_model')->save($rootProductModel);

        $subProductModel = $this->container->get('pim_catalog.factory.product_model')->create();
        $this->container->get('pim_catalog.updater.product_model')->update($subProductModel, [
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

        $errors = $this->container->get('pim_catalog.validator.product')->validate($subProductModel);
        Assert::assertCount(0, $errors);
        $this->container->get('pim_catalog.saver.product_model')->save($subProductModel);

        return [$rootProductModel, $subProductModel];
    }

    private function createProducts(): array
    {
        $product1 = $this->container->get('pim_catalog.builder.product')->createProduct('foo', 'familyA');
        $this->container->get('pim_catalog.updater.product')->update($product1, [
            'groups' => ['groupA', 'groupB'],
            'parent' => 'sub_product_model',
            'values' => [
                'a_yes_no' => [
                    ['data' => true, 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $product2 = $this->container->get('pim_catalog.builder.product')->createProduct('baz', null);
        $this->container->get('pim_catalog.updater.product')->update($product2, [
            'values' => [
                'a_localizable_image' => [
                    ['data' => $this->akeneoImagePath, 'locale' => 'en_US', 'scope' => null],
                    ['data' => $this->akeneoImagePath, 'locale' => 'fr_FR', 'scope' => null],
                ],
                'a_scopable_image' => [
                    ['data' => $this->akeneoImagePath, 'locale' => null, 'scope' => 'ecommerce'],
                    ['data' => $this->akeneoImagePath, 'locale' => null, 'scope' => 'tablet'],
                ],
                'a_yes_no' => [
                    ['data' => true, 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $errors = $this->container->get('validator')->validate($product1);
        Assert::assertCount(0, $errors);
        $errors = $this->container->get('validator')->validate($product2);
        Assert::assertCount(0, $errors);

        $this->container->get('pim_catalog.saver.product')->saveAll([$product1, $product2]);

        return [$product1, $product2];
    }

    private function createFamilyVariant(): void
    {
        $family = $this->container->get('pim_catalog.factory.family')->create();
        $this->container->get('pim_catalog.updater.family')->update($family, [
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

        $errors = $this->container->get('validator')->validate($family);
        Assert::assertCount(0, $errors);
        $this->container->get('pim_catalog.saver.family')->save($family);

        $familyVariant = $this->container->get('pim_catalog.factory.family_variant')->create();
        $this->container->get('pim_catalog.updater.family_variant')->update($familyVariant, [
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

        $errors = $this->container->get('validator')->validate($familyVariant);
        Assert::assertCount(0, $errors);
        $this->container->get('pim_catalog.saver.family_variant')->save($familyVariant);

        $familyVariant = $this->container->get('pim_catalog.factory.family_variant')->create();
        $this->container->get('pim_catalog.updater.family_variant')->update($familyVariant, [
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

        $errors = $this->container->get('validator')->validate($familyVariant);
        Assert::assertCount(0, $errors);
        $this->container->get('pim_catalog.saver.family_variant')->save($familyVariant);

        $familyVariant = $this->container->get('pim_catalog.factory.family_variant')->create();
        $this->container->get('pim_catalog.updater.family_variant')->update($familyVariant, [
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

        $errors = $this->container->get('validator')->validate($familyVariant);
        Assert::assertCount(0, $errors);
        $this->container->get('pim_catalog.saver.family_variant')->save($familyVariant);
    }
}
