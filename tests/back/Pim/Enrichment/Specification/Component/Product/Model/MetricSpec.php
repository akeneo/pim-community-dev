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
            1/3,
            'meter',
            6.6611
        );

        $anotherMetric->getData()->willReturn(2/6);
        $anotherMetric->getUnit()->willReturn('centimeter');

        $this->isEqual($anotherMetric)->shouldReturn(true);
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

        $anotherMetric->getData()->willReturn('666.11');
        $anotherMetric->getUnit()->willReturn('centimeter');

        $this->isEqual($anotherMetric)->shouldReturn(false);
    }

    function it_is_equal_to_another_metric_with_trailing_zero_decimal(
        MetricInterface $anotherMetric
    ) {
        $this->beConstructedWith(
            'length_family',
            'centimeter',
            '666',
            'meter',
            6.6611
        );

        $anotherMetric->getData()->willReturn('666.00000');
        $anotherMetric->getUnit()->willReturn('centimeter');

        $this->isEqual($anotherMetric)->shouldReturn(true);
    }

    function it_is_not_equal_to_another_metric_with_trailing_zero_non_decimal(
        MetricInterface $anotherMetric
    ) {
        $this->beConstructedWith(
            'length_family',
            'centimeter',
            '6',
            'meter',
            6.6611
        );

        $anotherMetric->getData()->willReturn('600');
        $anotherMetric->getUnit()->willReturn('centimeter');

        $this->isEqual($anotherMetric)->shouldReturn(false);
    }

    function it_is_equal_to_another_metric_with_trailing_zeroes_on_both_sides(
        MetricInterface $anotherMetric
    ) {
        $this->beConstructedWith(
            'length_family',
            'centimeter',
            '666.00',
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

    function it_is_equal_to_another_metric_with_string_value_for_currency(
        MetricInterface $anotherMetric
    ) {
        $this->beConstructedWith(
            'length_family',
            'centimeter',
            '666.00000000000000000000000000000000000000000000000001',
            'meter',
            6.6611
        );

        $anotherMetric->getData()->willReturn('666.00000000000000000000000000000000000000000000000001');
        $anotherMetric->getUnit()->willReturn('centimeter');

        $this->isEqual($anotherMetric)->shouldReturn(true);
    }

    function it_is_not_equal_to_another_metric_with_string_value_for_currency(
        MetricInterface $anotherMetric
    ) {
        $this->beConstructedWith(
            'length_family',
            'centimeter',
            '666.00000000000000000000000000000000000000000000000001',
            'meter',
            6.6611
        );

        $anotherMetric->getData()->willReturn('666.00000000000000000000000000000000000000000000000002');
        $anotherMetric->getUnit()->willReturn('centimeter');

        $this->isEqual($anotherMetric)->shouldReturn(false);
    }

    function it_is_not_equal_to_another_metric_with_very_high_numbers(
        MetricInterface $anotherMetric
    ) {
        $this->beConstructedWith(
            'length_family',
            'centimeter',
            '61529519452809720693702583126814',
            'meter',
            6.6611
        );

        $anotherMetric->getData()->willReturn('61529519452809720000000000000000');
        $anotherMetric->getUnit()->willReturn('centimeter');

        $this->isEqual($anotherMetric)->shouldReturn(false);
    }
}
