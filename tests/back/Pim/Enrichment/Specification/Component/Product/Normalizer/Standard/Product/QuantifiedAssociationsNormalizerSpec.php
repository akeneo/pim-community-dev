<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
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
        $product->normalizeQuantifiedAssociations()->willReturn([
            'PACK' => [
                'products' => [
                    ['identifier' => 'A', 'quantity' => 2],
                    ['identifier' => 'B', 'quantity' => 3],
                    ['identifier' => 'C', 'quantity' => 4],
                ],
            ],
        ]);

        $this->normalize($product, 'standard')->shouldReturn([
            'PACK' => [
                'products' => [
                    ['identifier' => 'A', 'quantity' => 2],
                    ['identifier' => 'B', 'quantity' => 3],
                    ['identifier' => 'C', 'quantity' => 4],
                ],
            ],
        ]);
    }

    function it_normalizes_a_product_variant_quantified_associations(
        ProductModelInterface $product_model,
        ProductModelInterface $variant_level_1,
        ProductInterface $variant_level_2
    ) {
        $product_model->getParent()->willReturn(null);
        $variant_level_1->getParent()->willReturn($product_model);
        $variant_level_2->getParent()->willReturn($variant_level_1);

        $product_model->normalizeQuantifiedAssociations()->willReturn([
            'PACK' => [
                'products' => [
                    ['identifier' => 'A', 'quantity' => 2],
                    ['identifier' => 'B', 'quantity' => 3],
                    ['identifier' => 'C', 'quantity' => 4],
                ],
            ],
        ]);
        $variant_level_1->normalizeQuantifiedAssociations()->willReturn([
            'PACK' => [
                'products' => [
                    ['identifier' => 'A', 'quantity' => 5],
                    ['identifier' => 'B', 'quantity' => 6],
                ],
            ],
        ]);
        $variant_level_2->normalizeQuantifiedAssociations()->willReturn([
            'PACK' => [
                'products' => [
                    ['identifier' => 'A', 'quantity' => 7],
                    ['identifier' => 'D', 'quantity' => 8],
                ],
            ],
        ]);

        $this->normalize($product_model, 'standard')->shouldReturn([
            'PACK' => [
                'products' => [
                    ['identifier' => 'A', 'quantity' => 2],
                    ['identifier' => 'B', 'quantity' => 3],
                    ['identifier' => 'C', 'quantity' => 4],
                ],
            ],
        ]);
        $this->normalize($variant_level_1, 'standard')->shouldReturn([
            'PACK' => [
                'products' => [
                    ['identifier' => 'A', 'quantity' => 5],
                    ['identifier' => 'B', 'quantity' => 6],
                    ['identifier' => 'C', 'quantity' => 4],
                ],
            ],
        ]);
        $this->normalize($variant_level_2, 'standard')->shouldReturn([
            'PACK' => [
                'products' => [
                    ['identifier' => 'A', 'quantity' => 7],
                    ['identifier' => 'B', 'quantity' => 6],
                    ['identifier' => 'C', 'quantity' => 4],
                    ['identifier' => 'D', 'quantity' => 8],
                ],
            ],
        ]);
    }
}
