<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product\Association;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\QuantifiedAssociation\GetProductModelQuantifiedAssociationsByProductIdentifiers;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\QuantifiedAssociation\GetProductModelQuantifiedAssociationsByProductUuids;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\QuantifiedAssociationsTestCaseTrait;
use AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\AbstractQuantifiedAssociationIntegration;

class GetProductModelQuantifiedAssociationsByProductUuidsIntegration extends AbstractQuantifiedAssociationIntegration
{
    use QuantifiedAssociationsTestCaseTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->messageBus = $this->get('pim_enrich.product.message_bus');

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
    public function itReturnQuantifiedAssociationWithProductModelsOnSingleProduct()
    {
        $this->getEntityBuilder()->createProductModel('productModelA', 'familyVariantWithTwoLevels', null, []);
        $this->getEntityBuilder()->createProductModel('productModelB', 'familyVariantWithTwoLevels', null, []);

        $userId = ($this->getUserId('admin') !== 0)
            ? $this->getUserId('admin')
            : $this->createAdminUser()->getId();

        $product = $this->createProductFromUserIntents(
            'productC',
            [
                new SetFamily('aFamily'),
                new AssociateQuantifiedProductModels(
                    'PRODUCT_SET',
                    [
                        new QuantifiedEntity('productModelA', 8),
                        new QuantifiedEntity('productModelB', 6),
                    ]
                )
            ],
            $userId
        );

        $actual = $this->getQuery()->fromProductUuids([$product->getUuid()]);
        $expected = [
            $product->getUuid()->toString() => [
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
    public function itReturnsTheQuantifiedAssociationOfTheChildrenWhenDesynchronizedWithTheParent()
    {
        $this->getEntityBuilder()->createProductModel('associated_product_model', 'familyVariantWithTwoLevels', null, []);
        $rootProductModel = $this->getEntityBuilder()->createProductModel('root', 'familyVariantWithTwoLevels', null, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'product_models' => [
                        ['identifier' => 'associated_product_model', 'quantity' => 1],
                    ],
                ],
            ],
        ]);
        $this->getEntityBuilder()->createProductModel('sub', 'familyVariantWithTwoLevels', $rootProductModel, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'product_models' => [
                        ['identifier' => 'associated_product_model', 'quantity' => 2],
                    ],
                ],
            ],
        ]);

        $userId = ($this->getUserId('admin') !== 0)
            ? $this->getUserId('admin')
            : $this->createAdminUser()->getId();

        $product = $this->createProductFromUserIntents(
            'productA',
            [
                new SetFamily('aFamily'),
                new ChangeParent('sub'),
                new SetBooleanValue('second_yes_no', null, null, true),
                new AssociateQuantifiedProductModels(
                    'PRODUCT_SET',
                    [
                        new QuantifiedEntity('associated_product_model', 3)
                    ]
                )
            ],
            $userId
        );

        $actual = $this->getQuery()->fromProductUuids([$product->getUuid()]);
        $expected = [
            $product->getUuid()->toString() => [
                'PRODUCT_SET' => [
                    'product_models' => [
                        ['identifier' => 'associated_product_model', 'quantity' => 3],
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function itReturnQuantifiedAssociationWithProductModelsOnMultipleProducts()
    {
        $this->getEntityBuilder()->createProductModel('productModelA', 'familyVariantWithTwoLevels', null, []);
        $this->getEntityBuilder()->createProductModel('productModelB', 'familyVariantWithTwoLevels', null, []);
        $this->getEntityBuilder()->createProductModel('productModelC', 'familyVariantWithTwoLevels', null, []);

        $userId = ($this->getUserId('admin') !== 0)
            ? $this->getUserId('admin')
            : $this->createAdminUser()->getId();

        $productC = $this->createProductFromUserIntents(
            'productC',
            [
                new SetFamily('aFamily'),
                new AssociateQuantifiedProductModels(
                    'PRODUCT_SET',
                    [
                        new QuantifiedEntity('productModelA', 3),
                        new QuantifiedEntity('productModelB', 2),
                    ]
                )
            ],
            $userId
        );

        $productD = $this->createProductFromUserIntents(
            'productD',
            [
                new SetFamily('aFamily'),
                new AssociateQuantifiedProductModels(
                    'PRODUCT_SET',
                    [
                        new QuantifiedEntity('productModelB', 1),
                    ]
                )
            ],
            $userId
        );

        $rootProductModel = $this->getEntityBuilder()->createProductModel('root_product_model', 'familyVariantWithTwoLevels', null, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'product_models' => [
                        ['identifier' => 'productModelC', 'quantity' => 7],
                    ],
                ],
            ]
        ]);

        $this->getEntityBuilder()->createProductModel('sub_product_model_1', 'familyVariantWithTwoLevels', $rootProductModel, [
            'quantified_associations' => [
                'PRODUCT_SET' => [
                    'product_models' => [
                        ['identifier' => 'productModelB', 'quantity' => 6],
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
                new AssociateQuantifiedProductModels(
                    'PRODUCT_SET',
                    [
                        new QuantifiedEntity('productModelA', 5),
                    ]
                )
            ],
            $userId
        );

        $actual = $this->getQuery()->fromProductUuids([
            $productC->getUuid(), $productD->getUuid(), $variantProduct->getUuid()
        ]);

        $expected = [
            $productC->getUuid()->toString() => [
                'PRODUCT_SET' => [
                    'product_models' => [
                        ['identifier' => 'productModelA', 'quantity' => 3],
                        ['identifier' => 'productModelB', 'quantity' => 2],
                    ],
                ],
            ],
            $productD->getUuid()->toString() => [
                'PRODUCT_SET' => [
                    'product_models' => [
                        ['identifier' => 'productModelB', 'quantity' => 1],
                    ],
                ],
            ],
            $variantProduct->getUuid()->toString() => [
                'PRODUCT_SET' => [
                    'product_models' => [
                        ['identifier' => 'productModelA', 'quantity' => 5],
                        ['identifier' => 'productModelB', 'quantity' => 6],
                        ['identifier' => 'productModelC', 'quantity' => 7],
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
        $this->getEntityBuilder()->createProductModel('productModelA', 'familyVariantWithTwoLevels', null, []);

        $userId = ($this->getUserId('admin') !== 0)
            ? $this->getUserId('admin')
            : $this->createAdminUser()->getId();

        $productA = $this->createProductFromUserIntents(
            'productA',
            [
                new SetFamily('aFamily'),
            ],
            $userId
        );

        $productB = $this->createProductFromUserIntents(
            'productB',
            [
                new SetFamily('aFamily'),
                new AssociateQuantifiedProductModels('PRODUCT_SET', [new QuantifiedEntity('productModelA', 3)]),
            ],
            $userId
        );

        $actual = $this->getQuery()->fromProductUuids([$productA->getUuid(), $productB->getUuid()]);
        $expected = [
            $productB->getUuid()->toString() => [
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
    public function itDoesNotReturnQuantifiedAssociationsWithProduct()
    {
        $rootProductModel = $this->getEntityBuilder()->createProductModel('root_product_model', 'familyVariantWithTwoLevels', null, []);
        $this->getEntityBuilder()->createProductModel('sub_product_model_1', 'familyVariantWithTwoLevels', $rootProductModel, []);

        $userId = ($this->getUserId('admin') !== 0)
            ? $this->getUserId('admin')
            : $this->createAdminUser()->getId();

        $this->createProductFromUserIntents(
            'productA',
            [
                new SetFamily('aFamily'),
            ],
            $userId
        );

        $productB = $this->createProductFromUserIntents(
            'productB',
            [
                new SetFamily('aFamily'),
                new AssociateQuantifiedProducts('PRODUCT_SET', [new QuantifiedEntity('productA', 3)]),
                new AssociateQuantifiedProductModels('PRODUCT_SET', [
                    new QuantifiedEntity('root_product_model', 6),
                    new QuantifiedEntity('sub_product_model_1', 2),
                ]),
            ],
            $userId
        );

        $actual = $this->getQuery()->fromProductUuids([$productB->getUuid()]);
        $expected = [
            $productB->getUuid()->toString() => [
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
    public function itReturnEmptyArrayWhenProductsGivenDoesNotHaveQuantifiedAssociationsWithProductModels()
    {
        $userId = ($this->getUserId('admin') !== 0)
            ? $this->getUserId('admin')
            : $this->createAdminUser()->getId();

        $productA = $this->createProductFromUserIntents(
            'productA',
            [
                new SetFamily('aFamily')
            ],
            $userId
        );

        $productB = $this->createProductFromUserIntents(
            'productB',
            [
                new SetFamily('aFamily'),
                new AssociateQuantifiedProducts(
                    'PRODUCT_SET',
                    [new QuantifiedEntity('productA', 8)]
                )
            ],
            $userId
        );

        $actual = $this->getQuery()->fromProductUuids([$productA->getUuid(), $productB->getUuid()]);

        $this->assertSame([], $actual);
    }

    /**
     * @test
     */
    public function itDoesNotReturnQuantifiedAssociationWithDeletedProductModel()
    {
        $userId = ($this->getUserId('admin') !== 0)
            ? $this->getUserId('admin')
            : $this->createAdminUser()->getId();

        $productModelA = $this->getEntityBuilder()->createProductModel('productModelA', 'familyVariantWithTwoLevels', null, []);

        $command = UpsertProductCommand::createFromCollection(
            userId: $userId,
            productIdentifier: 'productB',
            userIntents: [
                new SetFamily('aFamily'),
                new AssociateQuantifiedProductModels('PRODUCT_SET', [new QuantifiedEntity('productModelA', 3)]),
            ]
        );
        $this->messageBus->dispatch($command);
        $productB = $this->get('pim_catalog.repository.product')->findOneByIdentifier('productB');

        $this->getProductModelRemover()->remove($productModelA);
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

        $this->getEntityBuilder()->createProductModel('productModelA', 'familyVariantWithTwoLevels', null, []);

        $productB = $this->createProductFromUserIntents(
            'productB',
            [
                new SetFamily('aFamily'),
                new AssociateQuantifiedProductModels('PRODUCT_SET', [new QuantifiedEntity('productModelA', 3)])
            ],
            $userId
        );

        $associationType = $this->getAssociationTypeRepository()->findOneBy(['code' => 'PRODUCT_SET']);
        $this->getAssociationTypeRemover()->remove($associationType);
        $actual = $this->getQuery()->fromProductUuids([$productB->getUuid()]);

        $this->assertSame([], $actual);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getQuery(): GetProductModelQuantifiedAssociationsByProductUuids
    {
        return $this->get('akeneo.pim.enrichment.product.query.get_product_model_quantified_associations_by_product_uuids');
    }
}
