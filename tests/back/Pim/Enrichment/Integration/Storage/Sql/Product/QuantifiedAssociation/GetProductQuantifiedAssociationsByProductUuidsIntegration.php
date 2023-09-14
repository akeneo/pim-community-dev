<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product\Association;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\QuantifiedAssociation\GetProductQuantifiedAssociationsByProductUuids;
use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\QuantifiedAssociationsTestCaseTrait;
use AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\AbstractQuantifiedAssociationIntegration;
use Doctrine\DBAL\Connection;

class GetProductQuantifiedAssociationsByProductUuidsIntegration extends AbstractQuantifiedAssociationIntegration
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
    public function itReturnQuantifiedAssociationWithProductsOnSingleProduct()
    {
        $this->getEntityBuilder()->createProduct('productA', 'aFamily', []);
        $this->getEntityBuilder()->createProduct('productB', 'aFamily', []);
        $this->getEntityBuilder()->createProductModel('product_model', 'familyVariantWithTwoLevels', null, []);
        $this->getEntityBuilder()->createProduct('productC', 'aFamily', [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 8],
                        ['identifier' => 'productB', 'quantity' => 6],
                    ],
                ],
            ],
        ]);

        $uuidProductC = $this->getProductUuid('productC');
        $actual = $this->getQuery()->fromProductUuids([$uuidProductC]);
        $expected = [
            $uuidProductC->toString() => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 8, 'uuid' => $this->getProductUuid('productA')->toString()],
                        ['identifier' => 'productB', 'quantity' => 6, 'uuid' => $this->getProductUuid('productB')->toString()],
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function itReturnQuantifiedAssociationWithProductsOnMultipleProducts()
    {
        $this->getEntityBuilder()->createProduct('productA', 'aFamily', []);
        $this->getEntityBuilder()->createProduct('productB', 'aFamily', []);
        $this->getEntityBuilder()->createProduct('productC', 'aFamily', [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 3],
                        ['identifier' => 'productB', 'quantity' => 2],
                    ],
                ],
            ],
        ]);

        $this->getEntityBuilder()->createProduct('productD', 'aFamily', [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productB', 'quantity' => 1],
                    ],
                ],
            ],
        ]);
        $rootProductModel = $this->getEntityBuilder()->createProductModel('root_product_model', 'familyVariantWithTwoLevels', null, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productB', 'quantity' => 7],
                    ],
                ],
            ]
        ]);
        $subProductModel = $this->getEntityBuilder()->createProductModel('sub_product_model_1', 'familyVariantWithTwoLevels', $rootProductModel, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productC', 'quantity' => 6],
                    ],
                ],
            ]
        ]);
        $this->getEntityBuilder()->createVariantProduct('variant_product_1', 'aFamily', 'familyVariantWithTwoLevels', $subProductModel, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 5],
                    ],
                ],
            ],
        ]);


        $uuidProductC = $this->getProductUuid('productC');
        $uuidProductD = $this->getProductUuid('productD');
        $uuidVariantProduct1 = $this->getProductUuid('variant_product_1');
        $actual = $this->getQuery()->fromProductUuids([$uuidProductC, $uuidProductD, $uuidVariantProduct1]);
        $expected = [
            $uuidProductC->toString() => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 3, 'uuid' => $this->getProductUuid('productA')->toString()],
                        ['identifier' => 'productB', 'quantity' => 2, 'uuid' => $this->getProductUuid('productB')->toString()],
                    ],
                ],
            ],
            $uuidProductD->toString() => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productB', 'quantity' => 1, 'uuid' => $this->getProductUuid('productB')->toString()],
                    ],
                ],
            ],
            $uuidVariantProduct1->toString() => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 5, 'uuid' => $this->getProductUuid('productA')->toString()],
                        ['identifier' => 'productC', 'quantity' => 6, 'uuid' => $this->getProductUuid('productC')->toString()],
                        ['identifier' => 'productB', 'quantity' => 7, 'uuid' => $this->getProductUuid('productB')->toString()],
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
        $this->getEntityBuilder()->createProduct('associated_product', 'aFamily', []);
        $rootProductModel = $this->getEntityBuilder()->createProductModel('root', 'familyVariantWithTwoLevels', null, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'associated_product', 'quantity' => 1],
                    ],
                ],
            ],
        ]);
        $subProductModel = $this->getEntityBuilder()->createProductModel('sub', 'familyVariantWithTwoLevels', $rootProductModel, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'associated_product', 'quantity' => 2],
                    ],
                ],
            ],
        ]);
        $this->getEntityBuilder()->createVariantProduct('productA',  'aFamily', 'familyVariantWithTwoLevels', $subProductModel, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'associated_product', 'quantity' => 3],
                    ],
                ],
            ],
        ]);

        $uuidProductA = $this->getProductUuid('productA');
        $actual = $this->getQuery()->fromProductUuids([$uuidProductA]);
        $expected = [
            $uuidProductA->toString() => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'associated_product', 'quantity' => 3, 'uuid' => $this->getProductUuid('associated_product')->toString()],
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function itOnlyReturnProductsWithQuantifiedAssociation()
    {
        $this->getEntityBuilder()->createProduct('productA', 'aFamily', []);
        $this->getEntityBuilder()->createProduct('productB', 'aFamily', [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 3],
                    ],
                ],
            ],
        ]);

        $uuidProductA = $this->getProductUuid('productA');
        $uuidProductB = $this->getProductUuid('productB');
        $actual = $this->getQuery()->fromProductUuids([$uuidProductA, $uuidProductB]);
        $expected = [
            $uuidProductB->toString() => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 3, 'uuid' => $this->getProductUuid('productA')->toString()],
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
        $this->getEntityBuilder()->createProduct('productB', 'aFamily', [
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

        $uuidProductB = $this->getProductUuid('productB');
        $actual = $this->getQuery()->fromProductUuids([$uuidProductB]);
        $expected = [
            $uuidProductB->toString() => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 3, 'uuid' => $this->getProductUuid('productA')->toString()],
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function itReturnEmptyArrayWhenProductsGivenDoesNotHaveQuantifiedAssociationsWithProducts()
    {
        $this->getEntityBuilder()->createProductModel('productModelA', 'familyVariantWithTwoLevels', null, []);
        $this->getEntityBuilder()->createProductModel('productModelB', 'familyVariantWithTwoLevels', null, []);
        $this->getEntityBuilder()->createProduct('productA', 'aFamily', []);
        $this->getEntityBuilder()->createProduct('productB', 'aFamily', [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'product_models' => [
                        ['identifier' => 'productModelA', 'quantity' => 8],
                        ['identifier' => 'productModelB', 'quantity' => 6],
                    ],
                ],
            ],
        ]);

        $uuidProductA = $this->getProductUuid('productA');
        $uuidProductB = $this->getProductUuid('productB');
        $actual = $this->getQuery()->fromProductUuids([$uuidProductA, $uuidProductB]);

        $this->assertSame([], $actual);
    }

    /**
     * @test
     */
    public function itDoesNotReturnQuantifiedAssociationWithDeletedProduct()
    {
        $productA = $this->getEntityBuilder()->createProduct('productA', 'aFamily', []);
        $this->getEntityBuilder()->createProduct('productB', 'aFamily', [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 3],
                    ],
                ],
            ],
        ]);

        $this->getProductRemover()->remove($productA);
        $uuidProductB = $this->getProductUuid('productB');
        $actual = $this->getQuery()->fromProductUuids([$uuidProductB]);

        $this->assertSame([], $actual);
    }

    /**
     * @test
     */
    public function itDoesNotReturnDeletedQuantifiedAssociationType()
    {
        $this->getEntityBuilder()->createProduct('productA', 'aFamily', []);
        $this->getEntityBuilder()->createProduct('productB', 'aFamily', [
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
        $uuidProductB = $this->getProductUuid('productB');
        $actual = $this->getQuery()->fromProductUuids([$uuidProductB]);

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

        $uuidVariantProduct1 = $this->getProductUuid('variant_product_1');
        $actual = $this->getQuery()->fromProductUuids([$uuidVariantProduct1]);
        $this->assertSame([], $actual);
    }

    /** @test */
    public function itDoesNotFailIfAssociatedProductsHaveNoIdentifier(): void
    {
        $uuidA = $this->getEntityBuilder()->createProduct(null, 'aFamily', [])->getUuid();
        $uuidB = $this->getEntityBuilder()->createProduct('productB', 'aFamily', [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => null, 'uuid' => $uuidA->toString(), 'quantity' => 3],
                    ],
                ],
            ],
        ])->getUuid();

        $expected = [
            $uuidB->toString() => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => null, 'quantity' => 3, 'uuid' => $uuidA->toString()],
                    ],
                ],
            ],
        ];
        $actual = $this->getQuery()->fromProductUuids([$uuidB]);

        $this->assertSame($expected, $actual);
    }

    private function getQuery(): GetProductQuantifiedAssociationsByProductUuids
    {
        return $this->get('Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\QuantifiedAssociation\GetProductQuantifiedAssociationsByProductUuids');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
