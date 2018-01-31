<?php

namespace spec\Pim\Component\Catalog\Value;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;

class DateValueSpec extends ObjectBehavior
{
    function let(AttributeInterface $attribute, \DateTime $date)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $date);
    }

    function it_returns_data($date)
    {
        $this->getData()->shouldBeAnInstanceOf(\DateTime::class);
        $this->getData()->shouldReturn($date);
    }
}
