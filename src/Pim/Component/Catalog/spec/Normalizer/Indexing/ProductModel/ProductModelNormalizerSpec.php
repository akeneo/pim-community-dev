<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\ProductModel;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\CommonAttributeCollection;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
use Pim\Component\Catalog\Normalizer\Indexing\ProductModel\ProductModelNormalizer;
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

    function it_supports_indexing_normalization_only(ProductModelInterface $productModel)
    {
        $this->supportsNormalization($productModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
            ->shouldReturn(true);
        $this->supportsNormalization($productModel, 'other_format')
            ->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')
            ->shouldReturn(false);
    }

    function it_normalizes_a_root_product_model_in_indexing_format(
        $propertiesNormalizer,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant,
        CommonAttributeCollection $commonAttributes,
        AttributeInterface $propertyOne,
        AttributeInterface $propertyTwo
    ) {
        $productModel->getVariationLevel()->willReturn(0);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getCommonAttributes()->willReturn($commonAttributes);
        $commonAttributes->toArray()->willReturn([$propertyOne, $propertyTwo]);

        $propertyOne->getCode()->willReturn('property_1');
        $propertyTwo->getCode()->willReturn('property_2');

        $productModel->getRawValues()
            ->willReturn([
                'property_1' => ['value_1'],
                'property_2' => ['value_2'],
            ]);
        $propertiesNormalizer->normalize(
            $productModel,
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX,
            []
        )->willReturn(['properties' => 'properties are normalized here']);

        $this->normalize($productModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
            ->shouldReturn([
                'properties'                => 'properties are normalized here',
                'document_type'              => ProductModelInterface::class,
                'attributes_for_this_level' => ['property_1', 'property_2'],
            ]);
    }

    function it_normalizes_a_root_product_model_in_indexing_format_with_all_attributes_of_family(
        $propertiesNormalizer,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant,
        CommonAttributeCollection $commonAttributes,
        AttributeInterface $propertyOne,
        AttributeInterface $propertyTwo,
        AttributeInterface $propertyThree
    ) {
        $productModel->getVariationLevel()->willReturn(0);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getCommonAttributes()->willReturn($commonAttributes);
        $commonAttributes->toArray()->willReturn([$propertyOne, $propertyTwo, $propertyThree]);

        $propertyOne->getCode()->willReturn('property_1');
        $propertyTwo->getCode()->willReturn('property_2');
        $propertyThree->getCode()->willReturn('property_3');

        $productModel->getRawValues()
            ->willReturn([
                'property_1' => ['value_1'],
                'property_2' => ['value_2'],
            ]);
        $propertiesNormalizer->normalize(
            $productModel,
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX,
            []
        )->willReturn(['properties' => 'properties are normalized here']);

        $this->normalize($productModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
            ->shouldReturn([
                'properties'                => 'properties are normalized here',
                'document_type'              => ProductModelInterface::class,
                'attributes_for_this_level' => ['property_1', 'property_2', 'property_3'],
            ]);
    }

    function it_normalizes_a_sub_product_model_in_indexing_format_with_all_attributes_of_family(
        $propertiesNormalizer,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant,
        VariantAttributeSetInterface $variantAttributeSet,
        Collection $attributes,
        Collection $axes,
        AttributeInterface $propertyOne,
        AttributeInterface $propertyTwo,
        AttributeInterface $axeOne
    ) {
        $productModel->getVariationLevel()->willReturn(1);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getVariantAttributeSet(1)->willReturn($variantAttributeSet);
        $variantAttributeSet->getAttributes()->willReturn($attributes);
        $variantAttributeSet->getAxes()->willReturn($axes);

        $attributes->toArray()->willReturn([$propertyOne, $propertyTwo]);
        $axes->toArray()->willReturn([$axeOne]);
        $propertyOne->getCode()->willReturn('property_1');
        $propertyTwo->getCode()->willReturn('property_2');
        $axeOne->getCode()->willReturn('axe_1');

        $productModel->getRawValues()
            ->willReturn([
                'property_1' => ['value_1'],
                'property_2' => ['value_2'],
            ]);
        $propertiesNormalizer->normalize(
            $productModel,
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX,
            []
        )->willReturn(['properties' => 'properties are normalized here']);

        $this->normalize($productModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
            ->shouldReturn([
                'properties'                => 'properties are normalized here',
                'document_type'              => ProductModelInterface::class,
                'attributes_for_this_level' => ['axe_1', 'property_1', 'property_2'],
            ]);
    }

    function it_normalizes_a_sub_product_model_in_indexing_format(
        $propertiesNormalizer,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant,
        VariantAttributeSetInterface $variantAttributeSet,
        Collection $attributes,
        Collection $axes,
        AttributeInterface $propertyOne,
        AttributeInterface $axeOne
    ) {
        $productModel->getVariationLevel()->willReturn(1);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getVariantAttributeSet(1)->willReturn($variantAttributeSet);
        $variantAttributeSet->getAttributes()->willReturn($attributes);
        $variantAttributeSet->getAxes()->willReturn($axes);

        $attributes->toArray()->willReturn([$propertyOne]);
        $axes->toArray()->willReturn([$axeOne]);
        $propertyOne->getCode()->willReturn('property_1');
        $axeOne->getCode()->willReturn('property_2');

        $productModel->getRawValues()
            ->willReturn([
                'property_1' => ['value_1'],
                'property_2' => ['value_2'],
            ]);
        $propertiesNormalizer->normalize(
            $productModel,
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX,
            []
        )->willReturn(['properties' => 'properties are normalized here']);

        $this->normalize($productModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
            ->shouldReturn([
                'properties'                => 'properties are normalized here',
                'document_type'              => ProductModelInterface::class,
                'attributes_for_this_level' => ['property_1', 'property_2'],
            ]);
    }
}
