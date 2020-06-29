<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product\Association;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\QuantifiedAssociation\GetProductQuantifiedAssociationsByProductIdentifiers;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociations;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\QuantifiedAssociationsTestCaseTrait;
use AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\AbstractQuantifiedAssociationIntegration;

class GetProductQuantifiedAssociationsByProductIdentifiersIntegration extends AbstractQuantifiedAssociationIntegration
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
    public function itReturnQuantifiedAssociationWithProductsOnSingleProductWithParentQuantAssociationsOnProducts()
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

        $actual = $this->getQuery()->fromProductIdentifiers(['productC']);
        $expected = [
            'productC' => [
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
        $rootProductModel = $this->getEntityBuilder()->createProductModel('root_product_model', 'familyVariantWithTwoLevels', null, []);
        $subProductModel = $this->getEntityBuilder()->createProductModel('sub_product_model_1', 'familyVariantWithTwoLevels', $rootProductModel, []);
        $this->getEntityBuilder()->createVariantProduct('variant_product_1', 'aFamily', 'familyVariantWithTwoLevels', $subProductModel, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 5],
                    ],
                ],
            ],
        ]);
        $subProductModel->setQuantifiedAssociations(
            QuantifiedAssociations::createFromNormalized(
                [
                    'PRODUCT_SET' => [
                        'products' => [
                            ['identifier' => 'productC', 'quantity' => 6],
                        ],
                    ],
                ]
            )
        );
        $rootProductModel->setQuantifiedAssociations(
            QuantifiedAssociations::createFromNormalized(
                [
                    'PRODUCT_SET' => [
                        'products' => [
                            ['identifier' => 'productB', 'quantity' => 7],
                        ],
                    ],
                ]
            )
        );
        $this->productModelSaver()->save($rootProductModel);
        $this->productModelSaver()->save($subProductModel);

        $actual = $this->getQuery()->fromProductIdentifiers(['productC', 'productD', 'variant_product_1']);
        $expected = [
            'productC' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 3],
                        ['identifier' => 'productB', 'quantity' => 2],
                    ],
                ],
            ],
            'productD' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productB', 'quantity' => 1],
                    ],
                ],
            ],
            'variant_product_1' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productA', 'quantity' => 5],
                        ['identifier' => 'productC', 'quantity' => 6],
                        ['identifier' => 'productB', 'quantity' => 7],
                    ],
                ],
            ],
        ];

        $this->assertEqualsCanonicalizing($expected, $actual);
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

        $actual = $this->getQuery()->fromProductIdentifiers(['productA', 'productB']);
        $expected = [
            'productB' => [
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

        $actual = $this->getQuery()->fromProductIdentifiers(['productB']);
        $expected = [
            'productB' => [
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

        $actual = $this->getQuery()->fromProductIdentifiers(['productA', 'productB']);

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
        $actual = $this->getQuery()->fromProductIdentifiers(['productB']);

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
        $actual = $this->getQuery()->fromProductIdentifiers(['productB']);

        $this->assertSame([], $actual);
    }

    private function getQuery(): GetProductQuantifiedAssociationsByProductIdentifiers
    {
        return $this->get('akeneo.pim.enrichment.product.query.get_product_quantified_associations_by_product_identifiers');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function productModelSaver(): SaverInterface
    {
        return $this->get('pim_catalog.saver.product_model');
    }
}
