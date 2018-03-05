<?php

namespace spec\PimEnterprise\Component\Catalog\Security\Updater\Setter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Catalog\Updater\Setter\FieldSetterInterface;
use PimEnterprise\Component\Catalog\Security\Updater\Setter\GrantedAssociationFieldSetter;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class GrantedAssociationFieldSetterSpec extends ObjectBehavior
{
    function let(
        FieldSetterInterface $categoryFieldSetter,
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $productRepository,
        ProductModelRepositoryInterface $productModelRepository
    ) {
        $this->beConstructedWith($categoryFieldSetter, $authorizationChecker, $productRepository, ['associations'], $productModelRepository);
    }

    function it_implements_a_filter_interface()
    {
        $this->shouldImplement(FieldSetterInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\Catalog\Security\Updater\Setter\GrantedAssociationFieldSetter');
    }

    function it_sets_associations($productRepository, $authorizationChecker, ProductInterface $product)
    {
        $data = ['X_SELL' => ['products' => ['associationA']]];

        $productRepository->findOneByIdentifier('associationA')->willReturn($product);
        $authorizationChecker->isGranted([Attributes::VIEW], $product)->willReturn(true);

        $this->shouldNotThrow(
            new ResourceAccessDeniedException(
                $product,
                'You cannot associate a product on which you have not a view permission.'
            )
        )->during('setFieldData', [$product, 'associations', $data, []]);
    }

    function it_throws_an_exception_if_an_association_is_not_granted_on_a_product(
        $productRepository,
        $authorizationChecker,
        $categoryFieldSetter,
        ProductInterface $product,
        ProductInterface $associatedProductA,
        ProductInterface $associatedProductB
    ) {
        $data = ['X_SELL' => ['products' => ['associationA', 'associationB']]];

        $productRepository->findOneByIdentifier('associationA')->willReturn($associatedProductA);
        $authorizationChecker->isGranted([Attributes::VIEW], $associatedProductA)->willReturn(true);

        $productRepository->findOneByIdentifier('associationB')->willReturn($associatedProductB);
        $authorizationChecker->isGranted([Attributes::VIEW], $associatedProductB)->willReturn(false);

        $categoryFieldSetter->setFieldData($product, 'associations', $data, [])->shouldBeCalled();

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
        $authorizationChecker,
        $categoryFieldSetter,
        ProductInterface $product,
        ProductInterface $associatedProductA,
        ProductInterface $associatedProductB
    ) {
        $data = ['X_SELL' => ['product_models' => ['associationA', 'associationB']]];

        $productModelRepository->findOneByIdentifier('associationA')->willReturn($associatedProductA);
        $authorizationChecker->isGranted([Attributes::VIEW], $associatedProductA)->willReturn(true);

        $productModelRepository->findOneByIdentifier('associationB')->willReturn($associatedProductB);
        $authorizationChecker->isGranted([Attributes::VIEW], $associatedProductB)->willReturn(false);

        $categoryFieldSetter->setFieldData($product, 'associations', $data, [])->shouldBeCalled();

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
