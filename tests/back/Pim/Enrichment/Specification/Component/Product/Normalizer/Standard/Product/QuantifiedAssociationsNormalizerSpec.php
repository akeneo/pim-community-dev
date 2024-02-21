<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\QuantifiedAssociationsNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\QuantifiedAssociation\QuantifiedAssociationsMerger;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class QuantifiedAssociationsNormalizerSpec extends ObjectBehavior
{
    function let(
        QuantifiedAssociationsMerger $quantifiedAssociationsMerger,
        ProductModelInterface $product_model,
        ProductModelInterface $variant_level_1,
        ProductInterface $variant_level_2
    ) {
        $this->beConstructedWith(
            $quantifiedAssociationsMerger
        );

        $product_model->getParent()->willReturn(null);
        $product_model->normalizeQuantifiedAssociations()->willReturn(
            [
                'PACK' => [
                    'products' => [
                        ['identifier' => 'A', 'quantity' => 1],
                    ],
                ],
            ]
        );
        $variant_level_1->getParent()->willReturn($product_model);
        $variant_level_1->normalizeQuantifiedAssociations()->willReturn(
            [
                'PACK' => [
                    'products' => [
                        ['identifier' => 'B', 'quantity' => 2],
                    ],
                ],
            ]
        );
        $variant_level_2->getParent()->willReturn($variant_level_1);
        $variant_level_2->normalizeQuantifiedAssociations()->willReturn(
            [
                'PACK' => [
                    'products' => [
                        ['identifier' => 'C', 'quantity' => 3],
                    ],
                ],
            ]
        );

        $quantifiedAssociationsMerger
            ->normalizeAndMergeQuantifiedAssociationsFrom(
                [
                    $product_model,
                    $variant_level_1,
                ]
            )
            ->willReturn(
                [
                    'PACK' => [
                        'products' => [
                            ['identifier' => 'A', 'quantity' => 1],
                            ['identifier' => 'B', 'quantity' => 2],
                        ],
                    ],
                ]
            );

        $quantifiedAssociationsMerger
            ->normalizeAndMergeQuantifiedAssociationsFrom(
                [
                    $product_model,
                    $variant_level_1,
                    $variant_level_2,
                ]
            )
            ->willReturn(
                [
                    'PACK' => [
                        'products' => [
                            ['identifier' => 'A', 'quantity' => 1],
                            ['identifier' => 'B', 'quantity' => 2],
                            ['identifier' => 'C', 'quantity' => 3],
                        ],
                    ],
                ]
            );
    }

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

    public function it_normalizes_a_product_without_its_parents_associations(
        ProductInterface $variant_level_2
    ) {
        $this->normalizeWithoutParentsAssociations($variant_level_2, 'standard', [])->shouldReturn(
            [
                'PACK' => [
                    'products' => [
                        ['identifier' => 'C', 'quantity' => 3],
                    ],
                ],
            ]
        );
    }

    public function it_normalizes_a_product_with_only_its_parents_associations(
        ProductInterface $variant_level_2
    ) {
        $this->normalizeOnlyParentsAssociations($variant_level_2, 'standard', [])->shouldReturn(
            [
                'PACK' => [
                    'products' => [
                        ['identifier' => 'A', 'quantity' => 1],
                        ['identifier' => 'B', 'quantity' => 2],
                    ],
                ],
            ]
        );
    }

    public function it_normalizes_a_product_with_its_parents_associations(
        ProductInterface $variant_level_2
    ) {
        $this->normalizeWithParentsAssociations($variant_level_2, 'standard', [])->shouldReturn(
            [
                'PACK' => [
                    'products' => [
                        ['identifier' => 'A', 'quantity' => 1],
                        ['identifier' => 'B', 'quantity' => 2],
                        ['identifier' => 'C', 'quantity' => 3],
                    ],
                ],
            ]
        );
    }

    public function it_normalizes_a_product_nonvariant(
        EntityWithQuantifiedAssociationsInterface $nonVariantProduct
    ) {
        $nonVariantProduct->normalizeQuantifiedAssociations()->willReturn(
            [
                'PACK' => [
                    'products' => [
                        ['identifier' => 'A', 'quantity' => 1],
                    ],
                ],
            ]
        );

        $this->normalize($nonVariantProduct, 'standard', [])->shouldReturn(
            [
                'PACK' => [
                    'products' => [
                        ['identifier' => 'A', 'quantity' => 1],
                    ],
                ],
            ]
        );
    }

    public function it_normalizes_a_product_variant_and_merge_the_parents_associations_by_default(
        ProductInterface $variant_level_2
    ) {
        $this->normalize($variant_level_2, 'standard', [])->shouldReturn(
            [
                'PACK' => [
                    'products' => [
                        ['identifier' => 'A', 'quantity' => 1],
                        ['identifier' => 'B', 'quantity' => 2],
                        ['identifier' => 'C', 'quantity' => 3],
                    ],
                ],
            ]
        );
    }
}
