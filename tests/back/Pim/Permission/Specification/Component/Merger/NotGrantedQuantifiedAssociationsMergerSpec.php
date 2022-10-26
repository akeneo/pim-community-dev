<?php

namespace Specification\Akeneo\Pim\Permission\Component\Merger;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociationCollection;
use Akeneo\Pim\Permission\Bundle\Entity\Query\ProductCategoryAccessQuery;
use Akeneo\Pim\Permission\Bundle\Entity\Query\ProductModelCategoryAccessQuery;
use Akeneo\Pim\Permission\Component\Merger\NotGrantedQuantifiedAssociationsMerger;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FieldSetterInterface;
use Akeneo\Pim\Permission\Component\NotGrantedDataMergerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class NotGrantedQuantifiedAssociationsMergerSpec extends ObjectBehavior
{
    function let(
        FieldSetterInterface $fieldSetter,
        ProductCategoryAccessQuery $productCategoryAccessQuery,
        ProductModelCategoryAccessQuery $productModelCategoryAccessQuery,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $this->beConstructedWith(
            $fieldSetter,
            $productCategoryAccessQuery,
            $productModelCategoryAccessQuery,
            $tokenStorage
        );
    }

    function it_implements_a_not_granted_data_merger_interface()
    {
        $this->shouldImplement(NotGrantedDataMergerInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NotGrantedQuantifiedAssociationsMerger::class);
    }

    function it_merges_not_granted_associated_products_in_entity_with_quantified_associations(
        FieldSetterInterface $fieldSetter,
        ProductCategoryAccessQuery $productCategoryAccessQuery,
        ProductModelCategoryAccessQuery $productModelCategoryAccessQuery,
        EntityWithQuantifiedAssociationsInterface $filteredEntityWithQuantifiedAssociations,
        EntityWithQuantifiedAssociationsInterface $fullEntityWithQuantifiedAssociations,
        UserInterface $user
    ) {
        $productCategoryAccessQuery->getGrantedProductIdentifiers(
            ['product_b', 'product_c'],
            $user
        )->willReturn(['product_c']);

        $productCategoryAccessQuery->getGrantedProductUuids(
            [Uuid::fromString('92e85ee7-40c1-4164-abae-fca396165ed1')],
            $user
        )->willReturn([]);

        $productModelCategoryAccessQuery->getGrantedProductModelCodes(
            ['product_model_a', 'product_model_b'],
            $user
        )->willReturn(['product_model_a', 'product_model_b']);

        $fullEntityWithQuantifiedAssociations->getQuantifiedAssociations()->willReturn(
            QuantifiedAssociationCollection::createFromNormalized([
                'PRODUCTSET' => [
                    'products' => [
                        ['identifier' => 'product_b', 'quantity' => 1],
                        ['identifier' => 'product_c', 'quantity' => 2],
                        ['uuid' => '92e85ee7-40c1-4164-abae-fca396165ed1', 'quantity' => 69],
                    ],
                    'product_models' => [
                        ['identifier' => 'product_model_a', 'quantity' => 3],
                        ['identifier' => 'product_model_b', 'quantity' => 4],
                    ],
                ],
            ])
        );

        $filteredEntityWithQuantifiedAssociations->getQuantifiedAssociations()->willReturn(
            QuantifiedAssociationCollection::createFromNormalized([
                    'PRODUCTSET' => [
                        'products' => [
                            ['identifier' => 'product_c', 'quantity' => 12],
                            ['identifier' => 'product_d', 'quantity' => 15],
                        ],
                        'product_models' => [
                            ['identifier' => 'product_model_a', 'quantity' => 13],
                            ['identifier' => 'product_model_b', 'quantity' => 14],
                        ],
                    ],
                ]
            ));

        $fieldSetter->setFieldData($fullEntityWithQuantifiedAssociations, 'quantified_associations', [
            'PRODUCTSET' => [
                'products' => [
                    ['identifier' => 'product_b', 'quantity' => 1],
                    ['uuid' => '92e85ee7-40c1-4164-abae-fca396165ed1', 'identifier' => null, 'quantity' => 69],
                    ['identifier' => 'product_c', 'quantity' => 12],
                    ['identifier' => 'product_d', 'quantity' => 15],
                ],
                'product_models' => [
                    ['identifier' => 'product_model_a', 'quantity' => 13],
                    ['identifier' => 'product_model_b', 'quantity' => 14],
                ],
            ]
        ])->shouldBeCalled();

        $this
            ->merge($filteredEntityWithQuantifiedAssociations, $fullEntityWithQuantifiedAssociations)
            ->shouldReturn($fullEntityWithQuantifiedAssociations);
    }

    function it_merges_not_granted_associated_product_models_in_entity_with_quantified_associations(
        FieldSetterInterface $fieldSetter,
        ProductCategoryAccessQuery $productCategoryAccessQuery,
        ProductModelCategoryAccessQuery $productModelCategoryAccessQuery,
        EntityWithQuantifiedAssociationsInterface $filteredEntityWithQuantifiedAssociations,
        EntityWithQuantifiedAssociationsInterface $fullEntityWithQuantifiedAssociations,
        UserInterface $user
    ) {
        $productCategoryAccessQuery->getGrantedProductIdentifiers(
            ['product_b', 'product_c'],
            $user
        )->willReturn(['product_b', 'product_c']);

        $productCategoryAccessQuery->getGrantedProductUuids(
            [],
            $user
        )->willReturn([]);

        $productModelCategoryAccessQuery->getGrantedProductModelCodes(
            ['product_model_a', 'product_model_b'],
            $user
        )->willReturn(['product_model_a']);

        $fullEntityWithQuantifiedAssociations->getQuantifiedAssociations()->willReturn(
            QuantifiedAssociationCollection::createFromNormalized([
                    'PRODUCTSET' => [
                        'products' => [
                            ['identifier' => 'product_b', 'quantity' => 1],
                            ['identifier' => 'product_c', 'quantity' => 2],
                        ],
                        'product_models' => [
                            ['identifier' => 'product_model_a', 'quantity' => 3],
                            ['identifier' => 'product_model_b', 'quantity' => 4],
                        ],
                    ],
                ]
            ));

        $filteredEntityWithQuantifiedAssociations->getQuantifiedAssociations()->willReturn(
            QuantifiedAssociationCollection::createFromNormalized([
                'PRODUCTSET' => [
                    'products' => [
                        ['identifier' => 'product_b', 'quantity' => 11],
                        ['identifier' => 'product_c', 'quantity' => 12],
                    ],
                    'product_models' => [
                        ['identifier' => 'product_model_a', 'quantity' => 13],
                        ['identifier' => 'product_model_c', 'quantity' => 15],
                    ],
                ],
            ])
        );

        $fieldSetter->setFieldData($fullEntityWithQuantifiedAssociations, 'quantified_associations', [
            'PRODUCTSET' => [
                'products' => [
                    ['identifier' => 'product_b', 'quantity' => 11],
                    ['identifier' => 'product_c', 'quantity' => 12],
                ],
                'product_models' => [
                    ['identifier' => 'product_model_b', 'quantity' => 4],
                    ['identifier' => 'product_model_a', 'quantity' => 13],
                    ['identifier' => 'product_model_c', 'quantity' => 15],
                ],
            ]
        ])->shouldBeCalled();

        $this
            ->merge($filteredEntityWithQuantifiedAssociations, $fullEntityWithQuantifiedAssociations)
            ->shouldReturn($fullEntityWithQuantifiedAssociations);
    }

    function it_remove_granted_associated_products_when_present_in_filtered_entity_with_quantified_associations(
        FieldSetterInterface $fieldSetter,
        ProductCategoryAccessQuery $productCategoryAccessQuery,
        ProductModelCategoryAccessQuery $productModelCategoryAccessQuery,
        EntityWithQuantifiedAssociationsInterface $filteredEntityWithQuantifiedAssociations,
        EntityWithQuantifiedAssociationsInterface $fullEntityWithQuantifiedAssociations,
        UserInterface $user
    ) {
        $productCategoryAccessQuery->getGrantedProductIdentifiers(
            ['product_a', 'product_b'],
            $user
        )->willReturn(['product_a', 'product_b']);

        $productCategoryAccessQuery->getGrantedProductUuids(
            [],
            $user
        )->willReturn([]);

        $productModelCategoryAccessQuery->getGrantedProductModelCodes(
            ['product_model_a', 'product_model_b'],
            $user
        )->willReturn(['product_model_a', 'product_model_b']);

        $fullEntityWithQuantifiedAssociations->getQuantifiedAssociations()->willReturn(
            QuantifiedAssociationCollection::createFromNormalized([
                'PRODUCTSET' => [
                    'products' => [
                        ['identifier' => 'product_a', 'quantity' => 1],
                        ['identifier' => 'product_b', 'quantity' => 2],
                    ],
                    'product_models' => [
                        ['identifier' => 'product_model_a', 'quantity' => 3],
                        ['identifier' => 'product_model_b', 'quantity' => 4],
                    ],
                ],
            ])
        );

        $filteredEntityWithQuantifiedAssociations->getQuantifiedAssociations()->willReturn(
            QuantifiedAssociationCollection::createFromNormalized([
                'PRODUCTSET' => [
                    'products' => [
                        ['identifier' => 'product_b', 'quantity' => 2],
                    ],
                    'product_models' => [
                        ['identifier' => 'product_model_a', 'quantity' => 3],
                        ['identifier' => 'product_model_b', 'quantity' => 5],
                    ],
                ],
            ])
        );

        $fieldSetter->setFieldData($fullEntityWithQuantifiedAssociations, 'quantified_associations', [
            'PRODUCTSET' => [
                'products' => [
                    ['identifier' => 'product_b', 'quantity' => 2],
                ],
                'product_models' => [
                    ['identifier' => 'product_model_a', 'quantity' => 3],
                    ['identifier' => 'product_model_b', 'quantity' => 5],
                ],
            ]
        ])->shouldBeCalled();

        $this
            ->merge($filteredEntityWithQuantifiedAssociations, $fullEntityWithQuantifiedAssociations)
            ->shouldReturn($fullEntityWithQuantifiedAssociations);
    }

    function it_remove_granted_associated_product_models_when_present_in_filtered_entity_with_quantified_associations(
        FieldSetterInterface $fieldSetter,
        ProductCategoryAccessQuery $productCategoryAccessQuery,
        ProductModelCategoryAccessQuery $productModelCategoryAccessQuery,
        EntityWithQuantifiedAssociationsInterface $filteredEntityWithQuantifiedAssociations,
        EntityWithQuantifiedAssociationsInterface $fullEntityWithQuantifiedAssociations,
        UserInterface $user
    ) {
        $productCategoryAccessQuery->getGrantedProductIdentifiers(
            ['product_a', 'product_b'],
            $user
        )->willReturn(['product_a', 'product_b']);

        $productCategoryAccessQuery->getGrantedProductUuids(
            [],
            $user
        )->willReturn([]);

        $productModelCategoryAccessQuery->getGrantedProductModelCodes(
            ['product_model_a', 'product_model_b'],
            $user
        )->willReturn(['product_model_a', 'product_model_b']);

        $fullEntityWithQuantifiedAssociations->getQuantifiedAssociations()->willReturn(
            QuantifiedAssociationCollection::createFromNormalized([
                'PRODUCTSET' => [
                    'products' => [
                        ['identifier' => 'product_a', 'quantity' => 1],
                        ['identifier' => 'product_b', 'quantity' => 2],
                    ],
                    'product_models' => [
                        ['identifier' => 'product_model_a', 'quantity' => 3],
                        ['identifier' => 'product_model_b', 'quantity' => 4],
                    ],
                ],
            ])
        );

        $filteredEntityWithQuantifiedAssociations->getQuantifiedAssociations()->willReturn(
            QuantifiedAssociationCollection::createFromNormalized([
                'PRODUCTSET' => [
                    'products' => [
                        ['identifier' => 'product_a', 'quantity' => 1],
                        ['identifier' => 'product_b', 'quantity' => 2],
                    ],
                    'product_models' => [
                        ['identifier' => 'product_model_a', 'quantity' => 3],
                    ],
                ],
            ])
        );

        $fieldSetter->setFieldData($fullEntityWithQuantifiedAssociations, 'quantified_associations', [
            'PRODUCTSET' => [
                'products' => [
                    ['identifier' => 'product_a', 'quantity' => 1],
                    ['identifier' => 'product_b', 'quantity' => 2],
                ],
                'product_models' => [
                    ['identifier' => 'product_model_a', 'quantity' => 3],
                ],
            ]
        ])->shouldBeCalled();

        $this
            ->merge($filteredEntityWithQuantifiedAssociations, $fullEntityWithQuantifiedAssociations)
            ->shouldReturn($fullEntityWithQuantifiedAssociations);
    }



    function it_throws_an_exception_if_filtered_subject_is_not_a_entity_with_quantified_associations(
        EntityWithQuantifiedAssociationsInterface $entityWithQuantifiedAssociations
    ) {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                get_class(new \stdClass()),
                EntityWithQuantifiedAssociationsInterface::class
            )
        )->during('merge', [new \stdClass(), $entityWithQuantifiedAssociations]);
    }

    function it_throws_an_exception_if_full_subject_is_not_a_entity_with_quantified_associations(
        EntityWithQuantifiedAssociationsInterface $entityWithQuantifiedAssociations
    ) {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                get_class(new \stdClass()),
                EntityWithQuantifiedAssociationsInterface::class
            )
        )->during('merge', [$entityWithQuantifiedAssociations, new \stdClass()]);
    }
}
