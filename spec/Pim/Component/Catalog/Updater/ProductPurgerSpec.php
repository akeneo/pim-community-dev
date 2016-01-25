<?php

namespace spec\Pim\Component\Catalog\Updater;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Component\Catalog\EmptyChecker\ProductValue\EmptyCheckerInterface;

class ProductPurgerSpec extends ObjectBehavior
{
    function it_is_a_product_purger()
    {
        $this->shouldImplement('Pim\Component\Catalog\Updater\ProductPurgerInterface');
    }

    function it_contains_empty_product_value_checker(
        EmptyCheckerInterface $emptyCheckerOne,
        EmptyCheckerInterface $emptyCheckerTwo
    ) {
        $this->addEmptyProductValueChecker($emptyCheckerOne)->shouldReturn($this);
        $this->addEmptyProductValueChecker($emptyCheckerTwo)->shouldReturn($this);
    }

    function it_removes_nothing_when_no_empty_product_values(
        ProductInterface $product,
        ProductValueInterface $value,
        EmptyCheckerInterface $baseChecker
    ) {
        $this->addEmptyProductValueChecker($baseChecker)->shouldReturn($this);
        $product->getValues()->willReturn([$value]);
        $baseChecker->supports($value)->willReturn(true);
        $baseChecker->isEmpty($value)->willReturn(false);
        $this->removeEmptyProductValues($product)->shouldReturn(false);
    }

    function it_removes_empty_product_values(
        ProductInterface $product,
        ProductValueInterface $value,
        ProductValueInterface $emptyValue,
        EmptyCheckerInterface $baseChecker
    ) {
        $this->addEmptyProductValueChecker($baseChecker)->shouldReturn($this);
        $product->getValues()->willReturn([$value, $emptyValue]);
        $baseChecker->supports($value)->willReturn(true);
        $baseChecker->isEmpty($value)->willReturn(false);
        $baseChecker->supports($emptyValue)->willReturn(true);
        $baseChecker->isEmpty($emptyValue)->willReturn(true);
        $product->removeValue($emptyValue)->shouldBeCalled();
        $this->removeEmptyProductValues($product)->shouldReturn(true);
    }

    function it_throws_exception_when_no_checker(
        ProductInterface $product,
        ProductValueInterface $value,
        AttributeInterface $attribute
    ) {
        $product->getValues()->willReturn([$value]);
        $value->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn('not_supported_attribute_type');

        $this->shouldThrow(
            new \LogicException(
                'No compatible EmptyCheckerInterface found for attribute type "not_supported_attribute_type".'
            )
        )->during(
            'removeEmptyProductValues',
            [$product, []]
        );
    }
}
