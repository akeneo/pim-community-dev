<?php

namespace Specification\Akeneo\Pim\Structure\Component\Model;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValue;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;

class AttributeOptionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeOption::class);
    }

    function it_returns_null_when_there_is_no_translation()
    {
        $this->getOptionValue()->shouldReturn(null);
    }

    function its_code_is_a_string()
    {
        $this->setCode(1234);
        $this->getCode()->shouldReturn('1234');
    }

    function it_returns_the_expected_translation(
        AttributeOptionValueInterface $en,
        AttributeOptionValueInterface $fr
    ) {
        $en->getLocale()->willReturn('en');
        $fr->getLocale()->willReturn('fr');

        $en->setOption($this)->shouldBeCalled();
        $fr->setOption($this)->shouldBeCalled();

        $this->addOptionValue($en);
        $this->addOptionValue($fr);
        $this->setLocale('fr');

        $this->getOptionValue()->shouldReturn($fr);
    }

    function it_displays_an_attribute_option()
    {
        $value = new AttributeOptionValue();
        $value->setLabel('100');
        $value->setLocale('en_US');

        $this->setLocale('en_US');
        $this->addOptionValue($value);

        $this->__toString()->shouldReturn('100');
    }

    function it_has_no_default_sort_order()
    {
        $this->getSortOrder()->shouldBeNull();;
    }
}
