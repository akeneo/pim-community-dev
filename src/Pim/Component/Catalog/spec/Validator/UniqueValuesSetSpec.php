<?php

namespace spec\Pim\Component\Catalog\Validator;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Validator\UniqueValuesSet;

class UniqueValuesSetSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UniqueValuesSet::class);
    }

    function it_could_add_two_times_the_same_value(
        ProductValueInterface $productValue,
        ProductInterface $product,
        AttributeInterface $attribute
    ) {
        $product->getId()->willReturn('jean');
        $productValue->__toString()->willReturn('jean');
        $attribute->getCode()->willReturn('identifier');
        $productValue->getAttribute()->willReturn($attribute);

        $this->addValue($productValue, $product)->shouldReturn(true);
        $this->addValue($productValue, $product)->shouldReturn(true);
    }

    function it_cannot_add_two_times_the_same_value_if_the_products_do_not_exist(
        ProductValueInterface $productValue1,
        ProductInterface $product1,
        ProductValueInterface $productValue2,
        ProductInterface $product2,
        AttributeInterface $attribute
    ) {
        $product1->getId()->willReturn(null);
        $product2->getId()->willReturn(null);
        $productValue1->__toString()->willReturn('jean');
        $productValue2->__toString()->willReturn('jean');
        $attribute->getCode()->willReturn('identifier');
        $productValue1->getAttribute()->willReturn($attribute);
        $productValue2->getAttribute()->willReturn($attribute);

        $this->addValue($productValue1, $product1)->shouldReturn(true);
        $this->addValue($productValue2, $product2)->shouldReturn(false);
    }
}
