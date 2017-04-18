<?php

namespace spec\Pim\Component\Catalog\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;

class OptionProductValueSpec extends ObjectBehavior
{
    function let(AttributeInterface $attribute, AttributeOptionInterface $option)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $option);
    }

    function it_returns_data($option)
    {
        $this->getData()->shouldBeAnInstanceOf(AttributeOptionInterface::class);
        $this->getData()->shouldReturn($option);
    }
}
