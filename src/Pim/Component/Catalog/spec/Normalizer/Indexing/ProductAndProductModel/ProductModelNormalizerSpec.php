<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\CommonAttributeCollection;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
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
        $this->supportsNormalization($productModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
        $this->supportsNormalization($productModel, 'other_format')
            ->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')
            ->shouldReturn(false);
    }

    function it_normalizes_a_root_product_model_in_indexing_format(
        $propertiesNormalizer,
        ProductModelInterface $productModel,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant,
        CommonAttributeCollection $commonAttributes,
        AttributeInterface $attrOne,
        AttributeInterface $attrTwo
    ) {
        $productModel->getFamily()->willReturn($family);
        $productModel->getVariationLevel()->willReturn(0);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getCommonAttributes()->willReturn($commonAttributes);
        $commonAttributes->toArray()->willReturn([$attrOne, $attrTwo]);

        $attrOne->getCode()->willReturn('attr1');
        $attrTwo->getCode()->willReturn('attr2');

        $productModel->getRawValues()
            ->willReturn([
                'property_1' => ['value_1'],
                'property_2' => ['value_2'],
            ]);
        $propertiesNormalizer->normalize(
            $productModel,
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
            []
        )->willReturn(['properties' => 'properties are normalized here']);

        $this->normalize($productModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn([
                'properties'                => 'properties are normalized here',
                'document_type'              => ProductModelInterface::class,
                'attributes_for_this_level' => ['attr1', 'attr2', 'property_1', 'property_2'],
            ]);
    }

    function it_normalizes_a_sub_product_model_in_indexing_format(
        $propertiesNormalizer,
        ProductModelInterface $productModel,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant,
        VariantAttributeSetInterface $variantAttributeSet,
        Collection $attributes,
        Collection $axes,
        AttributeInterface $propertyOne,
        AttributeInterface $axeOne
    ) {
        $productModel->getFamily()->willReturn($family);
        $productModel->getVariationLevel()->willReturn(1);

        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getVariantAttributeSet(1)->willReturn($variantAttributeSet);
        $variantAttributeSet->getAttributes()->willReturn($attributes);
        $variantAttributeSet->getAxes()->willReturn($axes);

        $attributes->toArray()->willReturn([$propertyOne]);
        $axes->toArray()->willReturn([$axeOne]);
        $propertyOne->getCode()->willReturn('attr1');
        $axeOne->getCode()->willReturn('attr2');

        $productModel->getRawValues()
            ->willReturn([
                'property_1' => ['value_1'],
                'property_2' => ['value_2'],
            ]);
        $propertiesNormalizer->normalize(
            $productModel,
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
            []
        )->willReturn(['properties' => 'properties are normalized here']);

        $this->normalize($productModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn([
                'properties'                => 'properties are normalized here',
                'document_type'              => ProductModelInterface::class,
                'attributes_for_this_level' => ['attr1', 'attr2', 'property_1', 'property_2'],
            ]);
    }
}
