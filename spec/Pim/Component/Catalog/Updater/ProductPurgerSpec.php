<?php

namespace spec\Pim\Component\Catalog\Updater;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Component\Catalog\EmptyChecker\ProductValue\EmptyCheckerInterface;

class ProductPurgerSpec extends ObjectBehavior
{
    function let(EmptyCheckerInterface $baseChecker)
    {
        $this->beConstructedWith($baseChecker);
    }

    function it_is_a_product_purger()
    {
        $this->shouldImplement('Pim\Component\Catalog\Updater\ProductPurgerInterface');
    }

    function it_removes_nothing_when_no_empty_product_values(
        $baseChecker,
        ProductInterface $product,
        ProductValueInterface $value
    ) {
        $product->getValues()->willReturn([$value]);
        $baseChecker->supports($value)->willReturn(true);
        $baseChecker->isEmpty($value)->willReturn(false);
        $this->removeEmptyProductValues($product)->shouldReturn(false);
    }

    function it_removes_empty_product_values(
        $baseChecker,
        ProductInterface $product,
        ProductValueInterface $value,
        ProductValueInterface $emptyValue
    ) {
        $product->getValues()->willReturn([$value, $emptyValue]);
        $baseChecker->supports($value)->willReturn(true);
        $baseChecker->isEmpty($value)->willReturn(false);
        $baseChecker->supports($emptyValue)->willReturn(true);
        $baseChecker->isEmpty($emptyValue)->willReturn(true);
        $product->removeValue($emptyValue)->shouldBeCalled();
        $this->removeEmptyProductValues($product)->shouldReturn(true);
    }
}
