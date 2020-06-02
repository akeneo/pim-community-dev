<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\ProductModel\Association;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\QuantifiedAssociation\GetProductModelQuantifiedAssociationsByProductModelCodes;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\QuantifiedAssociationsTestCaseTrait;
use AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\AbstractQuantifiedAssociationIntegration;

class GetProductModelQuantifiedAssociationsByProductModelCodesIntegration extends AbstractQuantifiedAssociationIntegration
{
    use QuantifiedAssociationsTestCaseTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->givenBooleanAttributes(['first_yes_no', 'second_yes_no']);
        $this->createQuantifiedAssociationType('PRODUCT_SET');
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
    public function testItReturnQuantifiedAssociationWithProductModelsOnSingleProductModel()
    {
        $this->getEntityBuilder()->createProductModel('productModelA', 'familyVariantWithTwoLevels', null, []);
        $this->getEntityBuilder()->createProductModel('productModelB', 'familyVariantWithTwoLevels', null, []);

        $this->getEntityBuilder()->createProductModel('productModelC', 'familyVariantWithTwoLevels', null, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'product_models' => [
                        ['identifier' => 'productModelA', 'quantity' => 8],
                        ['identifier' => 'productModelB', 'quantity' => 6],
                    ],
                ],
            ],
        ]);

        $actual = $this->getQuery()->fromProductModelCodes(['productModelC']);
        $expected = [
            'productModelC' => [
                'PRODUCT_SET' => [
                    'product_models' => [
                        ['identifier' => 'productModelA', 'quantity' => 8],
                        ['identifier' => 'productModelB', 'quantity' => 6],
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function testItReturnQuantifiedAssociationWithProductModelsOnMultipleProductModels()
    {
        $rootProductModel = $this->getEntityBuilder()->createProductModel('root_product_model', 'familyVariantWithTwoLevels', null, []);
        $this->getEntityBuilder()->createProductModel('sub_product_model_1', 'familyVariantWithTwoLevels', $rootProductModel, []);

        $this->getEntityBuilder()->createProductModel('productModelA', 'familyVariantWithTwoLevels', null, []);
        $this->getEntityBuilder()->createProductModel('productModelB', 'familyVariantWithTwoLevels', null, []);
        $this->getEntityBuilder()->createProductModel('productModelC', 'familyVariantWithTwoLevels', null, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'product_models' => [
                        ['identifier' => 'productModelA', 'quantity' => 3],
                        ['identifier' => 'productModelB', 'quantity' => 2],
                        ['identifier' => 'sub_product_model_1', 'quantity' => 9],
                    ],
                ],
            ],
        ]);

        $this->getEntityBuilder()->createProductModel('productModelD', 'familyVariantWithTwoLevels', null, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'product_models' => [
                        ['identifier' => 'productModelB', 'quantity' => 1],
                    ],
                ],
            ],
        ]);

        $actual = $this->getQuery()->fromProductModelCodes(['productModelC', 'productModelD']);
        $expected = [
            'productModelC' => [
                'PRODUCT_SET' => [
                    'product_models' => [
                        ['identifier' => 'productModelA', 'quantity' => 3],
                        ['identifier' => 'productModelB', 'quantity' => 2],
                        ['identifier' => 'sub_product_model_1', 'quantity' => 9],
                    ],
                ],
            ],
            'productModelD' => [
                'PRODUCT_SET' => [
                    'product_models' => [
                        ['identifier' => 'productModelB', 'quantity' => 1],
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function testItOnlyReturnProductModelsWithQuantifiedAssociation()
    {
        $this->getEntityBuilder()->createProductModel('productModelA', 'familyVariantWithTwoLevels', null, []);
        $this->getEntityBuilder()->createProductModel('productModelB', 'familyVariantWithTwoLevels', null, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'product_models' => [
                        ['identifier' => 'productModelA', 'quantity' => 3],
                    ],
                ],
            ],
        ]);

        $actual = $this->getQuery()->fromProductModelCodes(['productModelA', 'productModelB']);
        $expected = [
            'productModelB' => [
                'PRODUCT_SET' => [
                    'product_models' => [
                        ['identifier' => 'productModelA', 'quantity' => 3],
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function testItDoesNotReturnQuantifiedAssociationsWithProduct()
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
                    'product_models' => [
                        ['identifier' => 'root_product_model', 'quantity' => 6],
                        ['identifier' => 'sub_product_model_1', 'quantity' => 2]
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function testItReturnEmptyArrayWhenProductModelsGivenDoesNotHaveQuantifiedAssociationsWithProductModels()
    {
        $this->getEntityBuilder()->createProduct('productA', 'aFamily', []);
        $this->getEntityBuilder()->createProduct('productB', 'aFamily', []);
        $this->getEntityBuilder()->createProductModel('productModelA', 'familyVariantWithTwoLevels', null, []);
        $this->getEntityBuilder()->createProductModel('productModelB', 'familyVariantWithTwoLevels', null, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 8],
                        ['identifier' => 'productB', 'quantity' => 6],
                    ],
                ],
            ],
        ]);

        $actual = $this->getQuery()->fromProductModelCodes(['productModelA', 'productModelB']);

        $this->assertSame([], $actual);
    }

    /**
     * @test
     */
    public function testItDoesNotReturnQuantifiedAssociationWithDeletedProductModel()
    {
        $productModelA = $this->getEntityBuilder()->createProductModel('productModelA', 'familyVariantWithTwoLevels', null, []);
        $this->getEntityBuilder()->createProductModel('productModelB', 'familyVariantWithTwoLevels', null, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'product_models' => [
                        ['identifier' => 'productModelA', 'quantity' => 3],
                    ],
                ],
            ],
        ]);

        $this->getProductModelRemover()->remove($productModelA);
        $actual = $this->getQuery()->fromProductModelCodes(['productModelB']);

        $this->assertSame([], $actual);
    }

    /**
     * @test
     */
    public function testItDoesNotReturnDeletedQuantifiedAssociationType()
    {
        $this->getEntityBuilder()->createProductModel('productModelA', 'familyVariantWithTwoLevels', null, []);
        $this->getEntityBuilder()->createProductModel('productModelB', 'familyVariantWithTwoLevels', null, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'product_models' => [
                        ['identifier' => 'productModelA', 'quantity' => 3],
                    ],
                ],
            ],
        ]);

        $associationType = $this->getAssociationTypeRepository()->findOneBy(['code' => 'PRODUCT_SET']);
        $this->getAssociationTypeRemover()->remove($associationType);
        $actual = $this->getQuery()->fromProductModelCodes(['productModelB']);

        $this->assertSame([], $actual);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getQuery(): GetProductModelQuantifiedAssociationsByProductModelCodes
    {
        return $this->get('akeneo.pim.enrichment.product_model.query.get_product_model_quantified_associations_by_product_model_codes');
    }
}
