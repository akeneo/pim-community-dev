<?php

namespace spec\Pim\Component\Catalog\Normalizer\Standard\Product;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\MetricInterface;
use Prophecy\Argument;

class MetricNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Normalizer\Standard\Product\MetricNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_standard_format_and_metric_object_only(MetricInterface $metric)
    {
        $otherObject = [];

        $this->supportsNormalization($metric, 'standard')->shouldReturn(true);
        $this->supportsNormalization($metric, 'other_format')->shouldReturn(false);
        $this->supportsNormalization($otherObject, 'standard')->shouldReturn(false);
        $this->supportsNormalization($otherObject, 'other_format')->shouldReturn(false);
    }

    function it_normalizes_metric_in_standard_format_only(MetricInterface $metric)
    {
        $metric->getUnit()->willReturn('KILOGRAM');
        $metric->getData()->willReturn('12.1231');

        $this->normalize($metric, 'standard')->shouldReturn([
            'amount' => '12.1231',
            'unit'   => 'KILOGRAM'
        ]);
    }
}
