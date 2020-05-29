<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\ProductModel\Association;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\QuantifiedAssociation\GetProductQuantifiedAssociationsByProductModelCodes;
use AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\AbstractQuantifiedAssociationIntegration;

class GetProductQuantifiedAssociationsByProductModelCodesIntegration extends AbstractQuantifiedAssociationIntegration
{
    public function setUp(): void
    {
        parent::setUp();

        $this->givenAssociationType(['code' => 'PRODUCT_SET', 'is_quantified' => true]);
        $this->givenBooleanAttributes(['first_yes_no', 'second_yes_no']);
        $this->givenFamily(['code' => 'aFamily', 'attribute_codes' => ['first_yes_no', 'second_yes_no']]);
        $this->getEntityBuilder()->createFamilyVariant(
            [
                'code' => 'familyVariantWithTwoLevels',
                'family' => 'aFamily',
                'variant_attribute_sets' => [
                    [
                        'level' => 1,
                        'axes' => ['first_yes_no'],
                        'attributes' => [],
                    ],
                    [
                        'level' => 2,
                        'axes' => ['second_yes_no'],
                        'attributes' => [],
                    ],
                ],
            ]
        );
    }

    /**
     * @test
     */
    public function testItReturnQuantifiedAssociationWithProductsOnSingleProductModel()
    {
        $this->getEntityBuilder()->createProduct('productA', 'aFamily', []);
        $this->getEntityBuilder()->createProduct('productB', 'aFamily', []);
        $this->getEntityBuilder()->createProductModel('productModelA', 'familyVariantWithTwoLevels', null, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 8],
                        ['identifier' => 'productB', 'quantity' => 6],
                    ],
                ],
            ],
        ]);

        $actual = $this->getQuery()->fromProductModelCodes(['productModelA']);
        $expected = [
            'productModelA' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 8],
                        ['identifier' => 'productB', 'quantity' => 6],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function testItReturnQuantifiedAssociationWithProductsOnMultipleProductModels()
    {
        $this->getEntityBuilder()->createProduct('productA', 'aFamily', []);
        $this->getEntityBuilder()->createProduct('productB', 'aFamily', []);
        $this->getEntityBuilder()->createProductModel('productModelA', 'familyVariantWithTwoLevels', null, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 3],
                        ['identifier' => 'productB', 'quantity' => 2],
                    ],
                ],
            ],
        ]);

        $this->getEntityBuilder()->createProductModel('productModelB', 'familyVariantWithTwoLevels', null, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productB', 'quantity' => 1],
                    ],
                ],
            ],
        ]);

        $actual = $this->getQuery()->fromProductModelCodes(['productModelA', 'productModelB']);
        $expected = [
            'productModelA' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 3],
                        ['identifier' => 'productB', 'quantity' => 2],
                    ],
                ],
            ],
            'productModelB' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productB', 'quantity' => 1],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function testItOnlyReturnProductModelsWithQuantifiedAssociation()
    {
        $this->getEntityBuilder()->createProduct('productA', 'aFamily', []);
        $this->getEntityBuilder()->createProductModel('productModelA', 'familyVariantWithTwoLevels', null, []);
        $this->getEntityBuilder()->createProductModel('productModelB', 'familyVariantWithTwoLevels', null, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 3],
                    ],
                ],
            ],
        ]);

        $actual = $this->getQuery()->fromProductModelCodes(['productModelA', 'productModelB']);
        $expected = [
            'productModelB' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 3],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function testItDoesNotReturnQuantifiedAssociationsWithProductModel()
    {
        $rootProductModel = $this->getEntityBuilder()->createProductModel('root_product_model', 'familyVariantWithTwoLevels', null, []);
        $this->getEntityBuilder()->createProductModel('sub_product_model_1', 'familyVariantWithTwoLevels', $rootProductModel, []);

        $this->getEntityBuilder()->createProduct('productA', 'aFamily', []);
        $this->getEntityBuilder()->createProductModel('productModelA', 'familyVariantWithTwoLevels', null, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 3],
                    ],
                    'product_models' => [
                        ['identifier' => 'root_product_model', 'quantity' => 6],
                        ['identifier' => 'sub_product_model_1', 'quantity' => 2]
                    ],
                ],
            ],
        ]);

        $actual = $this->getQuery()->fromProductModelCodes(['productModelA']);
        $expected = [
            'productModelA' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 3],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function testItReturnEmptyArrayWhenProductModelsGivenDoesNotHaveQuantifiedAssociationsWithProducts()
    {
        $this->getEntityBuilder()->createProductModel('productModelA', 'familyVariantWithTwoLevels', null, []);
        $this->getEntityBuilder()->createProductModel('productModelB', 'familyVariantWithTwoLevels', null, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'product_models' => [
                        ['identifier' => 'productModelA', 'quantity' => 8],
                    ],
                ],
            ],
        ]);

        $actual = $this->getQuery()->fromProductModelCodes(['productModelA', 'productModelB']);

        $this->assertEquals([], $actual);
    }

    /**
     * @test
     */
    public function testItDoesNotReturnQuantifiedAssociationWithDeletedProduct()
    {
        $productA = $this->getEntityBuilder()->createProduct('productA', 'aFamily', []);
        $this->getEntityBuilder()->createProductModel('productModelA', 'familyVariantWithTwoLevels', null, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 3],
                    ],
                ],
            ],
        ]);

        $this->getProductRemover()->remove($productA);
        $actual = $this->getQuery()->fromProductModelCodes(['productModelA']);

        $this->assertEquals([], $actual);
    }

    /**
     * @test
     */
    public function testItDoesNotReturnDeletedQuantifiedAssociationType()
    {
        $this->getEntityBuilder()->createProduct('productA', 'aFamily', []);
        $this->getEntityBuilder()->createProductModel('productModelA', 'familyVariantWithTwoLevels', null, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 3],
                    ],
                ],
            ],
        ]);

        $associationType = $this->getAssociationTypeRepository()->findOneBy(['code' => 'PRODUCT_SET']);
        $this->getAssociationTypeRemover()->remove($associationType);
        $actual = $this->getQuery()->fromProductModelCodes(['productModelA']);

        $this->assertEquals([], $actual);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getQuery(): GetProductQuantifiedAssociationsByProductModelCodes
    {
        return $this->get('akeneo.pim.enrichment.product_model.query.get_product_quantified_associations_by_product_model_codes');
    }
}
