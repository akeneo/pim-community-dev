<?php

namespace spec\PimEnterprise\Component\Catalog\Security\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Updater\Setter\FieldSetterInterface;
use PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException;
use Prophecy\Argument;

class GrantedAssociationFieldSetterSpec extends ObjectBehavior
{
    function let(FieldSetterInterface $categoryFieldSetter)
    {
        $this->beConstructedWith($categoryFieldSetter, ['associations']);
    }

    function it_implements_a_filter_interface()
    {
        $this->shouldImplement(FieldSetterInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\Catalog\Security\Updater\Setter\GrantedAssociationFieldSetter');
    }

    function it_sets_associations(ProductInterface $product)
    {
        $data = ['X_SELL' => ['products' => ['associationA']]];

        $this->shouldNotThrow(
            new ResourceAccessDeniedException(
                $product,
                'You cannot associate a product on which you have not a view permission.'
            )
        )->during('setFieldData', [$product, 'associations', $data, []]);
    }

    function it_throws_an_exception_if_a_association_is_not_granted($categoryFieldSetter, ProductInterface $product)
    {
        $data = ['X_SELL' => ['products' => ['associationA', 'associationB']]];

        $exception = new ResourceAccessDeniedException($product, 'message');
        $categoryFieldSetter->setFieldData($product, 'associations', $data, [])->willThrow($exception);

        $this->shouldThrow(
            new ResourceAccessDeniedException(
                $product,
                'You cannot associate a product on which you have not a view permission.',
                $exception
            )
        )->during('setFieldData', [$product, 'associations', $data, []]);
    }
}
