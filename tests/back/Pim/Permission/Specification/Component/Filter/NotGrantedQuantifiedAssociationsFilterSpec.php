<?php

namespace Specification\Akeneo\Pim\Permission\Component\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociationCollection;
use Akeneo\Pim\Permission\Bundle\Entity\Query\ProductCategoryAccessQuery;
use Akeneo\Pim\Permission\Bundle\Entity\Query\ProductModelCategoryAccessQuery;
use Akeneo\Pim\Permission\Component\Filter\NotGrantedQuantifiedAssociationsFilter;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Permission\Component\NotGrantedDataFilterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class NotGrantedQuantifiedAssociationsFilterSpec extends ObjectBehavior
{
    function let(
        AuthorizationCheckerInterface $authorizationChecker,
        ProductCategoryAccessQuery $productCategoryAccessQuery,
        ProductModelCategoryAccessQuery $productModelCategoryAccessQuery,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $this->beConstructedWith(
            $productCategoryAccessQuery,
            $productModelCategoryAccessQuery,
            $tokenStorage
        );
    }

    function it_implements_a_filter_interface()
    {
        $this->shouldImplement(NotGrantedDataFilterInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NotGrantedQuantifiedAssociationsFilter::class);
    }

    function it_removes_not_granted_associated_products_from_an_entity_with_quantified_associations(
        ProductCategoryAccessQuery $productCategoryAccessQuery,
        ProductModelCategoryAccessQuery $productModelCategoryAccessQuery,
        EntityWithQuantifiedAssociationsInterface $entityWithQuantifiedAssociations,
        UserInterface $user
    ) {
        $quantifiedAssociations = QuantifiedAssociationCollection::createFromNormalized([
            'COMPOSITION' => [
                'products' => [
                    ['identifier' => 'a_motor', 'quantity' => 1],
                    ['identifier' => 'a_wheel', 'quantity' => 4],
                ],
                'product_models' => [
                    ['identifier' => 'a_door', 'quantity' => 4],
                    ['identifier' => 'a_sheetmetal', 'quantity' => 1],
                ],
            ],
            'PRODUCTSET' => [
                'products' => [
                    ['identifier' => 'a_gps', 'quantity' => 1],
                ],
                'product_models' => [
                    ['identifier' => 'a_bicycle_rack', 'quantity' => 1],
                    ['identifier' => 'a_roof_rack', 'quantity' => 2],
                ],
            ],
        ]);

        $entityWithQuantifiedAssociations->getQuantifiedAssociations()->willReturn($quantifiedAssociations);
        $productCategoryAccessQuery->getGrantedProductIdentifiers(
            ['a_motor', 'a_wheel', 'a_gps'],
            $user
        )->willReturn(['a_motor']);

        $productModelCategoryAccessQuery->getGrantedProductModelCodes(
            ['a_door', 'a_sheetmetal', 'a_bicycle_rack', 'a_roof_rack'],
            $user
        )->willReturn(['a_door', 'a_sheetmetal', 'a_bicycle_rack', 'a_roof_rack']);

        $entityWithQuantifiedAssociations->filterQuantifiedAssociations(
            ['a_motor'],
            ['a_door', 'a_sheetmetal', 'a_bicycle_rack', 'a_roof_rack']
        )->shouldBeCalled();

        $this->filter($entityWithQuantifiedAssociations);
    }

    function it_removes_not_granted_associated_product_models_from_an_entity_with_quantified_associations(
        ProductCategoryAccessQuery $productCategoryAccessQuery,
        ProductModelCategoryAccessQuery $productModelCategoryAccessQuery,
        EntityWithQuantifiedAssociationsInterface $entityWithQuantifiedAssociations,
        UserInterface $user
    ) {
        $quantifiedAssociations = QuantifiedAssociationCollection::createFromNormalized([
            'COMPOSITION' => [
                'products' => [
                    ['identifier' => 'a_motor', 'quantity' => 1],
                    ['identifier' => 'a_wheel', 'quantity' => 4],
                ],
                'product_models' => [
                    ['identifier' => 'a_door', 'quantity' => 4],
                    ['identifier' => 'a_sheetmetal', 'quantity' => 1],
                ],
            ],
            'PRODUCTSET' => [
                'products' => [
                    ['identifier' => 'a_gps', 'quantity' => 1],
                ],
                'product_models' => [
                    ['identifier' => 'a_bicycle_rack', 'quantity' => 1],
                    ['identifier' => 'a_roof_rack', 'quantity' => 2],
                ],
            ],
        ]);

        $entityWithQuantifiedAssociations->getQuantifiedAssociations()->willReturn($quantifiedAssociations);
        $productCategoryAccessQuery->getGrantedProductIdentifiers(
            ['a_motor', 'a_wheel', 'a_gps'],
            $user
        )->willReturn(['a_motor', 'a_wheel', 'a_gps']);

        $productModelCategoryAccessQuery->getGrantedProductModelCodes(
            ['a_door', 'a_sheetmetal', 'a_bicycle_rack', 'a_roof_rack'],
            $user
        )->willReturn(['a_door']);

        $entityWithQuantifiedAssociations->filterQuantifiedAssociations(
            ['a_motor', 'a_wheel', 'a_gps'],
            ['a_door']
        );

        $this->filter($entityWithQuantifiedAssociations);
    }

    function it_throws_an_exception_if_subject_is_not_an_entity_with_quantified_associations()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                \stdClass::class,
                EntityWithQuantifiedAssociationsInterface::class
            )
        )->during('filter', [new \stdClass()]);
    }
}
