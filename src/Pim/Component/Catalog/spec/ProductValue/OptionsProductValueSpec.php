<?php

namespace spec\Pim\Component\Catalog\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\AttributeOptionValueInterface;

class OptionsProductValueSpec extends ObjectBehavior
{
    function let(AttributeInterface $attribute, AttributeOptionInterface $optionA, AttributeOptionInterface $optionB)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', [$optionA, $optionB]);
    }

    function it_returns_data($optionA, $optionB)
    {
        $this->getData()->shouldReturn([$optionA, $optionB]);
    }

    function it_checks_if_code_exist($optionA)
    {
        $optionA->getCode()->willReturn('option_a');
        $this->hasCode('option_a')->shouldReturn(true);
        $this->hasCode('option_c')->shouldReturn(false);
    }

    function it_returns_codes($optionA, $optionB)
    {
        $optionA->getCode()->willReturn('option_a');
        $optionB->getCode()->willReturn('option_b');
        $this->getOptionCodes()->shouldReturn(['option_a', 'option_b']);
    }

    function it_can_be_formatted_as_string_when_there_is_no_translation($optionA, $optionB)
    {
        $optionA->getOptionValue()->willReturn(null);
        $optionA->getCode()->willReturn('option_a');

        $optionB->getOptionValue()->willReturn(null);
        $optionB->getCode()->willReturn('option_b');

        $this->__toString()->shouldReturn('[option_a], [option_b]');
    }

    function it_can_be_formatted_as_string_when_there_is_a_translation(
        $optionA,
        $optionB,
        AttributeOptionValueInterface $translationA,
        AttributeOptionValueInterface $translationB
    ) {
        $optionA->getOptionValue()->willReturn($translationA);
        $translationA->getValue()->willReturn('Translation A');
        $optionA->getCode()->shouldNotBeCalled();

        $optionB->getOptionValue()->willReturn($translationB);
        $translationB->getValue()->willReturn('Translation B');
        $optionB->getCode()->shouldNotBeCalled();

        $this->__toString()->shouldReturn('Translation A, Translation B');
    }
}
