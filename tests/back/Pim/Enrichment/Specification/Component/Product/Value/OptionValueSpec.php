<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValueInterface;
use PhpSpec\ObjectBehavior;

class OptionValueSpec extends ObjectBehavior
{
    function it_returns_data()
    {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_option', 'option_a', 'ecommerce', 'en_US']);

        $this->getData()->shouldReturn('option_a');
    }

    function it_can_be_formatted_as_string() {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_option', 'red', 'ecommerce', 'en_US']);

        $this->__toString()->shouldReturn('[red]');
    }

    function it_compares_itself_to_the_same_option_value(
        OptionValueInterface $sameOptionValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_option', 'optionA', 'ecommerce', 'en_US']);

        $sameOptionValue->getLocaleCode()->willReturn('en_US');
        $sameOptionValue->getScopeCode()->willReturn('ecommerce');
        $sameOptionValue->getData()->willReturn('optionA');

        $this->isEqual($sameOptionValue)->shouldReturn(true);
    }

    function it_compares_itself_to_another_value_type(
        MetricValueInterface $metricValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_option', 'optionA', 'ecommerce', 'en_US']);

        $this->isEqual($metricValue)->shouldReturn(false);
    }

    function it_compares_itself_with_null_option_to_an_option_value_with_null_option(
        OptionValueInterface $sameOptionValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_option', null, 'ecommerce', 'en_US']);

        $sameOptionValue->getScopeCode()->willReturn('ecommerce');
        $sameOptionValue->getLocaleCode()->willReturn('en_US');
        $sameOptionValue->getData()->willReturn(null);

        $this->isEqual($sameOptionValue)->shouldReturn(true);
    }

    function it_compares_itself_with_null_option_to_a_different_option_value_with_null_option(
        OptionValueInterface $otherOptionValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_option', null, 'ecommerce', 'en_US']);

        $otherOptionValue->getScopeCode()->willReturn('mobile');
        $otherOptionValue->getLocaleCode()->willReturn('en_US');
        $otherOptionValue->getData()->willReturn(null);

        $this->isEqual($otherOptionValue)->shouldReturn(false);
    }

    function it_compares_itself_to_an_option_value_with_null_option(
        OptionValueInterface $otherOptionValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_option', 'optionA', 'ecommerce', 'en_US']);

        $otherOptionValue->getScopeCode()->willReturn('ecommerce');
        $otherOptionValue->getLocaleCode()->willReturn('en_US');
        $otherOptionValue->getData()->willReturn(null);

        $this->isEqual($otherOptionValue)->shouldReturn(false);
    }

    function it_compares_itself_to_an_option_value_with_different_option(
        OptionValueInterface $otherOptionValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_option', 'optionA', 'ecommerce', 'en_US']);

        $otherOptionValue->getScopeCode()->willReturn('ecommerce');
        $otherOptionValue->getLocaleCode()->willReturn('en_US');
        $otherOptionValue->getData()->willReturn('the_A_option');

        $this->isEqual($otherOptionValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_option_value(
        OptionValueInterface $otherOptionValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_option', 'optionA', 'ecommerce', 'en_US']);

        $otherOptionValue->getScopeCode()->willReturn('mobile');
        $otherOptionValue->getLocaleCode()->willReturn('en_US');
        $otherOptionValue->getData()->willReturn('optionA');

        $this->isEqual($otherOptionValue)->shouldReturn(false);
    }
}
