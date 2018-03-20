<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductNormalizer;
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

    function it_supports_products_and_variant_products(
        ProductInterface $product,
        VariantProductInterface $variantProduct
    ) {
        $this->supportsNormalization($product, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
        $this->supportsNormalization($product, 'other_format')
            ->shouldReturn(false);

        $this->supportsNormalization($variantProduct, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
        $this->supportsNormalization($variantProduct, 'other_format')
            ->shouldReturn(false);

        $this->supportsNormalization(new \stdClass(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')
            ->shouldReturn(false);
    }

    function it_normalizes_a_product_in_product_and_product_model_format(
        $propertiesNormalizer,
        ProductInterface $product,
        FamilyInterface $family
    ) {
        $product->getFamily()->willReturn($family);
        $family->getAttributeCodes()->willReturn(['attr1', 'attr2']);
        $product->getRawValues()
            ->willReturn([
                'property_1' => ['value_1'],
                'property_2' => ['value_2'],
            ]);
        $propertiesNormalizer->normalize(
            $product,
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
            []
        )->willReturn(['properties' => 'properties are normalized here']);

        $this->normalize($product, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn([
                'properties'                => 'properties are normalized here',
                'document_type'              => ProductInterface::class,
                'attributes_for_this_level' => ['attr1', 'attr2', 'property_1', 'property_2'],
            ]);
    }

    function it_normalizes_a_variant_product_in_product_and_product_model_format(
        $propertiesNormalizer,
        VariantProductInterface $variantProduct,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant,
        VariantAttributeSetInterface $variantAttributeSet,
        Collection $attributes,
        Collection $axes,
        AttributeInterface $propertyOne,
        AttributeInterface $axeOne
    ) {
        $variantProduct->getFamily()->willReturn($family);
        $variantProduct->getVariationLevel()->willReturn(0);
        $variantProduct->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getVariantAttributeSet(0)->willReturn($variantAttributeSet);
        $variantAttributeSet->getAttributes()->willReturn($attributes);
        $variantAttributeSet->getAxes()->willReturn($axes);

        $attributes->toArray()->willReturn([$propertyOne]);
        $axes->toArray()->willReturn([$axeOne]);
        $propertyOne->getCode()->willReturn('attr_1');
        $axeOne->getCode()->willReturn('attr_2');

        $variantProduct->getRawValues()
            ->willReturn([
                'property_1' => ['value_1'],
                'property_2' => ['value_2'],
            ]);
        $propertiesNormalizer->normalize(
            $variantProduct,
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
            []
        )->willReturn(['properties' => 'properties are normalized here']);

        $this->normalize($variantProduct, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn([
                'properties'                => 'properties are normalized here',
                'document_type'              => ProductInterface::class,
                'attributes_for_this_level' => ['attr_1', 'attr_2', 'property_1', 'property_2'],
            ]);
    }
}
