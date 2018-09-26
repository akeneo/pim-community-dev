<?php

namespace spec\Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;
use PhpSpec\ObjectBehavior;

class OptionsValueSpec extends ObjectBehavior
{
    function it_returns_data(
        AttributeInterface $attribute,
        AttributeOptionInterface $optionA,
        AttributeOptionInterface $optionB
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', [$optionA, $optionB]);

        $this->getData()->shouldReturn([$optionA, $optionB]);
    }

    function it_checks_if_code_exist(
        AttributeInterface $attribute,
        AttributeOptionInterface $optionA,
        AttributeOptionInterface $optionB
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', [$optionA, $optionB]);

        $optionA->getCode()->willReturn('option_a');

        $this->hasCode('option_a')->shouldReturn(true);
        $this->hasCode('option_c')->shouldReturn(false);
    }

    function it_returns_codes(
        AttributeInterface $attribute,
        AttributeOptionInterface $optionA,
        AttributeOptionInterface $optionB
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', [$optionA, $optionB]);

        $optionA->getCode()->willReturn('option_a');
        $optionB->getCode()->willReturn('option_b');
        $this->getOptionCodes()->shouldReturn(['option_a', 'option_b']);
    }

    function it_can_be_formatted_as_string_when_there_is_no_translation(
        AttributeInterface $attribute,
        AttributeOptionInterface $optionA,
        AttributeOptionInterface $optionB
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', [$optionA, $optionB]);

        $optionA->getOptionValue()->willReturn(null);
        $optionA->getCode()->willReturn('option_a');

        $optionB->getOptionValue()->willReturn(null);
        $optionB->getCode()->willReturn('option_b');

        $this->__toString()->shouldReturn('[option_a], [option_b]');
    }

    function it_can_be_formatted_as_string_when_there_is_a_translation(
        AttributeInterface $attribute,
        AttributeOptionInterface $optionA,
        AttributeOptionInterface $optionB,
        AttributeOptionValueInterface $translationA,
        AttributeOptionValueInterface $translationB
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', [$optionA, $optionB]);

        $optionA->getOptionValue()->willReturn($translationA);
        $translationA->getValue()->willReturn('Translation A');
        $optionA->getCode()->shouldNotBeCalled();

        $optionB->getOptionValue()->willReturn($translationB);
        $translationB->getValue()->willReturn('Translation B');
        $optionB->getCode()->shouldNotBeCalled();

        $this->__toString()->shouldReturn('Translation A, Translation B');
    }

    function it_compares_itself_to_a_same_options_value(
        AttributeInterface $attribute,
        OptionsValueInterface $sameOptionsValue,
        AttributeOptionInterface $optionA,
        AttributeOptionInterface $optionB,
        AttributeOptionInterface $sameOptionA,
        AttributeOptionInterface $sameOptionB
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', [$optionA, $optionB]);

        $sameOptionsValue->getLocale()->willReturn('en_US');
        $sameOptionsValue->getScope()->willReturn('ecommerce');
        $sameOptionsValue->getData()->willReturn([$sameOptionA, $sameOptionB]);

        $optionA->getCode()->willReturn('option_a');
        $sameOptionA->getCode()->willReturn('option_a');

        $optionB->getCode()->willReturn('option_b');
        $sameOptionB->getCode()->willReturn('option_b');

        $this->isEqual($sameOptionsValue)->shouldReturn(true);
    }

    function it_compares_itself_to_a_same_options_value_without_options(
        AttributeInterface $attribute,
        OptionsValueInterface $sameOptionsValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', []);

        $sameOptionsValue->getLocale()->willReturn('en_US');
        $sameOptionsValue->getScope()->willReturn('ecommerce');
        $sameOptionsValue->getData()->willReturn([]);

        $this->isEqual($sameOptionsValue)->shouldReturn(true);
    }

    function it_compares_itself_to_another_value_type(
        AttributeInterface $attribute,
        MetricValueInterface $metricValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', []);

        $this->isEqual($metricValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_options_value(
        AttributeInterface $attribute,
        OptionsValueInterface $differentOptionsValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', []);

        $differentOptionsValue->getScope()->willReturn('mobile');
        $differentOptionsValue->getLocale()->willReturn('en_US');

        $this->isEqual($differentOptionsValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_same_options_value_with_different_number_of_options(
        AttributeInterface $attribute,
        OptionsValueInterface $sameOptionsValue,
        AttributeOptionInterface $optionA,
        AttributeOptionInterface $optionB,
        AttributeOptionInterface $sameOptionA
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', [$optionA, $optionB]);

        $sameOptionsValue->getLocale()->willReturn('en_US');
        $sameOptionsValue->getScope()->willReturn('ecommerce');
        $sameOptionsValue->getData()->willReturn([$sameOptionA]);

        $this->isEqual($sameOptionsValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_same_options_value_with_different_options(
        AttributeInterface $attribute,
        OptionsValueInterface $sameOptionsValue,
        AttributeOptionInterface $optionA,
        AttributeOptionInterface $optionB,
        AttributeOptionInterface $sameOptionA,
        AttributeOptionInterface $differentOptionB
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', [$optionA, $optionB]);

        $sameOptionsValue->getLocale()->willReturn('en_US');
        $sameOptionsValue->getScope()->willReturn('ecommerce');
        $sameOptionsValue->getData()->willReturn([$sameOptionA, $differentOptionB]);

        $optionA->getCode()->willReturn('option_a');
        $sameOptionA->getCode()->willReturn('option_a');

        $optionB->getCode()->willReturn('option_b');
        $differentOptionB->getCode()->willReturn('the_B_option');

        $this->isEqual($sameOptionsValue)->shouldReturn(false);
    }
}
