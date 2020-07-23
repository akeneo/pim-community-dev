<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\EntityWithQuantifiedAssociations;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociationCollection;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class QuantifiedAssociationsNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldBeAnInstanceOf(NormalizerInterface::class);
    }

    function it_only_supports_flat_normalization_of_entity_with_quantified_associations(
        ProductInterface $product,
        ProductModelInterface $productModel,
        GroupInterface $group,
        QuantifiedAssociationCollection $quantifiedAssociations
    ) {
        $this->supportsNormalization($product, 'flat')->shouldBe(true);
        $this->supportsNormalization($productModel, 'flat')->shouldBe(true);

        $this->supportsNormalization($product, 'standard')->shouldBe(false);
        $this->supportsNormalization($productModel, 'standard')->shouldBe(false);
        $this->supportsNormalization($group, 'flat')->shouldBe(false);
        $this->supportsNormalization($quantifiedAssociations, 'flat')->shouldBe(false);
    }

    function it_normalize_quantified_association_on_product(ProductInterface $product)
    {
        $product->normalizeQuantifiedAssociations()->willReturn([
            'PRODUCT_SET' => [
                'products' => [],
                'product_models' => [],
            ],
            'PRODUCT_SET1' => [
                'products' => [
                    ['identifier' => 'productA', 'quantity' => 5],
                    ['identifier' => 'productB', 'quantity' => 3],
                ],
                'product_models' => [
                    ['identifier' => 'productModelA', 'quantity' => 5],
                    ['identifier' => 'productModelB', 'quantity' => 8],
                ],
            ],
            'PRODUCT_SET2' => [
                'products' => [
                    ['identifier' => 'productA', 'quantity' => 5],
                ],
                'product_models' => [
                    ['identifier' => 'productModelB', 'quantity' => 8],
                ],
            ],
        ]);

        $this->normalize($product, 'flat')->shouldReturn([
            'PRODUCT_SET-products' => '',
            'PRODUCT_SET-products-quantity' => '',
            'PRODUCT_SET-product_models' => '',
            'PRODUCT_SET-product_models-quantity' => '',
            'PRODUCT_SET1-products' => 'productA,productB',
            'PRODUCT_SET1-products-quantity' => '5|3',
            'PRODUCT_SET1-product_models' => 'productModelA,productModelB',
            'PRODUCT_SET1-product_models-quantity' => '5|8',
            'PRODUCT_SET2-products' => 'productA',
            'PRODUCT_SET2-products-quantity' => '5',
            'PRODUCT_SET2-product_models' => 'productModelB',
            'PRODUCT_SET2-product_models-quantity' => '8',
        ]);
    }
}
