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
use Prophecy\Argument;
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
        FamilyVariantInterface $familyVariant
    ) {
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $productModel->getVariationLevel()->willReturn(0);

        $propertiesNormalizer->normalize(
            $productModel,
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX,
            []
        )->willReturn(['properties' => 'properties are normalized here']);

        $this->normalize($productModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
            ->shouldReturn([
                'properties'                => 'properties are normalized here',
                'document_type'              => ProductModelInterface::class,
                'attributes_of_ancestors' => [],
            ]);
    }

    function it_normalizes_a_sub_product_model_in_indexing_format(
        $propertiesNormalizer,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant,
        VariantAttributeSetInterface $variantAttributeSet,
        CommonAttributeCollection $commonAttributes,
        Collection $axes,
        AttributeInterface $propertyOne,
        AttributeInterface $axeOne
    ) {
        $productModel->getVariationLevel()->willReturn(1);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getCommonAttributes()->willReturn($commonAttributes);
        $variantAttributeSet->getAxes()->willReturn($axes);

        $commonAttributes->map(Argument::cetera())->willReturn($commonAttributes);
        $commonAttributes->toArray()->willReturn(['property_1', 'property_2']);

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
                'attributes_of_ancestors' => ['property_1', 'property_2'],
            ]);
    }
}
