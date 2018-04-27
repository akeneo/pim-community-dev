<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\FamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductNormalizer;
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
        $product->isVariant()->willReturn(false);
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
                'attributes_of_ancestors' => [],
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
        AttributeInterface $commonAttribute
    ) {
        $variantProduct->isVariant()->willReturn(true);
        $variantProduct->getVariationLevel()->willReturn(2);
        $variantProduct->getParent()->willReturn($subProductModel);

        $subProductModel->getParent()->willReturn($rootProductModel);

        $attributesProvider->getAttributes($rootProductModel)->willReturn([$commonAttribute, $propertyOne ,$axeOne]);
        $attributesProvider->getAttributes($subProductModel)->willReturn([$propertyTwo ,$axeTwo]);

        $propertyOne->getCode()->willReturn('property_one');
        $propertyTwo->getCode()->willReturn('property_two');
        $axeOne->getCode()->willReturn('axis_one');
        $axeTwo->getCode()->willReturn('axis_two');
        $commonAttribute->getCode()->willReturn('common');

        $propertiesNormalizer->normalize(
            $variantProduct,
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
            []
        )->willReturn(['properties' => 'properties are normalized here']);

        $this->normalize($variantProduct, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn([
                'properties'                => 'properties are normalized here',
                'document_type'              => ProductInterface::class,
                'attributes_of_ancestors' => ['axis_one',  'axis_two', 'common', 'property_one', 'property_two'],
            ]);
    }
}
