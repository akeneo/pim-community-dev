<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $propertiesNormalizer,
        EntityWithFamilyVariantAttributesProvider $attributesProvider
    ) {
        $this->beConstructedWith($propertiesNormalizer, $attributesProvider);
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
        ProductInterface $variantProduct
    ) {
        $this->supportsNormalization($product, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
        $this->supportsNormalization($product, 'other_format')
            ->shouldReturn(false);

        $this->supportsNormalization($variantProduct, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
        $this->supportsNormalization($variantProduct, 'other_format')
            ->shouldReturn(false);

        $this->supportsNormalization(new \stdClass(), ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
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
        $product->isVariant()->willReturn(false);
        $family->getAttributeCodes()->willReturn(['attr1', 'attr2']);
        $product->getRawValues()
            ->willReturn([
                'optional_attribute1' => ['value_1'],
                'optional_attribute2' => ['value_2'],
            ]);
        $propertiesNormalizer->normalize(
            $product,
            ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
            []
        )->willReturn(['properties' => 'properties are normalized here']);

        $this->normalize($product, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn([
                'properties'                => 'properties are normalized here',
                'document_type'              => ProductInterface::class,
                'attributes_of_ancestors' => [],
                'attributes_for_this_level' => ['attr1', 'attr2', 'optional_attribute1', 'optional_attribute2'],
            ]);
    }

    function it_normalizes_a_variant_product_in_product_and_product_model_format(
        $propertiesNormalizer,
        $attributesProvider,
        ProductInterface $variantProduct,
        ProductModelInterface $subProductModel,
        ProductModelInterface $rootProductModel,
        AttributeInterface $propertyOne,
        AttributeInterface $axeOne,
        AttributeInterface $propertyTwo,
        AttributeInterface $axeTwo,
        AttributeInterface $commonAttribute,
        AttributeInterface $productAttribute1,
        AttributeInterface $productAttribute2
    ) {
        $variantProduct->getRawValues()
            ->willReturn([
                'optional_attribute1' => ['value_1'],
                'optional_attribute2' => ['value_2'],
            ]);
        $variantProduct->isVariant()->willReturn(true);
        $variantProduct->getVariationLevel()->willReturn(2);
        $variantProduct->getParent()->willReturn($subProductModel);

        $subProductModel->getParent()->willReturn($rootProductModel);

        $attributesProvider->getAttributes($rootProductModel)->willReturn([$commonAttribute, $propertyOne ,$axeOne]);
        $attributesProvider->getAttributes($subProductModel)->willReturn([$propertyTwo ,$axeTwo]);
        $attributesProvider->getAttributes($variantProduct)->willReturn([$productAttribute1 ,$productAttribute2]);

        $productAttribute1->getCode()->willReturn('product_attribute1');
        $productAttribute2->getCode()->willReturn('product_attribute2');

        $propertyOne->getCode()->willReturn('property_one');
        $propertyTwo->getCode()->willReturn('property_two');
        $axeOne->getCode()->willReturn('axis_one');
        $axeTwo->getCode()->willReturn('axis_two');
        $commonAttribute->getCode()->willReturn('common');

        $propertiesNormalizer->normalize(
            $variantProduct,
            ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
            []
        )->willReturn(['properties' => 'properties are normalized here']);

        $this->normalize($variantProduct, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn([
                'properties'                => 'properties are normalized here',
                'document_type'              => ProductInterface::class,
                'attributes_of_ancestors' => ['axis_one',  'axis_two', 'common', 'property_one', 'property_two'],
                'attributes_for_this_level' => [
                    'optional_attribute1',
                    'optional_attribute2',
                    'product_attribute1',
                    'product_attribute2',
                ],
            ]);
    }
}
