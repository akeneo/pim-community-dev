<?php

namespace spec\Pim\Component\Catalog\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\AttributeOptionValueInterface;

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

    function it_can_be_formatted_as_string_when_there_is_no_translation($option)
    {
        $option->getOptionValue()->willReturn(null);
        $option->getCode()->willReturn('red');

        $this->__toString()->shouldReturn('[red]');
    }

    function it_can_be_formatted_as_string_when_there_is_a_translation(
        $option,
        AttributeOptionValueInterface $translation
    ) {
        $translation->getValue()->willReturn('Blue');

        $option->getOptionValue()->willReturn($translation);
        $option->getCode()->shouldNotBeCalled();

        $this->__toString()->shouldReturn('Blue');
    }
}
