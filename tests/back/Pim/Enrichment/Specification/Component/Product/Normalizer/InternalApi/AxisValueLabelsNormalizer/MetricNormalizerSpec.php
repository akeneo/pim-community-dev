<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\AxisValueLabelsNormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PhpSpec\ObjectBehavior;

class MetricNormalizerSpec extends ObjectBehavior
{
    function it_normalizes_a_metric_product_value(MetricValue $value)
    {
        $value->getAmount()->willReturn(10);
        $value->getUnit()->willReturn('KILOGRAM');
        $this->normalize($value, 'en_US')->shouldReturn('10 KILOGRAM');
    }

    function it_supports_only_metric_attributes()
    {
        $this->supports(AttributeTypes::METRIC)->shouldReturn(true);
        $this->supports(AttributeTypes::TEXT)->shouldReturn(false);
    }
}
