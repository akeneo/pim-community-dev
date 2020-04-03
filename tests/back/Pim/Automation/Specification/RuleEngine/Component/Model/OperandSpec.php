<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Model;

use Akeneo\Pim\Automation\RuleEngine\Component\Model\Operand;
use PhpSpec\ObjectBehavior;

class OperandSpec extends ObjectBehavior
{
    function it_is_an_operand()
    {
        $this->beConstructedThrough('fromNormalized', [['field' => 'height']]);
        $this->shouldHaveType(Operand::class);
    }

    function it_can_represent_a_non_scopable_non_localizable_attribute_value()
    {
        $this->beConstructedThrough('fromNormalized', [['field' => 'height']]);
        $this->getAttributeCode()->shouldReturn('height');
        $this->getChannelCode()->shouldReturn(null);
        $this->getLocaleCode()->shouldReturn(null);
        $this->getCurrencyCode()->shouldReturn(null);
        $this->getConstantValue()->shouldReturn(null);
    }

    function it_can_represent_a_scopable_attribute_value()
    {
        $this->beConstructedThrough('fromNormalized', [['field' => 'height', 'scope' => 'ecommerce']]);
        $this->getAttributeCode()->shouldReturn('height');
        $this->getChannelCode()->shouldReturn('ecommerce');
        $this->getLocaleCode()->shouldReturn(null);
        $this->getCurrencyCode()->shouldReturn(null);
        $this->getConstantValue()->shouldReturn(null);
    }

    function it_can_represent_a_localizable_attribute_value()
    {
        $this->beConstructedThrough('fromNormalized', [['field' => 'height', 'locale' => 'en_US']]);
        $this->getAttributeCode()->shouldReturn('height');
        $this->getChannelCode()->shouldReturn(null);
        $this->getLocaleCode()->shouldReturn('en_US');
        $this->getCurrencyCode()->shouldReturn(null);
        $this->getConstantValue()->shouldReturn(null);
    }

    function it_can_represent_a_scopable_and_localizable_attribute_value()
    {
        $this->beConstructedThrough('fromNormalized', [['field' => 'height', 'scope' => 'ecommerce', 'locale' => 'en_US']]);
        $this->getAttributeCode()->shouldReturn('height');
        $this->getChannelCode()->shouldReturn('ecommerce');
        $this->getLocaleCode()->shouldReturn('en_US');
        $this->getCurrencyCode()->shouldReturn(null);
        $this->getConstantValue()->shouldReturn(null);
    }

    function it_can_represent_a_price_value()
    {
        $this->beConstructedThrough('fromNormalized', [['field' => 'base_price', 'currency' => 'USD']]);
        $this->getAttributeCode()->shouldReturn('base_price');
        $this->getChannelCode()->shouldReturn(null);
        $this->getLocaleCode()->shouldReturn(null);
        $this->getCurrencyCode()->shouldReturn('USD');
        $this->getConstantValue()->shouldReturn(null);
    }

    function it_can_represent_a_constant_value()
    {
        $this->beConstructedThrough('fromNormalized', [['value' => 14.5]]);
        $this->getAttributeCode()->shouldReturn(null);
        $this->getChannelCode()->shouldReturn(null);
        $this->getLocaleCode()->shouldReturn(null);
        $this->getCurrencyCode()->shouldReturn(null);
        $this->getConstantValue()->shouldReturn(14.5);
    }

    function it_casts_constant_value_to_float()
    {
        $this->beConstructedThrough('fromNormalized', [['value' => 3]]);
        $this->getConstantValue()->shouldReturn(3.0);
    }

    function it_cannot_be_constructed_with_a_non_numeric_value()
    {
        $this->beConstructedThrough('fromNormalized', [['value' => 'abcdef']]);
        $this->shouldThrow(new \InvalidArgumentException('Operand expects a numeric "value" key'))
            ->duringInstantiation();
    }

    function it_cannot_be_constructed_without_field_nor_value()
    {
        $this->beConstructedThrough('fromNormalized', [['locale' => 'en_US']]);
        $this->shouldThrow(new \InvalidArgumentException('An operation expects one of the "field" or "value" keys'))
            ->duringInstantiation();
    }

    function it_cannot_be_constructed_with_both_field_and_value()
    {
        $this->beConstructedThrough('fromNormalized', [['field' => 'width', 'value' => 3.1415927]]);
        $this->shouldThrow(new \InvalidArgumentException('An operation cannot be defined with both the "field" and "value" keys'))
            ->duringInstantiation();
    }
}
