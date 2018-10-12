<?php

namespace spec\Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;
use PhpSpec\ObjectBehavior;

class OptionValueSpec extends ObjectBehavior
{
    function it_returns_data(AttributeInterface $attribute, AttributeOptionInterface $option)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $option);

        $this->getData()->shouldBeAnInstanceOf(AttributeOptionInterface::class);
        $this->getData()->shouldReturn($option);
    }

    function it_can_be_formatted_as_string_when_there_is_no_translation(
        AttributeInterface $attribute,
        AttributeOptionInterface $option
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $option);

        $option->getOptionValue()->willReturn(null);
        $option->getCode()->willReturn('red');

        $this->__toString()->shouldReturn('[red]');
    }

    function it_can_be_formatted_as_string_when_there_is_a_translation(
        AttributeInterface $attribute,
        AttributeOptionInterface $option,
        AttributeOptionValueInterface $translation
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $option);

        $translation->getValue()->willReturn('Blue');

        $option->getOptionValue()->willReturn($translation);
        $option->getCode()->shouldNotBeCalled();

        $this->__toString()->shouldReturn('Blue');
    }

    function it_compares_itself_to_the_same_option_value(
        AttributeInterface $attribute,
        AttributeOptionInterface $option,
        OptionValueInterface $sameOptionValue,
        AttributeOptionInterface $sameOption
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $option);

        $sameOptionValue->getLocale()->willReturn('en_US');
        $sameOptionValue->getScope()->willReturn('ecommerce');
        $sameOptionValue->getData()->willReturn($sameOption);

        $option->getCode()->willReturn('optionA');
        $sameOption->getCode()->willReturn('optionA');

        $option->getLocale()->willReturn('en_US');
        $sameOption->getLocale()->willReturn('en_US');

        $this->isEqual($sameOptionValue)->shouldReturn(true);
    }

    function it_compares_itself_to_another_value_type(
        AttributeInterface $attribute,
        MetricValueInterface $metricValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US');

        $this->isEqual($metricValue)->shouldReturn(false);
    }

    function it_compares_itself_with_null_option_to_an_option_value_with_null_option(
        AttributeInterface $attribute,
        OptionValueInterface $sameOptionValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', null);

        $sameOptionValue->getScope()->willReturn('ecommerce');
        $sameOptionValue->getLocale()->willReturn('en_US');
        $sameOptionValue->getData()->willReturn(null);

        $this->isEqual($sameOptionValue)->shouldReturn(true);
    }

    function it_compares_itself_with_null_option_to_a_different_option_value_with_null_option(
        AttributeInterface $attribute,
        OptionValueInterface $sameOptionValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', null);

        $sameOptionValue->getScope()->willReturn('mobile');
        $sameOptionValue->getLocale()->willReturn('en_US');
        $sameOptionValue->getData()->willReturn(null);

        $this->isEqual($sameOptionValue)->shouldReturn(false);
    }

    function it_compares_itself_to_an_option_value_with_null_option(
        AttributeInterface $attribute,
        AttributeOptionInterface $option,
        OptionValueInterface $sameOptionValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $option);

        $sameOptionValue->getScope()->willReturn('ecommerce');
        $sameOptionValue->getLocale()->willReturn('en_US');
        $sameOptionValue->getData()->willReturn(null);

        $this->isEqual($sameOptionValue)->shouldReturn(false);
    }

    function it_compares_itself_to_an_option_value_with_different_option(
        AttributeInterface $attribute,
        AttributeOptionInterface $option,
        OptionValueInterface $sameOptionValue,
        AttributeOptionInterface $differentOption
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $option);

        $sameOptionValue->getScope()->willReturn('ecommerce');
        $sameOptionValue->getLocale()->willReturn('en_US');
        $sameOptionValue->getData()->willReturn($differentOption);

        $option->getCode()->willReturn('optionA');
        $differentOption->getCode()->willReturn('the_A_option');

        $option->getLocale()->willReturn('en_US');
        $differentOption->getLocale()->willReturn('en_US');

        $this->isEqual($sameOptionValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_option_value(
        AttributeInterface $attribute,
        AttributeOptionInterface $option,
        OptionValueInterface $sameOptionValue,
        AttributeOptionInterface $sameOption
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $option);

        $sameOptionValue->getScope()->willReturn('mobile');
        $sameOptionValue->getLocale()->willReturn('en_US');
        $sameOptionValue->getData()->willReturn($sameOption);

        $this->isEqual($sameOptionValue)->shouldReturn(false);
    }
}
