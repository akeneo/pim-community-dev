<?php

namespace spec\Pim\Component\Catalog\Value;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;

class ScalarValueSpec extends ObjectBehavior
{
    function let(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', 'text');
    }

    function it_returns_data()
    {
        $this->getData()->shouldReturn('text');
    }
}
