<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use PhpSpec\ObjectBehavior;

class ScalarValueSpec extends ObjectBehavior
{
    function it_returns_data()
    {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_text', 'A nice text', 'ecommerce', 'en_US']);

        $this->getData()->shouldReturn('A nice text');
    }

    function it_returns_data_as_string()
    {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_text', 123, 'ecommerce', 'en_US']);

        $this->__toString()->shouldReturn('123');
    }

    function it_compares_itself_to_the_same_scalar_value(
        ScalarValue $sameScalarValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_text', 123, 'ecommerce', 'en_US']);

        $sameScalarValue->getData()->willReturn(123);
        $sameScalarValue->getLocaleCode()->willReturn('en_US');
        $sameScalarValue->getScopeCode()->willReturn('ecommerce');

        $this->isEqual($sameScalarValue)->shouldReturn(true);
    }

    function it_compares_itself_with_null_data_to_the_same_scalar_value_with_null_data(
        ScalarValue $sameScalarValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_text', null, 'ecommerce', 'en_US']);

        $sameScalarValue->getData()->willReturn(null);
        $sameScalarValue->getLocaleCode()->willReturn('en_US');
        $sameScalarValue->getScopeCode()->willReturn('ecommerce');

        $this->isEqual($sameScalarValue)->shouldReturn(true);
    }

    function it_compares_itself_to_the_same_scalar_value_with_null_data(
        ScalarValue $sameScalarValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_text', 123, 'ecommerce', 'en_US']);

        $sameScalarValue->getData()->willReturn(null);
        $sameScalarValue->getLocaleCode()->willReturn('en_US');
        $sameScalarValue->getScopeCode()->willReturn('ecommerce');

        $this->isEqual($sameScalarValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_scalar_value(
        ScalarValue $differentScalarValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_text', 123, 'ecommerce', 'en_US']);

        $differentScalarValue->getData()->willReturn(123);
        $differentScalarValue->getLocaleCode()->willReturn('fr_FR');
        $differentScalarValue->getScopeCode()->willReturn('ecommerce');

        $this->isEqual($differentScalarValue)->shouldReturn(false);
    }

    function it_compares_itself_to_another_value(
        MetricValueInterface $metricValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_text', 123, 'ecommerce', 'en_US']);

        $this->isEqual($metricValue)->shouldReturn(false);
    }
}
