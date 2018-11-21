<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;
use PhpSpec\ObjectBehavior;

class OptionsValueSpec extends ObjectBehavior
{
    function it_returns_data()
    {
        $this->beConstructedThrough(
            'scopableLocalizableValue',
            ['my_options', ['option_a', 'option_b'], 'ecommerce', 'en_US']
        );

        $this->getData()->shouldReturn(['option_a', 'option_b']);
    }

    function it_checks_if_code_exist()
    {
        $this->beConstructedThrough(
            'scopableLocalizableValue',
            ['my_options', ['option_a', 'option_b'], 'ecommerce', 'en_US']
        );

        $this->hasCode('option_a')->shouldReturn(true);
        $this->hasCode('option_c')->shouldReturn(false);
    }

    function it_returns_codes() {
        $this->beConstructedThrough(
            'scopableLocalizableValue',
            ['my_options', ['option_a', 'option_b'], 'ecommerce', 'en_US']
        );

        $this->getOptionCodes()->shouldReturn(['option_a', 'option_b']);
    }

    function it_can_be_formatted_as_string() {
        $this->beConstructedThrough(
            'scopableLocalizableValue',
            ['my_options', ['option_a', 'option_b'], 'ecommerce', 'en_US']
        );

        $this->__toString()->shouldReturn('[option_a], [option_b]');
    }

    function it_compares_itself_to_a_same_options_value(
        OptionsValueInterface $sameOptionsValue
    ) {
        $this->beConstructedThrough(
            'scopableLocalizableValue',
            ['my_options', ['option_a', 'option_b'], 'ecommerce', 'en_US']
        );

        $sameOptionsValue->getLocaleCode()->willReturn('en_US');
        $sameOptionsValue->getScopeCode()->willReturn('ecommerce');
        $sameOptionsValue->getData()->willReturn(['option_a', 'option_b']);

        $this->isEqual($sameOptionsValue)->shouldReturn(true);
    }

    function it_compares_itself_to_a_same_options_value_without_options(
        OptionsValueInterface $sameOptionsValue
    ) {
        $this->beConstructedThrough(
            'scopableLocalizableValue',
            ['my_options', null, 'ecommerce', 'en_US']
        );

        $sameOptionsValue->getLocaleCode()->willReturn('en_US');
        $sameOptionsValue->getScopeCode()->willReturn('ecommerce');
        $sameOptionsValue->getData()->willReturn([]);

        $this->isEqual($sameOptionsValue)->shouldReturn(true);
    }

    function it_compares_itself_to_another_value_type(
        MetricValueInterface $metricValue
    ) {
        $this->beConstructedThrough(
            'scopableLocalizableValue',
            ['my_options', [], 'ecommerce', 'en_US']
        );

        $this->isEqual($metricValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_options_value(
        OptionsValueInterface $differentOptionsValue
    ) {
        $this->beConstructedThrough(
            'scopableLocalizableValue',
            ['my_options', [], 'ecommerce', 'en_US']
        );

        $differentOptionsValue->getScopeCode()->willReturn('mobile');
        $differentOptionsValue->getLocaleCode()->willReturn('en_US');

        $this->isEqual($differentOptionsValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_same_options_value_with_different_number_of_options(
        OptionsValueInterface $otherOptionsValue
    ) {
        $this->beConstructedThrough(
            'scopableLocalizableValue',
            ['my_options', ['option_a', 'option_b'], 'ecommerce', 'en_US']
        );

        $otherOptionsValue->getLocaleCode()->willReturn('en_US');
        $otherOptionsValue->getScopeCode()->willReturn('ecommerce');
        $otherOptionsValue->getData()->willReturn(['option_a']);

        $this->isEqual($otherOptionsValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_same_options_value_with_different_options(
        OptionsValueInterface $otherOptionsValue
    ) {
        $this->beConstructedThrough(
            'scopableLocalizableValue',
            ['my_options', ['option_a', 'option_b'], 'ecommerce', 'en_US']
        );

        $otherOptionsValue->getLocaleCode()->willReturn('en_US');
        $otherOptionsValue->getScopeCode()->willReturn('ecommerce');
        $otherOptionsValue->getData()->willReturn(['option_a', 'the_B_option']);

        $this->isEqual($otherOptionsValue)->shouldReturn(false);
    }
}
