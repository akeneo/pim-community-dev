<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product\Association;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\QuantifiedAssociation\GetProductQuantifiedAssociationsByProductUuids;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\QuantifiedAssociationsTestCaseTrait;
use AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\AbstractQuantifiedAssociationIntegration;
use Doctrine\DBAL\Connection;

class GetProductQuantifiedAssociationsByProductUuidsIntegration extends AbstractQuantifiedAssociationIntegration
{
    use QuantifiedAssociationsTestCaseTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->messageBus = $this->get('pim_enrich.product.message_bus');

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
        $userId = ($this->getUserId('admin') !== 0)
            ? $this->getUserId('admin')
            : $this->createAdminUser()->getId();

        $productA = $this->createProductFromUserIntents(
            'productA',
            [new SetFamily('aFamily')],
            $userId
        );
        $productB = $this->createProductFromUserIntents(
            'productB',
            [new SetFamily('aFamily')],
            $userId
        );

        $this->getEntityBuilder()->createProductModel('product_model', 'familyVariantWithTwoLevels', null, []);

        $productC = $this->createProductFromUserIntents(
            'productC',
            [
                new SetFamily('aFamily'),
                new AssociateQuantifiedProducts(
                    'PRODUCT_SET',
                    [
                        new QuantifiedEntity('productA', 8),
                        new QuantifiedEntity('productB', 6),
                    ]
                )
            ],
            $userId
        );

