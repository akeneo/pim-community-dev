<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\ProductNormalizer;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
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
        $this->shouldHaveType(ProductNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
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
