<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\MetricNormalizer;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MetricNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MetricNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_standard_format_and_metric_object_only(MetricInterface $metric)
    {
        $this->supportsNormalization($metric, 'standard')->shouldReturn(true);
        $this->supportsNormalization($metric, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
    }

    function it_normalizes_metric_in_standard_format_only_with_decimal_allowed(MetricInterface $metric)
    {
        $metric->getUnit()->willReturn('KILOGRAM');
        $metric->getData()->willReturn('12.1231');

        $this->normalize($metric, 'standard', ['is_decimals_allowed' => true])->shouldReturn([
            'amount' => '12.1231',
            'unit'   => 'KILOGRAM'
        ]);
    }

    function it_normalizes_metric_in_standard_format_only_with_decimal_disallowed(MetricInterface $metric)
    {
        $metric->getUnit()->willReturn('KILOGRAM');
        $metric->getData()->willReturn('12.0000');

        $this->normalize($metric, 'standard', ['is_decimals_allowed' => false])->shouldReturn([
            'amount' => 12,
            'unit'   => 'KILOGRAM'
        ]);
    }

    function it_normalizes_metric_in_standard_format_with_zero_amount(MetricInterface $metric)
    {
        $metric->getUnit()->willReturn('KILOGRAM');
        $metric->getData()->willReturn('0');

        $this->normalize($metric, 'standard', ['is_decimals_allowed' => false])->shouldReturn([
            'amount' => 0,
            'unit'   => 'KILOGRAM'
        ]);
    }

    function it_returns_data_if_it_is_not_a_numeric(MetricInterface $metric)
    {
        $metric->getUnit()->willReturn('KILOGRAM');
        $metric->getData()->willReturn('a_metric_data');

        $this->normalize($metric, 'standard', ['is_decimals_allowed' => false])->shouldReturn([
            'amount' => 'a_metric_data',
            'unit'   => 'KILOGRAM'
        ]);
    }

    function it_returns_empty_unit_if_it_is_an_empty_amount(
        MetricInterface $metric
    ) {
        $metric->getUnit()->willReturn('KILOGRAM');
        $metric->getData()->willReturn(null);

        $this->normalize($metric, 'standard', ['is_decimals_allowed' => true])->shouldReturn([
            'amount' => null,
            'unit'   => null
        ]);
    }

}