        $actual = $this->getQuery()->fromProductUuids([$productC->getUuid()]);
        $expected = [
            $productC->getUuid()->toString() => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['uuid' => $productA->getUuid()->toString(), 'quantity' => 8],
                        ['uuid' => $productB->getUuid()->toString(), 'quantity' => 6],
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
        $userId = ($this->getUserId('admin') !== 0)
            ? $this->getUserId('admin')
            : $this->createAdminUser()->getId();

        $productA = $this->createProductFromUserIntents(
            'productA',
            [new SetFamily('aFamily')],
            $userId
        );

        $productB = $this->createProductFromUserIntents(
            'productB',
            [new SetFamily('aFamily')],
            $userId
        );
        $productC = $this->createProductFromUserIntents(
            'productC',
            [
                new SetFamily('aFamily'),
                new AssociateQuantifiedProducts(
                    'PRODUCT_SET',
                    [
                        new QuantifiedEntity('productA', 3),
                        new QuantifiedEntity('productB', 2)
                    ]
                )
            ],
            $userId
        );

        $productD = $this->createProductFromUserIntents(
            'productD',
            [
                new SetFamily('aFamily'),
                new AssociateQuantifiedProducts(
                    'PRODUCT_SET',
                    [
                        new QuantifiedEntity('productB', 1),
                    ]
                ),
            ],
            $userId
        );

        $rootProductModel = $this->getEntityBuilder()->createProductModel('root_product_model', 'familyVariantWithTwoLevels', null, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productB', 'quantity' => 7],
                    ],
                ],
            ]
        ]);
        $this->getEntityBuilder()->createProductModel('sub_product_model_1', 'familyVariantWithTwoLevels', $rootProductModel, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'productC', 'quantity' => 6],
                    ],
                ],
            ]
        ]);

        $variantProduct = $this->createProductFromUserIntents(
            'variant_product_1',
            [
                new SetFamily('aFamily'),
                new ChangeParent('sub_product_model_1'),
                new SetBooleanValue('second_yes_no', null, null, true),
                new AssociateQuantifiedProducts(
                    'PRODUCT_SET',
                    [
                        new QuantifiedEntity('productA', 5)
                    ]
                ),
            ],
            $userId
        );

        $actual = $this->getQuery()->fromProductUuids([$productC->getUuid(), $productD->getUuid(), $variantProduct->getUuid()]);
        $expected = [
            $productC->getUuid()->toString() => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['uuid' => $productA->getUuid()->toString(), 'quantity' => 3],
                        ['uuid' => $productB->getUuid()->toString(), 'quantity' => 2],
                    ],
                ],
            ],
            $productD->getUuid()->toString() => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['uuid' => $productB->getUuid()->toString(), 'quantity' => 1],
                    ],
                ],
            ],
            $variantProduct->getUuid()->toString() => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['uuid' => $productA->getUuid()->toString(), 'quantity' => 5],
                        ['uuid' => $productC->getUuid()->toString(), 'quantity' => 6],
                        ['uuid' => $productB->getUuid()->toString(), 'quantity' => 7],
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
        $userId = ($this->getUserId('admin') !== 0)
            ? $this->getUserId('admin')
            : $this->createAdminUser()->getId();

        $associatedProduct = $this->createProductFromUserIntents(
            'associated_product',
            [
                new SetFamily('aFamily')
            ],
            $userId
        );
        $rootProductModel = $this->getEntityBuilder()->createProductModel('root', 'familyVariantWithTwoLevels', null, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'associated_product', 'quantity' => 1],
                    ],
                ],
            ],
        ]);
        $this->getEntityBuilder()->createProductModel('sub', 'familyVariantWithTwoLevels', $rootProductModel, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['identifier' => 'associated_product', 'quantity' => 2],
                    ],
                ],
            ],
        ]);

        $productA = $this->createProductFromUserIntents(
            'productA',
            [
                new SetFamily('aFamily'),
                new ChangeParent('sub'),
                new SetBooleanValue('second_yes_no', null, null, true),
                new AssociateQuantifiedProducts(
                    'PRODUCT_SET',
                    [
                        new QuantifiedEntity('associated_product', 3),
                    ]
                ),
            ],
            $userId
        );

        $actual = $this->getQuery()->fromProductUuids([$productA->getUuid()]);
        $expected = [
            $productA->getUuid()->toString() => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['uuid' => $associatedProduct->getUuid()->toString(), 'quantity' => 3],
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
        $userId = ($this->getUserId('admin') !== 0)
            ? $this->getUserId('admin')
            : $this->createAdminUser()->getId();

        $productA = $this->createProductFromUserIntents(
            'productA',
            [new SetFamily('aFamily')],
            $userId
        );

        $productB = $this->createProductFromUserIntents(
            'productB',
            [
                new SetFamily('aFamily'),
                new AssociateQuantifiedProducts(
                    'PRODUCT_SET',
                    [new QuantifiedEntity('productA', 3)]
                )
            ],
            $userId
        );

        $actual = $this->getQuery()->fromProductUuids([$productA->getUuid(), $productB->getUuid()]);
        $expected = [
            $productB->getUuid()->toString() => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['uuid' => $productA->getUuid()->toString(), 'quantity' => 3],
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

        $userId = ($this->getUserId('admin') !== 0)
            ? $this->getUserId('admin')
            : $this->createAdminUser()->getId();

        $productA = $this->createProductFromUserIntents(
            'productA',
            [new SetFamily('aFamily')],
            $userId
        );

        $productB = $this->createProductFromUserIntents(
            'productB',
            [
                new SetFamily('aFamily'),
                new AssociateQuantifiedProducts('PRODUCT_SET', [new QuantifiedEntity('productA', 3)]),
                new AssociateQuantifiedProductModels(
                    'PRODUCT_SET',
                    [
                        new QuantifiedEntity('root_product_model', 6),
                        new QuantifiedEntity('sub_product_model_1', 2),
                    ]
                ),
            ],
            $userId
        );

        $actual = $this->getQuery()->fromProductUuids([$productB->getUuid()]);
        $expected = [
            $productB->getUuid()->toString() => [
                'PRODUCT_SET' => [
                    'products' => [
                        ['uuid' => $productA->getUuid()->toString(), 'quantity' => 3],
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

        $userId = ($this->getUserId('admin') !== 0)
            ? $this->getUserId('admin')
            : $this->createAdminUser()->getId();

        $productA = $this->createProductFromUserIntents(
            'productA',
            [new SetFamily('aFamily')],
            $userId
        );

        $productB = $this->createProductFromUserIntents(
            'productB',
            [
                new SetFamily('aFamily'),
                new AssociateQuantifiedProductModels(
                    'PRODUCT_SET',
                    [
                        new QuantifiedEntity('productModelA', 8),
                        new QuantifiedEntity('productModelB', 6),
                    ]
                ),
            ],
            $userId
        );

        $actual = $this->getQuery()->fromProductUuids([$productA->getUuid(), $productB->getUuid()]);

        $this->assertSame([], $actual);
    }

    /**
     * @test
     */
    public function itDoesNotReturnQuantifiedAssociationWithDeletedProduct()
    {
        $userId = ($this->getUserId('admin') !== 0)
            ? $this->getUserId('admin')
            : $this->createAdminUser()->getId();

        $productA = $this->createProductFromUserIntents(
            'productA',
            [new SetFamily('aFamily')],
            $userId
        );
        $productB = $this->createProductFromUserIntents(
            'productB',
            [
                new SetFamily('aFamily'),
                new AssociateQuantifiedProducts(
                    'PRODUCT_SET',
                    [
                        new QuantifiedEntity('productA', 3),
                    ]
                ),
            ],
            $userId
        );

        $this->getProductRemover()->remove($productA);
        $actual = $this->getQuery()->fromProductUuids([$productB->getUuid()]);

        $this->assertSame([], $actual);
    }

    /**
     * @test
     */
    public function itDoesNotReturnDeletedQuantifiedAssociationType()
    {
        $userId = ($this->getUserId('admin') !== 0)
            ? $this->getUserId('admin')
            : $this->createAdminUser()->getId();

        $productA = $this->createProductFromUserIntents(
            'productA',
            [new SetFamily('aFamily')],
            $userId
        );
        $productB = $this->createProductFromUserIntents(
            'productB',
            [
                new SetFamily('aFamily'),
                new AssociateQuantifiedProducts(
                    'PRODUCT_SET',
                    [
                        new QuantifiedEntity('productA', 3)
                    ]
                ),
            ],
            $userId
        );

        $associationType = $this->getAssociationTypeRepository()->findOneBy(['code' => 'PRODUCT_SET']);
        $this->getAssociationTypeRemover()->remove($associationType);
        $actual = $this->getQuery()->fromProductUuids([$productB->getUuid()]);

        $this->assertSame([], $actual);
    }

    /**
     * @test
     *
     * https://akeneo.atlassian.net/browse/PIM-9356
     */
    public function itDoesNotFailOnInvalidQuantifiedAssociations()
    {
        $userId = ($this->getUserId('admin') !== 0)
            ? $this->getUserId('admin')
            : $this->createAdminUser()->getId();

        $rootProductModel = $this->getEntityBuilder()->createProductModel('root_product_model', 'familyVariantWithTwoLevels', null, []);
        $this->getEntityBuilder()->createProductModel('sub_product_model_1', 'familyVariantWithTwoLevels', $rootProductModel, []);
        $variantProduct = $this->createProductFromUserIntents(
            'variant_product_1',
            [
                new SetFamily('aFamily'),
                new ChangeParent('sub_product_model_1'),
                new SetBooleanValue('second_yes_no', null, null, true),
            ],
            $userId
        );

        /** @var Connection $connection */
        $connection = $this->get('doctrine.dbal.default_connection');

        $query = <<<SQL
        UPDATE pim_catalog_product_model
        SET quantified_associations = '[]'
        WHERE code = 'root_product_model'
SQL;

        $connection->executeUpdate($query);

        $actual = $this->getQuery()->fromProductUuids([$variantProduct->getUuid()]);
        $this->assertSame([], $actual);
    }

    private function getQuery(): GetProductQuantifiedAssociationsByProductUuids
    {
        return $this->get('akeneo.pim.enrichment.product.query.get_product_quantified_associations_by_product_uuids');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
