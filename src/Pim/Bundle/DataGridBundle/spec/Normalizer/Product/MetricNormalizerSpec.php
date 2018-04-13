<?php

namespace spec\Pim\Bundle\DataGridBundle\Normalizer\Product;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Normalizer\Product\MetricNormalizer;
use Pim\Component\Catalog\Model\MetricInterface;
use Pim\Component\Catalog\Value\MetricValueInterface;
use Prophecy\Argument;

class MetricNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MetricNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_datagrid_format_and_metric_value(MetricValueInterface $value)
    {
        $this->supportsNormalization($value, 'datagrid')->shouldReturn(true);
        $this->supportsNormalization($value, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'datagrid')->shouldReturn(false);
    }

    function it_normalizes_a_metric_value(
        MetricValueInterface $value,
        MetricInterface $metric
    ) {
        $value->getUnit()->willReturn('gram');
        $value->getAmount()->willReturn(0.5);
        $value->getData()->willReturn($metric);
        $metric->getFamily()->willReturn('Weight');

        $this->normalize($value, Argument::any(), Argument::any())->shouldReturn([
            'data' => [
                'unit' => 'gram',
                'amount' => 0.5,
                'family' => 'Weight',
            ]
        ]);
    }
}
