<?php

namespace spec\PimEnterprise\Component\Catalog\Security\Updater\Setter;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Updater\Setter\FieldSetterInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class GrantedAssociationFieldSetterSpec extends ObjectBehavior
{
    function let(
        FieldSetterInterface $categoryFieldSetter,
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $productRepository
    ) {
        $this->beConstructedWith($categoryFieldSetter, $authorizationChecker, $productRepository, ['associations']);
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

    function it_throws_an_exception_if_a_association_is_not_granted(
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

        $exception = new ResourceAccessDeniedException(
            $associatedProductB,
            'You cannot associate a product on which you have not a view permission.'
        );
        $categoryFieldSetter->setFieldData($product, 'associations', $data, [])->willThrow($exception);

        $this->shouldThrow(
            new ResourceAccessDeniedException(
                $associatedProductB,
                'You cannot associate a product on which you have not a view permission.',
                $exception
            )
        )->during('setFieldData', [$product, 'associations', $data, []]);
    }
}
