<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndModel\ProductModelNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $propertiesNormalizer)
    {
        $this->beConstructedWith($propertiesNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_indexing_normalization_only(ProductInterface $product)
    {
        $this->supportsNormalization($product, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
        $this->supportsNormalization($product, 'other_format')
            ->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')
            ->shouldReturn(false);
    }

    function it_normalizes_the_product_in_indexing_format(
        $propertiesNormalizer,
        ProductInterface $product
    ) {
        $propertiesNormalizer->normalize($product, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX, [])
            ->willReturn(['properties' => 'properties are normalized here']);

        $this->normalize($product, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(['properties' => 'properties are normalized here']);
    }
}
