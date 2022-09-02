<?php

namespace Specification\Akeneo\Pim\Permission\Component\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\AssociationFieldSetter;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FieldSetterInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Query\ItemCategoryAccessQuery;
use Akeneo\Pim\Permission\Component\Exception\ResourceAccessDeniedException;
use Akeneo\Pim\Permission\Component\Updater\Setter\GrantedAssociationFieldSetter;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class GrantedAssociationFieldSetterSpec extends ObjectBehavior
{
    function let(
        FieldSetterInterface $associationFieldSetter,
        ProductRepositoryInterface $productRepository,
        ProductModelRepositoryInterface $productModelRepository,
        ItemCategoryAccessQuery $productCategoryAccessQuery,
        ItemCategoryAccessQuery $productModelCategoryAccessQuery,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn(new User());

        $this->beConstructedWith(
            $associationFieldSetter,
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
        ProductRepositoryInterface $productRepository,
        ItemCategoryAccessQuery $productCategoryAccessQuery,
        ProductInterface $product,
    ) {
        $data = ['X_SELL' => ['products' => ['associationA']]];

        $productRepository->findBy(['identifier' => ['associationA']])->willReturn([$product]);
        $product->getUuid()->willReturn(Uuid::fromString('aab1fcbf-bacb-430c-8a90-b9d34db2d676'));
        $productCategoryAccessQuery->getGrantedProductUuids([$product], Argument::type(UserInterface::class))
            ->willReturn(['aab1fcbf-bacb-430c-8a90-b9d34db2d676']);

        $this->shouldNotThrow(
            new ResourceAccessDeniedException(
                $product,
                'You cannot associate a product on which you have not a view permission.'
            )
        )->during('setFieldData', [$product, 'associations', $data, []]);
    }

    function it_throws_an_exception_if_an_associated_product_identifier_is_not_viewable(
        ProductRepositoryInterface $productRepository,
        AssociationFieldSetter $associationFieldSetter,
        ItemCategoryAccessQuery $productCategoryAccessQuery,
        ProductInterface $product,
        ProductInterface $associatedProductA,
        ProductInterface $associatedProductB,
    ) {
        $uuidA = Uuid::fromString('aab1fcbf-bacb-430c-8a90-b9d34db2d676');
        $uuidB = Uuid::fromString('88d2457a-f7fb-495d-9e55-32a41159093a');
        $associatedProductA->getUuid()->willReturn($uuidA);
        $associatedProductB->getUuid()->willReturn($uuidB);
        $associatedProductB->getIdentifier()->willReturn('associationB');

        $identifierData = ['X_SELL' => ['products' => ['associationA', 'associationB']]];

        $productRepository->findBy(['identifier' => ['associationA', 'associationB']])->willReturn([$associatedProductA, $associatedProductB]);

        $associationFieldSetter->setFieldData($product, 'associations', $identifierData, [])->shouldBeCalled();

        $productCategoryAccessQuery->getGrantedProductUuids([$associatedProductA, $associatedProductB], Argument::type(UserInterface::class))
            ->willReturn([$uuidA->toString()]);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'associations',
                'product identifier',
                'The product does not exist',
                GrantedAssociationFieldSetter::class,
                'associationB'
            )
        )->during('setFieldData', [$product, 'associations', $identifierData, []]);
    }

    function it_throws_an_exception_if_an_associated_product_uuid_is_not_viewable(
        ProductRepositoryInterface $productRepository,
        AssociationFieldSetter $associationFieldSetter,
        ItemCategoryAccessQuery $productCategoryAccessQuery,
        ProductInterface $product,
        ProductInterface $associatedProductA,
        ProductInterface $associatedProductB,
    ) {
        $uuidA = Uuid::fromString('aab1fcbf-bacb-430c-8a90-b9d34db2d676');
        $uuidB = Uuid::fromString('88d2457a-f7fb-495d-9e55-32a41159093a');
        $associatedProductA->getUuid()->willReturn($uuidA);
        $associatedProductB->getUuid()->willReturn($uuidB);

        $uuidData = ['X_SELL' => ['product_uuids' => [$uuidA->toString(), $uuidB->toString()]]];

        $productRepository->findBy(['uuid' => [$uuidA->toString(), $uuidB->toString()]])
            ->willReturn([$associatedProductA, $associatedProductB]);

        $associationFieldSetter->setFieldData($product, 'associations', $uuidData, [])->shouldBeCalled();

        $productCategoryAccessQuery->getGrantedProductUuids([$associatedProductA, $associatedProductB], Argument::type(UserInterface::class))
            ->willReturn([$uuidA->toString()]);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'associations',
                'product uuid',
                'The product does not exist',
                GrantedAssociationFieldSetter::class,
                '88d2457a-f7fb-495d-9e55-32a41159093a'
            )
        )->during('setFieldData', [$product, 'associations', $uuidData, []]);
    }

    function it_throws_an_exception_if_an_association_is_not_granted_on_a_product_model(
        ProductModelRepositoryInterface $productModelRepository,
        AssociationFieldSetter $associationFieldSetter,
        ItemCategoryAccessQuery $productModelCategoryAccessQuery,
        ProductInterface $product,
        ProductModelInterface $associatedProductModelA,
        ProductModelInterface $associatedProductModelB,
    ) {
        $data = ['X_SELL' => ['product_models' => ['associationA', 'associationB']]];

        $productModelRepository->getItemsFromIdentifiers(['associationA', 'associationB'])
            ->willReturn([$associatedProductModelA, $associatedProductModelB]);
        $associationFieldSetter->setFieldData($product, 'associations', $data, [])->shouldBeCalled();

        $associatedProductModelA->getId()->willReturn(1);
        $associatedProductModelB->getId()->willReturn(2);
        $associatedProductModelB->getCode()->willReturn('associationB');
        $productModelCategoryAccessQuery->getGrantedItemIds([$associatedProductModelA, $associatedProductModelB], Argument::type(UserInterface::class))->willReturn([1 => 1]);

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
