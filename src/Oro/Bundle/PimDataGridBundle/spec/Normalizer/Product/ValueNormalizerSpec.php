<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Normalizer\Product;

use Oro\Bundle\PimDataGridBundle\Normalizer\Product\ValueNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

class ValueNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $standardNormalizer)
    {
        $this->beConstructedWith($standardNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ValueNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_datagrid_format_and_product_value(ValueInterface $value)
    {
        $this->supportsNormalization($value, 'datagrid')->shouldReturn(true);
        $this->supportsNormalization($value, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'datagrid')->shouldReturn(false);
    }

    function it_normalizes_a_product_value(
        $standardNormalizer,
        ValueInterface $value
    ) {
        $data =  [
            'locale' => null,
            'scope'  => null,
            'data'   => 'product_value_data',
        ];

        $standardNormalizer->normalize($value, 'standard', [])->willReturn($data);

        $this->normalize($value)->shouldReturn($data);
    }
}
