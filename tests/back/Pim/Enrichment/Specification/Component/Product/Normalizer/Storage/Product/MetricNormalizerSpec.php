<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\Product;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\Product\MetricNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MetricNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $stdNormalizer)
    {
        $this->beConstructedWith($stdNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MetricNormalizer::class);
    }

    function it_support_metrics(MetricInterface $metric)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'storage')->shouldReturn(false);
        $this->supportsNormalization($metric, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($metric, 'storage')->shouldReturn(true);
    }

    function it_normalizes_product_assocations($stdNormalizer, MetricInterface $metric)
    {
        $stdNormalizer->normalize($metric, 'storage', ['context'])->willReturn(['std-metric']);

        $metric->getFamily()->willReturn('Length');

        $metric->getBaseData()->willReturn(100);
        $metric->getBaseUnit()->willReturn('M');

        $this->normalize($metric, 'storage', ['context'])->shouldReturn(
            ['std-metric', 'base_data' => 100, 'base_unit' => 'M', 'family' => 'Length']
        );
    }
}
