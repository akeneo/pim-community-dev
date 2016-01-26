<?php

namespace spec\Pim\Component\Catalog\EmptyChecker\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Component\Catalog\EmptyChecker\ProductValue\EmptyCheckerInterface;

class ChainedEmptyCheckerSpec extends ObjectBehavior
{
    function it_is_a_empty_checker()
    {
        $this->shouldImplement('Pim\Component\Catalog\EmptyChecker\ProductValue\EmptyCheckerInterface');
    }

    function it_contains_checkers(
        EmptyCheckerInterface $emptyCheckerOne,
        EmptyCheckerInterface $emptyCheckerTwo
    ) {
        $this->addEmptyChecker($emptyCheckerOne)->shouldReturn($this);
        $this->addEmptyChecker($emptyCheckerTwo)->shouldReturn($this);
    }

    function it_supports_product_value(ProductValueInterface $productValue, EmptyCheckerInterface $checker)
    {
        $this->addEmptyChecker($checker)->shouldReturn($this);
        $checker->supports($productValue)->shouldBeCalled();
        $this->supports($productValue);
    }

    function it_checks_not_empty_product_values(
        ProductValueInterface $value,
        EmptyCheckerInterface $baseChecker
    ) {
        $this->addEmptyChecker($baseChecker)->shouldReturn($this);
        $baseChecker->supports($value)->willReturn(true);
        $baseChecker->isEmpty($value)->willReturn(false);
        $this->isEmpty($value)->shouldReturn(false);
    }

    function it_checks_empty_product_values(
        ProductValueInterface $value,
        EmptyCheckerInterface $baseChecker
    ) {
        $this->addEmptyChecker($baseChecker)->shouldReturn($this);
        $baseChecker->supports($value)->willReturn(true);
        $baseChecker->isEmpty($value)->willReturn(true);
        $this->isEmpty($value)->shouldReturn(true);
    }

    function it_throws_exception_when_no_checker_supports_the_value(
        ProductValueInterface $value,
        AttributeInterface $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn('not_supported_attribute_type');

        $this->shouldThrow(
            new \LogicException(
                'No compatible EmptyCheckerInterface found for attribute type "not_supported_attribute_type".'
            )
        )->during(
            'isEmpty',
            [$value, []]
        );
    }
}
