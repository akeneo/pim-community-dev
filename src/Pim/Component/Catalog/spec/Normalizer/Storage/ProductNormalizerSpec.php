<?php

namespace spec\Pim\Component\Catalog\Normalizer\Storage;

use Pim\Component\Catalog\Model\ProductInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $propertiesNormalizer, NormalizerInterface $associationsNormalizer)
    {
        $this->beConstructedWith($propertiesNormalizer, $associationsNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Normalizer\Storage\ProductNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_storage_normalization_only(ProductInterface $product)
    {
        $this->supportsNormalization($product, 'storage')->shouldReturn(true);
        $this->supportsNormalization($product, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'storage')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
    }

    function it_normalizes_the_product_in_storage_format(
        $propertiesNormalizer,
        $associationsNormalizer,
        ProductInterface $product
    ) {
        $associationsNormalizer->normalize($product, 'storage', [])->willReturn('associations are normalized here');
        $propertiesNormalizer->normalize($product, 'storage', [])->willReturn(
            ['properties' => 'properties are normalized here']
        );

        $this->normalize($product, 'storage')->shouldReturn([
            'properties' => 'properties are normalized here',
            'associations' => 'associations are normalized here',
        ]);
    }
}
