<?php

namespace Specification\Akeneo\Pim\Permission\Component\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociationCollection;
use Akeneo\Pim\Permission\Bundle\Entity\Query\ProductCategoryAccessQuery;
use Akeneo\Pim\Permission\Bundle\Entity\Query\ProductModelCategoryAccessQuery;
use Akeneo\Pim\Permission\Component\Filter\NotGrantedQuantifiedAssociationsFilter;
use Akeneo\Pim\Permission\Component\NotGrantedDataFilterInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class NotGrantedQuantifiedAssociationsFilterSpec extends ObjectBehavior
{
    function let(
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
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $quantifiedAssociations = QuantifiedAssociationCollection::createFromNormalized([
            'COMPOSITION' => [
                'products' => [
                    ['identifier' => 'a_motor', 'quantity' => 1],
                    ['identifier' => 'a_wheel', 'quantity' => 4],
                    ['uuid' => $uuid1->toString(), 'quantity' => 42],
                ],
                'product_models' => [
                    ['identifier' => 'a_door', 'quantity' => 4],
                    ['identifier' => 'a_sheetmetal', 'quantity' => 1],
                ],
            ],
            'PRODUCTSET' => [
                'products' => [
                    ['identifier' => 'a_gps', 'quantity' => 1],
                    ['uuid' => $uuid2->toString(), 'quantity' => 69],
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

        $productCategoryAccessQuery->getGrantedProductUuids(
            Argument::that(function ($param) use ($uuid1, $uuid2) {
                return $param[0]->toString() === $uuid1->toString() &&
                $param[1]->toString() === $uuid2->toString();
            }),
            $user
        )->willReturn([$uuid1]);

        $productModelCategoryAccessQuery->getGrantedProductModelCodes(
            ['a_door', 'a_sheetmetal', 'a_bicycle_rack', 'a_roof_rack'],
            $user
        )->willReturn(['a_door', 'a_sheetmetal', 'a_bicycle_rack', 'a_roof_rack']);

        $entityWithQuantifiedAssociations->filterQuantifiedAssociations(
            ['a_motor'],
            [$uuid1],
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

        $productCategoryAccessQuery->getGrantedProductUuids(
            Argument::any(),
            $user
        )->willReturn([]);

        $productModelCategoryAccessQuery->getGrantedProductModelCodes(
            ['a_door', 'a_sheetmetal', 'a_bicycle_rack', 'a_roof_rack'],
            $user
        )->willReturn(['a_door']);

        $entityWithQuantifiedAssociations->filterQuantifiedAssociations(
            ['a_motor', 'a_wheel', 'a_gps'],
            [],
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
