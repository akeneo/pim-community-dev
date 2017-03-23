<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Normalizer\Indexing\ProductNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $propertiesNormalizer)
    {
        $this->beConstructedWith($propertiesNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_indexing_normalization_only(ProductInterface $product)
    {
        $this->supportsNormalization($product, 'indexing')->shouldReturn(true);
        $this->supportsNormalization($product, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'indexing')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
    }

    function it_normalizes_the_product_in_indexing_format(
        $propertiesNormalizer,
        ProductInterface $product
    ) {
        $propertiesNormalizer->normalize($product, 'indexing', [])->willReturn(
            ['properties' => 'properties are normalized here']
        );

        $this->normalize($product, 'indexing')->shouldReturn(
            [
                'properties' => 'properties are normalized here',
            ]
        );
    }
}
