<?php

namespace spec\Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

class ScalarValueSpec extends ObjectBehavior
{
    function let(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', 'text');
    }

    function it_returns_data(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', 'text');

        $this->getData()->shouldReturn('text');
    }

    function it_returns_data_as_string(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', 123);

        $this->__toString()->shouldReturn('123');
    }

    function it_compares_itself_to_the_same_scalar_value(
        AttributeInterface $attribute,
        ScalarValue $sameScalarValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', 123);

        $sameScalarValue->getData()->willReturn(123);
        $sameScalarValue->getLocale()->willReturn('en_US');
        $sameScalarValue->getScope()->willReturn('ecommerce');

        $this->isEqual($sameScalarValue)->shouldReturn(true);
    }

    function it_compares_itself_with_null_data_to_the_same_scalar_value_with_null_data(
        AttributeInterface $attribute,
        ScalarValue $sameScalarValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', null);

        $sameScalarValue->getData()->willReturn(null);
        $sameScalarValue->getLocale()->willReturn('en_US');
        $sameScalarValue->getScope()->willReturn('ecommerce');

        $this->isEqual($sameScalarValue)->shouldReturn(true);
    }

    function it_compares_itself_to_the_same_scalar_value_with_null_data(
        AttributeInterface $attribute,
        ScalarValue $sameScalarValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', 123);

        $sameScalarValue->getData()->willReturn(null);
        $sameScalarValue->getLocale()->willReturn('en_US');
        $sameScalarValue->getScope()->willReturn('ecommerce');

        $this->isEqual($sameScalarValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_scalar_value(
        AttributeInterface $attribute,
        ScalarValue $differentScalarValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', 123);

        $differentScalarValue->getData()->willReturn(123);
        $differentScalarValue->getLocale()->willReturn('fr_FR');
        $differentScalarValue->getScope()->willReturn('ecommerce');

        $this->isEqual($differentScalarValue)->shouldReturn(false);
    }

    function it_compares_itself_to_another_value(
        AttributeInterface $attribute,
        MetricValueInterface $metricValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', 123);

        $this->isEqual($metricValue)->shouldReturn(false);
    }
}
