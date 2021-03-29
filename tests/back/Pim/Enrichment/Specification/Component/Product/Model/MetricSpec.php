<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use PhpSpec\ObjectBehavior;

class MetricSpec extends ObjectBehavior
{
    function it_is_equal_to_another_metric_with_float_value(
        MetricInterface $anotherMetric
    ) {
        $this->beConstructedWith(
            'length_family',
            'centimeter',
            666.11,
            'meter',
            6.6611
        );

        $anotherMetric->getData()->willReturn(666.11);
        $anotherMetric->getUnit()->willReturn('centimeter');

        $this->isEqual($anotherMetric)->shouldReturn(true);
    }

    function it_is_equal_to_another_metric_with_string_value(
        MetricInterface $anotherMetric
    ) {
        $this->beConstructedWith(
            'length_family',
            'centimeter',
            666.11,
            'meter',
            6.6611
        );

        $anotherMetric->getData()->willReturn('666.11');
        $anotherMetric->getUnit()->willReturn('centimeter');

        $this->isEqual($anotherMetric)->shouldReturn(true);
    }

    function it_is_equal_to_another_metric_with_trailing_zeroes(
        MetricInterface $anotherMetric
    ) {
        $this->beConstructedWith(
            'length_family',
            'centimeter',
            666,
            'meter',
            6.6611
        );

        $anotherMetric->getData()->willReturn('666.00000');
        $anotherMetric->getUnit()->willReturn('centimeter');

        $this->isEqual($anotherMetric)->shouldReturn(true);
    }

    function it_is_not_equal_to_another_metric_with_float_value(
        MetricInterface $anotherMetric
    ) {
        $this->beConstructedWith(
            'length_family',
            'centimeter',
            666.11,
            'meter',
            6.6611
        );

        $anotherMetric->getData()->willReturn(666);
        $anotherMetric->getUnit()->willReturn('centimeter');

        $this->isEqual($anotherMetric)->shouldReturn(false);
    }

    function it_is_not_equal_to_another_metric_with_string_value(
        MetricInterface $anotherMetric
    ) {
        $this->beConstructedWith(
            'length_family',
            'centimeter',
            666.11,
            'meter',
            6.6611
        );

        $anotherMetric->getData()->willReturn('666');
        $anotherMetric->getUnit()->willReturn('centimeter');

        $this->isEqual($anotherMetric)->shouldReturn(false);
    }
}
