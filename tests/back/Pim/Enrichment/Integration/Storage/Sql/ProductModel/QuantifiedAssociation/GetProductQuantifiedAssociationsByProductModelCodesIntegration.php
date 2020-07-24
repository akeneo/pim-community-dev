<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\ProductModel\Association;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\QuantifiedAssociation\GetProductQuantifiedAssociationsByProductModelCodes;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\QuantifiedAssociationsTestCaseTrait;
use AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\AbstractQuantifiedAssociationIntegration;
use Doctrine\DBAL\Connection;

class GetProductQuantifiedAssociationsByProductModelCodesIntegration extends AbstractQuantifiedAssociationIntegration
{
    use QuantifiedAssociationsTestCaseTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->createQuantifiedAssociationType('PRODUCT_SET');
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
    public function itReturnQuantifiedAssociationWithProductsOnSingleProductModel()
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

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function itReturnQuantifiedAssociationWithProductsOnMultipleProductModels()
    {
        $this->getEntityBuilder()->createProduct('productA', 'aFamily', []);
        $this->getEntityBuilder()->createProduct('productB', 'aFamily', []);
        $rootProductModel = $this->getEntityBuilder()->createProductModel('productModelA', 'familyVariantWithTwoLevels', null, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 3],
                    ],
                ],
            ],
        ]);

        $this->getEntityBuilder()->createProductModel('productModelB', 'familyVariantWithTwoLevels', $rootProductModel, [
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
                    ],
                ],
            ],
            'productModelB' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productB', 'quantity' => 1],
                        ['identifier' => 'productA', 'quantity' => 3],
                    ],
                ],
            ],
        ];

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    /**
     * @test
     */
    public function itReturnsTheQuantifiedAssociationOfTheChildrenWhenDesynchronizedWithTheParent()
    {
        $this->getEntityBuilder()->createProduct('productA', 'aFamily', []);
        $rootProductModel = $this->getEntityBuilder()->createProductModel('root_product_model', 'familyVariantWithTwoLevels', null, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 999],
                    ],
                ],
            ]
        ]);
        $this->getEntityBuilder()->createProductModel('productModelB', 'familyVariantWithTwoLevels', $rootProductModel, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 1],
                    ],
                ],
            ],
        ]);

        $actual = $this->getQuery()->fromProductModelCodes(['productModelB']);
        $expected = [
            'productModelB' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 1],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function itOnlyReturnProductModelsWithQuantifiedAssociation()
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

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function itDoesNotReturnQuantifiedAssociationsWithProductModel()
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

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function itReturnEmptyArrayWhenProductModelsGivenDoesNotHaveQuantifiedAssociationsWithProducts()
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

        $this->assertSame([], $actual);
    }

    /**
     * @test
     */
    public function itDoesNotReturnQuantifiedAssociationWithDeletedProduct()
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

        $this->assertSame([], $actual);
    }

    /**
     * @test
     */
    public function itDoesNotReturnDeletedQuantifiedAssociationType()
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

        $this->assertSame([], $actual);
    }

    /**
     * @test
     *
     * https://akeneo.atlassian.net/browse/PIM-9356
     */
    public function itDoesNotFailOnInvalidQuantifiedAssociations()
    {
        $rootProductModel = $this->getEntityBuilder()->createProductModel('root_product_model', 'familyVariantWithTwoLevels', null, []);
        $subProductModel = $this->getEntityBuilder()->createProductModel('sub_product_model_1', 'familyVariantWithTwoLevels', $rootProductModel, []);
        $this->getEntityBuilder()->createVariantProduct('variant_product_1', 'aFamily', 'familyVariantWithTwoLevels', $subProductModel, []);

        /** @var Connection $connection */
        $connection = $this->get('doctrine.dbal.default_connection');

        $query = <<<SQL
        UPDATE pim_catalog_product_model
        SET quantified_associations = '[]'
        WHERE code = 'root_product_model'
SQL;

        $connection->executeUpdate($query);

        $actual = $this->getQuery()->fromProductModelCodes(['variant_product_1']);
        $this->assertSame([], $actual);
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
