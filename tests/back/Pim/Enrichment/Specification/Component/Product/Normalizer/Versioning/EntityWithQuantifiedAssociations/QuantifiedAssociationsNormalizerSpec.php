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
            '1234' => [
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
            '1234-products' => 'productA',
            '1234-products-quantity' => '5',
            '1234-product_models' => 'productModelB',
            '1234-product_models-quantity' => '8',
        ]);
    }

    function it_normalize_quantified_association_on_product_with_uuid_or_identifier(ProductInterface $product)
    {
        $product->normalizeQuantifiedAssociations()->willReturn([
            'PRODUCT_SET' => [
                'products' => [],
                'product_models' => [],
            ],
            'PRODUCT_SET1' => [
                'products' => [
                    ['uuid' => '862fbbcf-ff23-4feb-962d-3216458c0ffb', 'quantity' => 5],
                    ['uuid' => '05fb0a57-9516-4a9f-a302-59d480d741aa', 'quantity' => 3],
                ],
                'product_models' => [
                    ['identifier' => 'productModelA', 'quantity' => 5],
                    ['uuid' => 'c93e0596-25b7-415e-896b-c863459eea50', 'quantity' => 8],
                ],
            ],
            '1234' => [
                'products' => [
                    ['uuid' => 'c93e0596-25b7-415e-896b-c863459eea50', 'quantity' => 5],
                ],
                'product_models' => [
                    ['uuid' => '1a1078c9-41a3-4bc8-a904-da8c7cb55d4d', 'quantity' => 8],
                ],
            ],
        ]);

        $this->normalize($product, 'flat')->shouldReturn([
            'PRODUCT_SET-products' => '',
            'PRODUCT_SET-products-quantity' => '',
            'PRODUCT_SET-product_models' => '',
            'PRODUCT_SET-product_models-quantity' => '',
            'PRODUCT_SET1-products' => '862fbbcf-ff23-4feb-962d-3216458c0ffb,05fb0a57-9516-4a9f-a302-59d480d741aa',
            'PRODUCT_SET1-products-quantity' => '5|3',
            'PRODUCT_SET1-product_models' => 'productModelA,c93e0596-25b7-415e-896b-c863459eea50',
            'PRODUCT_SET1-product_models-quantity' => '5|8',
            '1234-products' => 'c93e0596-25b7-415e-896b-c863459eea50',
            '1234-products-quantity' => '5',
            '1234-product_models' => '1a1078c9-41a3-4bc8-a904-da8c7cb55d4d',
            '1234-product_models-quantity' => '8',
        ]);
    }
}
