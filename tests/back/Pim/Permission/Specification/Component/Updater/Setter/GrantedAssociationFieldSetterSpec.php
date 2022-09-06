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
use Akeneo\Pim\Permission\Component\Query\ProductCategoryAccessQueryInterface;
use Akeneo\Pim\Permission\Component\Query\ProductModelCategoryAccessQueryInterface;
use Akeneo\Pim\Permission\Component\Updater\Setter\GrantedAssociationFieldSetter;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpParser\Node\Arg;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class GrantedAssociationFieldSetterSpec extends ObjectBehavior
{
    function let(
        FieldSetterInterface $associationFieldSetter,
        ProductCategoryAccessQueryInterface $productCategoryAccessQuery,
        ProductModelCategoryAccessQueryInterface $productModelCategoryAccessQuery,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn(new User());

        $this->beConstructedWith(
            $associationFieldSetter,
            ['associations'],
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
        ProductCategoryAccessQueryInterface $productCategoryAccessQuery,
        ProductInterface $product
    ) {
        $data = ['X_SELL' => ['products' => ['associationA']]];

        $productCategoryAccessQuery->getGrantedProductIdentifiers(['associationA'], Argument::type(UserInterface::class))
            ->willReturn(['associationA']);

        $this->shouldNotThrow(
            new ResourceAccessDeniedException(
                $product,
                'You cannot associate a product on which you have not a view permission.'
            )
        )->during('setFieldData', [$product, 'associations', $data, []]);
    }

    function it_throws_an_exception_if_an_associated_product_identifier_is_not_viewable(
        AssociationFieldSetter $associationFieldSetter,
        ProductCategoryAccessQueryInterface $productCategoryAccessQuery,
        ProductInterface $product,
    ) {
        $identifierData = ['X_SELL' => ['products' => ['associationA', 'associationB']]];
        $associationFieldSetter->setFieldData($product, 'associations', $identifierData, [])->shouldBeCalled();
        $productCategoryAccessQuery
            ->getGrantedProductIdentifiers(['associationA', 'associationB'], Argument::any())
            ->willReturn(['associationA']);

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
        AssociationFieldSetter $associationFieldSetter,
        ProductCategoryAccessQueryInterface $productCategoryAccessQuery,
        ProductInterface $product,
    ) {
        $uuidA = Uuid::fromString('aab1fcbf-bacb-430c-8a90-b9d34db2d676');
        $uuidB = Uuid::fromString('88d2457a-f7fb-495d-9e55-32a41159093a');

        $uuidData = ['X_SELL' => ['product_uuids' => [$uuidA->toString(), $uuidB->toString()]]];
        $associationFieldSetter->setFieldData($product, 'associations', $uuidData, [])->shouldBeCalled();
        $productCategoryAccessQuery
            ->getGrantedProductUuids([$uuidA, $uuidB], Argument::any())
            ->willReturn([$uuidA]);

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
        AssociationFieldSetter $associationFieldSetter,
        ProductModelCategoryAccessQueryInterface $productModelCategoryAccessQuery,
        ProductInterface $product,
    ) {
        $data = ['X_SELL' => ['product_models' => ['associationA', 'associationB']]];

        $associationFieldSetter->setFieldData($product, 'associations', $data, [])->shouldBeCalled();

        $productModelCategoryAccessQuery
            ->getGrantedProductModelCodes(['associationA', 'associationB'], Argument::any())
            ->willReturn(['associationA']);

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
