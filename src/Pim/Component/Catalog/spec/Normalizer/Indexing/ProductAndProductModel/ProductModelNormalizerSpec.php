<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\CommonAttributeCollection;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
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
                'properties'              => 'properties are normalized here',
                'document_type'           => ProductModelInterface::class,
                'attributes_of_ancestors' => [],
            ]);
    }

    function it_normalizes_a_sub_product_model_in_indexing_format(
        $propertiesNormalizer,
        ProductModelInterface $productModel,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant,
        CommonAttributeCollection $commonAttributeCollection,
        ArrayCollection $attributeCodes
    ) {
        $productModel->getFamily()->willReturn($family);
        $productModel->getVariationLevel()->willReturn(1);

        $productModel->getFamilyVariant()->willReturn($familyVariant);

        $familyVariant->getCommonAttributes()->willReturn($commonAttributeCollection);
        $commonAttributeCollection->map(Argument::cetera())->willReturn($attributeCodes);
        $attributeCodes->toArray()->willReturn(['attr1', 'attr2', 'property_1', 'property_2']);

        $propertiesNormalizer->normalize(
            $productModel,
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
            []
        )->willReturn(['properties' => 'properties are normalized here']);

        $this->normalize($productModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn([
                'properties'              => 'properties are normalized here',
                'document_type'           => ProductModelInterface::class,
                'attributes_of_ancestors' => ['attr1', 'attr2', 'property_1', 'property_2'],
            ]);
    }

    function it_normalizes_a_product_model_not_having_a_family_variant(
        $propertiesNormalizer,
        ProductModelInterface $productModel,
        FamilyInterface $family
    ) {
        $productModel->getFamily()->willReturn($family);
        $productModel->getVariationLevel()->willReturn(1);
        $productModel->getFamilyVariant()->willReturn(null);

        $propertiesNormalizer->normalize(
            $productModel,
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
            []
        )->willReturn(['properties' => 'properties are normalized here']);

        $this->normalize($productModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(
                [
                    'properties'              => 'properties are normalized here',
                    'document_type'           => ProductModelInterface::class,
                    'attributes_of_ancestors' => [],
                ]
            );
    }
}
