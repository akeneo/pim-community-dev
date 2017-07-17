<?php

namespace spec\Pim\Component\Catalog\Value;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\MetricInterface;

class MetricValueSpec extends ObjectBehavior
{
    function let(AttributeInterface $attribute, MetricInterface $metric)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $metric);
    }

    function it_returns_data($metric)
    {
        $this->getData()->shouldBeAnInstanceOf(MetricInterface::class);
        $this->getData()->shouldReturn($metric);
    }

    function it_returns_amount_of_metric($metric)
    {
        $metric->getData()->willReturn(12);

        $this->getAmount()->shouldReturn(12);
    }

    function it_returns_unit_of_metric($metric)
    {
        $metric->getUnit()->willReturn('KILO');

        $this->getUnit()->shouldReturn('KILO');
    }
}
