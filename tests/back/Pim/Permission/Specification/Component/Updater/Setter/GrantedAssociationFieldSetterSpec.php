<?php

namespace Specification\Akeneo\Pim\Permission\Component\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FieldSetterInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Query\ItemCategoryAccessQuery;
use Akeneo\Pim\Permission\Component\Exception\ResourceAccessDeniedException;
use Akeneo\Pim\Permission\Component\Updater\Setter\GrantedAssociationFieldSetter;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class GrantedAssociationFieldSetterSpec extends ObjectBehavior
{
    function let(
        FieldSetterInterface $associationFieldSetter,
        AuthorizationCheckerInterface $authorizationChecker,
        CursorableRepositoryInterface $productRepository,
        CursorableRepositoryInterface $productModelRepository,
        ItemCategoryAccessQuery $productCategoryAccessQuery,
        ItemCategoryAccessQuery $productModelCategoryAccessQuery,
        TokenStorageInterface $tokenStorage
    ) {
        $this->beConstructedWith(
            $associationFieldSetter,
            $authorizationChecker,
            $productRepository,
            ['associations'],
            $productModelRepository,
            $productCategoryAccessQuery,
            $productModelCategoryAccessQuery,
            $tokenStorage
        );
    }

    function it_implements_a_filter_interface()
    {
        $this->shouldImplement(FieldSetterInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GrantedAssociationFieldSetter::class);
    }

    function it_sets_associations(
        $productRepository,
        $tokenStorage,
        $productCategoryAccessQuery,
        ProductInterface $product,
        TokenInterface $token
    ) {
        $user = new User();
        $data = ['X_SELL' => ['products' => ['associationA']]];

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $productRepository->getItemsFromIdentifiers(['associationA'])->willReturn([$product]);
        $product->getId()->willReturn(1);
        $productCategoryAccessQuery->getGrantedItemIds([$product], $user)->willReturn([1 => 1]);

        $this->shouldNotThrow(
            new ResourceAccessDeniedException(
                $product,
                'You cannot associate a product on which you have not a view permission.'
            )
        )->during('setFieldData', [$product, 'associations', $data, []]);
    }

    function it_throws_an_exception_if_an_association_is_not_granted_on_a_product(
        $productRepository,
        $associationFieldSetter,
        $tokenStorage,
        $productCategoryAccessQuery,
        ProductInterface $product,
        ProductInterface $associatedProductA,
        ProductInterface $associatedProductB,
        TokenInterface $token
    ) {
        $user = new User();
        $data = ['X_SELL' => ['products' => ['associationA', 'associationB']]];

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $productRepository->getItemsFromIdentifiers(['associationA', 'associationB'])->willReturn([$associatedProductA, $associatedProductB]);
        $associationFieldSetter->setFieldData($product, 'associations', $data, [])->shouldBeCalled();
        $associatedProductA->getId()->willReturn(1);
        $associatedProductB->getId()->willReturn(2);
        $associatedProductB->getIdentifier()->willReturn('associationB');
        $productCategoryAccessQuery->getGrantedItemIds([$associatedProductA, $associatedProductB], $user)->willReturn([1 => 1]);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'associations',
                'product identifier',
                'The product does not exist',
                GrantedAssociationFieldSetter::class,
                'associationB'
            )
        )->during('setFieldData', [$product, 'associations', $data, []]);
    }

    function it_throws_an_exception_if_an_association_is_not_granted_on_a_product_model(
        $productModelRepository,
        $associationFieldSetter,
        $tokenStorage,
        $productModelCategoryAccessQuery,
        ProductInterface $product,
        ProductModelInterface $associatedProductModelA,
        ProductModelInterface $associatedProductModelB,
        TokenInterface $token
    ) {
        $user = new User();
        $data = ['X_SELL' => ['product_models' => ['associationA', 'associationB']]];

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $productModelRepository->getItemsFromIdentifiers(['associationA', 'associationB'])
            ->willReturn([$associatedProductModelA, $associatedProductModelB]);
        $associationFieldSetter->setFieldData($product, 'associations', $data, [])->shouldBeCalled();

        $associatedProductModelA->getId()->willReturn(1);
        $associatedProductModelB->getId()->willReturn(2);
        $associatedProductModelB->getCode()->willReturn('associationB');
        $productModelCategoryAccessQuery->getGrantedItemIds([$associatedProductModelA, $associatedProductModelB], $user)->willReturn([1 => 1]);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'associations',
                'product model identifier',
                'The product model does not exist',
                GrantedAssociationFieldSetter::class,
                'associationB'
            )
        )->during('setFieldData', [$product, 'associations', $data, []]);
    }
}
