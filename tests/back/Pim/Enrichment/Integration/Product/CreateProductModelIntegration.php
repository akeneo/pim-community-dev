<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateProductModelIntegration extends TestCase
{
    /**
     * Create a product without any errors
     */
    public function testTheProductModelCreation()
    {
        $productModel = $this->createProductModel(
            [
                'code' => 'product_model_code',
                'values' => [
                    'description' => [
                        [
                            'locale' => 'fr_FR',
                            'scope' => 'mobile',
                            'data' => 'T-shirt super beau',
                        ],
                    ],
                ],
                'family_variant' => 'clothing_color_size',
                'categories' => ['tshirts'],
            ]
        );

        $errors = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        $this->assertEquals(0, $errors->count());

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $this->assertNotNull(
            $productModel,
            'The product model with the code "product_model_code" does not exist'
        );

        $this->assertEquals($productModel->getCategoryCodes(), ['tshirts']);

        $sku = $productModel->getValues()->first();
        $this->assertEquals($sku->getLocaleCode(), 'fr_FR');
        $this->assertEquals($sku->getScopeCode(), 'mobile');
        $this->assertEquals($sku->getData(), 'T-shirt super beau');
    }

    /**
     * Basic validation, a product model code must not be empty
     */
    public function testThatTheProductModelCodeMustNotBeEmpty()
    {
        $productModel = $this->createProductModel(
            [
                'code' => '',
            ]
        );

        $errors = $this->get('pim_catalog.validator.product_model')->validate($productModel);

        $this->assertEquals('The product model code must not be empty.', $errors->get(0)->getMessage());
        $this->assertEquals('code', $errors->get(0)->getPropertyPath());
    }

    /**
     * Basic validation, a product model code must be valid
     */
    public function testThatTheProductModelCodeMustBeValid()
    {
        $productModel = $this->createProductModel(
            [
                'code' => 'product_model_code',
                'family_variant' => 'clothing_color_size',
            ]
        );

        $errors = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        $this->assertEquals(0, count($errors));

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $productModel = $this->createProductModel(
            [
                'code' => 'product_model_code',
            ]
        );

        $errors = $this->get('pim_catalog.validator.product_model')->validate($productModel);

        $this->assertEquals(
            'The same code is already set on another product model.',
            $errors->get(0)->getMessage()
        );
        $this->assertEquals('code', $errors->get(0)->getPropertyPath());
    }

    /**
     * Family variant validation: A product model cannot be constructed without a family variant
     */
    public function testTheProductModelValidityDependingOnItsFamily()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Property "family_variant" expects a valid family variant code. The family variant does not exist, "" given.');

        $this->createProductModel(
            [
                'code' => 'product_model_code',
                'values' => [
                    'name' => [
                        [
                            'locale' => 'fr_FR',
                            'scope' => null,
                            'data' => 'T-shirt super beau',
                        ],
                    ],
                ],
                'family_variant' => '',
                'categories' => ['tshirts'],
            ]
        );
    }

    /**
     * Advanced validation, a product model axis must be filled
     */
    public function testTheProductModelAxisValueIsSet()
    {
        $productModelParent = $this->createProductModel(
            [
                'code' => 'product_model_parent_code',
                'family_variant' => 'clothing_color_size',
            ]
        );

        $errors = $this->get('pim_catalog.validator.product_model')->validate($productModelParent);
        $this->assertEquals(0, $errors->count());
        $this->get('pim_catalog.saver.product_model')->save($productModelParent);

        $productModel = $this->createProductModel(
            [
                'code' => 'product_model_code',
                'family_variant' => 'clothing_color_size',
            ]
        );
        $productModel->setParent($productModelParent);

        $errors = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        $this->assertEquals(
            'Attribute "color" cannot be empty, as it is defined as an axis for this entity',
            $errors->get(0)->getMessage()
        );
    }

    /**
     * Advanced validation, we can't set extra attributes to a product model, other than the ones in the related
     * Attribute Set
     */
    public function testTheProductModelAttributesAreInTheAttributeSet()
    {
        $productModelParent = $this->createProductModel(
            [
                'code' => 'product_model_parent_code',
                'family_variant' => 'clothing_color_size',
            ]
        );

        $errors = $this->get('pim_catalog.validator.product_model')->validate($productModelParent);
        $this->assertEquals(0, $errors->count());
        $this->get('pim_catalog.saver.product_model')->save($productModelParent);

        $productModel = $this->createProductModel(
            [
                'code' => 'product_model_code',
                'family_variant' => 'clothing_color_size',
                'values' => [
                    'color' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'blue',
                        ]
                    ],
                    'sku' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'pant',
                        ],
                    ],
                ]
            ]
        );
        $productModel->setParent($productModelParent);

        $errors = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        $this->assertEquals(
            'Cannot set the property "sku" to this entity as it is not in the attribute set',
            $errors->get(0)->getMessage()
        );
    }

    /**
     * Advanced validation, we can't set the same value for axes, as axes values are unique
     */
    public function testVariantAxisValuesCombinationIsUniqueInDatabase()
    {
        $productModelParent = $this->createProductModel(
            [
                'code' => 'product_model_parent_code',
                'family_variant' => 'clothing_color_size',
            ]
        );

        $errors = $this->get('pim_catalog.validator.product_model')->validate($productModelParent);
        $this->assertEquals(0, $errors->count());
        $this->get('pim_catalog.saver.product_model')->save($productModelParent);

        $productModel = $this->createProductModel(
            [
                'code' => 'product_model_code',
                'family_variant' => 'clothing_color_size',
                'values' => [
                    'color' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'blue',
                        ]
                    ],
                ]
            ]
        );
        $productModel->setParent($productModelParent);
        $errors = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        $this->assertEquals(0, $errors->count());
        $this->get('pim_catalog.saver.product_model')->save($productModel);
        $this->get('pim_catalog.validator.unique_axes_combination_set')->reset();

        $productModelDuplicate = $this->createProductModel(
            [
                'code' => 'product_model_duplicate_code',
                'family_variant' => 'clothing_color_size',
                'values' => [
                    'color' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'blue',
                        ]
                    ],
                ]
            ]
        );
        $productModelDuplicate->setParent($productModelParent);
        $errors = $this->get('pim_catalog.validator.product_model')->validate($productModelDuplicate);
        $this->assertEquals(
            'Cannot set value "[blue]" for the attribute axis "color" on product model "product_model_duplicate_code", as the product model "product_model_code" already has this value',
            $errors->get(0)->getMessage()
        );
    }

    /**
     * Advanced validation, we can't set the same value for axes, as axes values are unique
     */
    public function testVariantAxisValuesCombinationIsUniqueInMemory()
    {
        $productModelParent = $this->createProductModel(
            [
                'code' => 'product_model_parent_code',
                'family_variant' => 'clothing_color_size',
            ]
        );

        $errors = $this->get('pim_catalog.validator.product_model')->validate($productModelParent);
        $this->assertEquals(0, $errors->count());
        $this->get('pim_catalog.saver.product_model')->save($productModelParent);

        $productModel = $this->createProductModel(
            [
                'code' => 'product_model_code',
                'family_variant' => 'clothing_color_size',
                'values' => [
                    'color' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'blue',
                        ]
                    ],
                ]
            ]
        );
        $productModel->setParent($productModelParent);
        $errors = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        $this->assertEquals(0, $errors->count());

        $productModelDuplicate = $this->createProductModel(
            [
                'code' => 'product_model_duplicate_code',
                'family_variant' => 'clothing_color_size',
                'values' => [
                    'color' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'blue',
                        ]
                    ],
                ]
            ]
        );
        $productModelDuplicate->setParent($productModelParent);
        $errors = $this->get('pim_catalog.validator.product_model')->validate($productModelDuplicate);
        $this->assertEquals(
            'Cannot set value "[blue]" for the attribute axis "color" on product model "product_model_duplicate_code", as the product model "product_model_code" already has this value',
            $errors->get(0)->getMessage()
        );
    }

    public function testTheProductModelHaveValidMetricValue()
    {
        $productModel = $this->createProductModel(
            [
                'code' => 'model-running-shoes-l',
                'family_variant' => 'shoes_size_color',
                'parent' => 'model-running-shoes',
                'values' => [
                    'size' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'l',
                        ],
                    ],
                    'weight' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => [
                                'amount' => 'foobar',
                                'unit' => 'GRAM'
                            ],
                        ],
                    ],
                ],
            ]
        );

        $errors = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        $this->assertEquals(
            'This value should be a valid number.',
            $errors->get(0)->getMessage()
        );
    }

    public function testFamilyVariantIsOptionalForSubProductModel()
    {
        $productModel = $this->createProductModel(
            [
                'code' => 'model-running-shoes-l',
                'parent' => 'model-running-shoes',
                'values' => [
                    'size' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'l',
                        ],
                    ],
                ],
            ]
        );

        $errors = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        $this->assertEquals(0, $errors->count());
        $this->assertEquals('shoes_size_color', $productModel->getFamilyVariant()->getCode());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    /**
     * @param array $data
     *
     * @return ProductModelInterface
     */
    private function createProductModel(array $data): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        return $productModel;
    }
}
