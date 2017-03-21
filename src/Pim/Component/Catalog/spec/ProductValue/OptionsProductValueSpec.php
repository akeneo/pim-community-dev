<?php

namespace spec\Pim\Component\Catalog\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;

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
}
