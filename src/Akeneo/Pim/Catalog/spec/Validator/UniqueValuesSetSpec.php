<?php

namespace spec\Pim\Component\Catalog\Validator;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Validator\UniqueValuesSet;

class UniqueValuesSetSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UniqueValuesSet::class);
    }

    function it_could_add_two_times_the_same_value(
        ValueInterface $value,
        ProductInterface $product,
        AttributeInterface $attribute
    ) {
        $product->getId()->willReturn('jean');
        $value->__toString()->willReturn('jean');
        $attribute->getCode()->willReturn('identifier');
        $value->getAttribute()->willReturn($attribute);

        $this->addValue($value, $product)->shouldReturn(true);
        $this->addValue($value, $product)->shouldReturn(true);
    }

    function it_cannot_add_two_times_the_same_value_if_the_products_do_not_exist(
        ValueInterface $value1,
        ProductInterface $product1,
        ValueInterface $value2,
        ProductInterface $product2,
        AttributeInterface $attribute
    ) {
        $product1->getId()->willReturn(null);
        $product2->getId()->willReturn(null);
        $value1->__toString()->willReturn('jean');
        $value2->__toString()->willReturn('jean');
        $attribute->getCode()->willReturn('identifier');
        $value1->getAttribute()->willReturn($attribute);
        $value2->getAttribute()->willReturn($attribute);

        $this->addValue($value1, $product1)->shouldReturn(true);
        $this->addValue($value2, $product2)->shouldReturn(false);
    }
}
