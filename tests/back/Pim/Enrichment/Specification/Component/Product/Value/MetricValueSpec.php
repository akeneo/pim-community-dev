<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;

class MetricValueSpec extends ObjectBehavior
{
    function it_returns_data(MetricInterface $metric)
    {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_metric', $metric, 'ecommerce', 'en_US']);

        $this->getData()->shouldBeAnInstanceOf(MetricInterface::class);
        $this->getData()->shouldReturn($metric);
    }

    function it_returns_amount_of_metric(MetricInterface $metric)
    {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_metric', $metric, 'ecommerce', 'en_US']);

        $metric->getData()->willReturn("12");

        $this->getAmount()->shouldReturn("12");
    }

    function it_returns_unit_of_metric(MetricInterface $metric)
    {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_metric', $metric, 'ecommerce', 'en_US']);

        $metric->getUnit()->willReturn('KILO');

        $this->getUnit()->shouldReturn('KILO');
    }

    function it_compares_itself_to_a_same_metric_value(
        MetricValueInterface $sameMetricValue,
        MetricInterface $sameMetric,
        MetricInterface $metric
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_metric', $metric, 'ecommerce', 'en_US']);

        $sameMetricValue->getScopeCode()->willReturn('ecommerce');
        $sameMetricValue->getLocaleCode()->willReturn('en_US');
        $sameMetricValue->getData()->willReturn($sameMetric);

        $metric->isEqual($sameMetric)->willReturn(true);

        $this->isEqual($sameMetricValue)->shouldReturn(true);
    }

    function it_compares_itself_as_null_to_a_null_metric(
        MetricValueInterface $sameMetricValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_metric', null, 'ecommerce', 'en_US']);

        $sameMetricValue->getScopeCode()->willReturn('ecommerce');
        $sameMetricValue->getLocaleCode()->willReturn('en_US');
        $sameMetricValue->getData()->willReturn(null);

        $this->isEqual($sameMetricValue)->shouldReturn(true);
    }

    function it_compares_itself_to_a_null_metric_value(
        MetricValueInterface $sameMetricValue,
        MetricInterface $metric
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_metric', $metric, 'ecommerce', 'en_US']);

        $sameMetricValue->getScopeCode()->willReturn('ecommerce');
        $sameMetricValue->getLocaleCode()->willReturn('en_US');
        $sameMetricValue->getData()->willReturn(null);

        $this->isEqual($sameMetricValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_metric(
        MetricValueInterface $sameMetricValue,
        MetricInterface $metric,
        MetricInterface $differentMetric
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_metric', $metric, 'ecommerce', 'en_US']);

        $sameMetricValue->getScopeCode()->willReturn('ecommerce');
        $sameMetricValue->getLocaleCode()->willReturn('en_US');
        $sameMetricValue->getData()->willReturn($differentMetric);

        $metric->isEqual($differentMetric)->willReturn(false);

        $this->isEqual($sameMetricValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_metric_value(
        MetricValueInterface $sameMetricValue,
        MetricInterface $metric,
        MetricInterface $sameMetric
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_metric', $metric, 'ecommerce', 'en_US']);

        $sameMetricValue->getScopeCode()->willReturn('mobile');
        $sameMetricValue->getLocaleCode()->willReturn('en_US');
        $sameMetricValue->getData()->willReturn($sameMetric);

        $metric->isEqual($sameMetric)->willReturn(true);

        $this->isEqual($sameMetricValue)->shouldReturn(false);
    }
}
