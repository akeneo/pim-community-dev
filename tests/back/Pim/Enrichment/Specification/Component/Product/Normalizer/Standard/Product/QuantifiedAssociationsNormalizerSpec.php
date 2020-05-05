<?php

namespace AkeneoTest\Pim\Enrichment\Specification\Component\Product\Normalizer\Standard\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\QuantifiedAssociationsNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class QuantifiedAssociationsNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(QuantifiedAssociationsNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_standard_format_and_product_only(
        ProductInterface $product
    ) {
        $this->supportsNormalization($product, 'standard')->shouldReturn(true);
        $this->supportsNormalization($product, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
    }

    function it_normalizes_a_product_quantified_associations(EntityWithQuantifiedAssociationsInterface $product)
    {
        $product->normalizeQuantifiedAssociations()->willReturn('quantified associations are normalized here');

        $this->normalize($product, 'standard')->shouldReturn('quantified associations are normalized here');
    }
}
