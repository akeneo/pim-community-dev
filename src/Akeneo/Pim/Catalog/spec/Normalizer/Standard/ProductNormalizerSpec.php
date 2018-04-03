<?php

namespace spec\Pim\Component\Catalog\Normalizer\Standard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $propertiesNormalizer, NormalizerInterface $associationsNormalizer)
    {
        $this->beConstructedWith($propertiesNormalizer, $associationsNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Normalizer\Standard\ProductNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_standard_normalization_only(ProductInterface $product)
    {
        $this->supportsNormalization($product, 'standard')->shouldReturn(true);
        $this->supportsNormalization($product, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
    }

    function it_normalizes_the_product_in_standard_format(
        $propertiesNormalizer,
        $associationsNormalizer,
        ProductInterface $product
    ) {
        $associationsNormalizer->normalize($product, 'standard', [])->willReturn('associations are normalized here');
        $propertiesNormalizer->normalize($product, 'standard', [])->willReturn(
            ['properties' => 'properties are normalized here']
        );

        $this->normalize($product, 'standard')->shouldReturn([
            'properties' => 'properties are normalized here',
            'associations' => 'associations are normalized here',
        ]);
    }
}
