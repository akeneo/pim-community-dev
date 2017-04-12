<?php

namespace spec\Pim\Bundle\DataGridBundle\Normalizer\Product;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductValueInterface;

class ProductValueNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $standardNormalizer)
    {
        $this->beConstructedWith($standardNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGridBundle\Normalizer\Product\ProductValueNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_datagrid_format_and_product_value(ProductValueInterface $productValue)
    {
        $this->supportsNormalization($productValue, 'datagrid')->shouldReturn(true);
        $this->supportsNormalization($productValue, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'datagrid')->shouldReturn(false);
    }

    function it_normalizes_a_product_value(
        $standardNormalizer,
        ProductValueInterface $productValue
    ) {
        $data =  [
            'locale' => null,
            'scope'  => null,
            'data'   => 'product_value_data',
        ];

        $standardNormalizer->normalize($productValue, 'standard', [])->willReturn($data);

        $this->normalize($productValue)->shouldReturn($data);
    }
}
