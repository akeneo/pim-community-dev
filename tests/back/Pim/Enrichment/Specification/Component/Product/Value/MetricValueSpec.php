<?php

namespace spec\Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

class MetricValueSpec extends ObjectBehavior
{
    function it_returns_data(AttributeInterface $attribute, MetricInterface $metric)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $metric);

        $this->getData()->shouldBeAnInstanceOf(MetricInterface::class);
        $this->getData()->shouldReturn($metric);
    }

    function it_returns_amount_of_metric(AttributeInterface $attribute, MetricInterface $metric)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $metric);

        $metric->getData()->willReturn(12);

        $this->getAmount()->shouldReturn(12);
    }

    function it_returns_unit_of_metric(AttributeInterface $attribute, MetricInterface $metric)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $metric);

        $metric->getUnit()->willReturn('KILO');

        $this->getUnit()->shouldReturn('KILO');
    }

    function it_compares_itself_to_a_same_metric_value(
        AttributeInterface $attribute,
        MetricValueInterface $sameMetricValue,
        MetricInterface $sameMetric,
        MetricInterface $metric
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $metric);

        $sameMetricValue->getScope()->willReturn('ecommerce');
        $sameMetricValue->getLocale()->willReturn('en_US');
        $sameMetricValue->getData()->willReturn($sameMetric);

        $metric->isEqual($sameMetric)->willReturn(true);

        $this->isEqual($sameMetricValue)->shouldReturn(true);
    }

    function it_compares_itself_as_null_to_a_null_metric(
        AttributeInterface $attribute,
        MetricValueInterface $sameMetricValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', null);

        $sameMetricValue->getScope()->willReturn('ecommerce');
        $sameMetricValue->getLocale()->willReturn('en_US');
        $sameMetricValue->getData()->willReturn(null);

        $this->isEqual($sameMetricValue)->shouldReturn(true);
    }

    function it_compares_itself_to_a_null_metric_value(
        AttributeInterface $attribute,
        MetricValueInterface $sameMetricValue,
        MetricInterface $metric
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $metric);

        $sameMetricValue->getScope()->willReturn('ecommerce');
        $sameMetricValue->getLocale()->willReturn('en_US');
        $sameMetricValue->getData()->willReturn(null);

        $this->isEqual($sameMetricValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_metric(
        AttributeInterface $attribute,
        MetricValueInterface $sameMetricValue,
        MetricInterface $metric,
        MetricInterface $differentMetric
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $metric);

        $sameMetricValue->getScope()->willReturn('ecommerce');
        $sameMetricValue->getLocale()->willReturn('en_US');
        $sameMetricValue->getData()->willReturn($differentMetric);

        $metric->isEqual($differentMetric)->willReturn(false);

        $this->isEqual($sameMetricValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_metric_value(
        AttributeInterface $attribute,
        MetricValueInterface $sameMetricValue,
        MetricInterface $metric,
        MetricInterface $sameMetric
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $metric);

        $sameMetricValue->getScope()->willReturn('mobile');
        $sameMetricValue->getLocale()->willReturn('en_US');
        $sameMetricValue->getData()->willReturn($sameMetric);

        $metric->isEqual($sameMetric)->willReturn(true);

        $this->isEqual($sameMetricValue)->shouldReturn(false);
    }
}
