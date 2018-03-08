<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
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
        FamilyInterface $family
    ) {
        $productModel->getFamily()->willReturn($family);
        $family->getAttributeCodes()->willReturn(['attr1', 'attr2']);
        $productModel->getVariationLevel()->willReturn(0);
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
        FamilyInterface $family
    ) {
        $productModel->getFamily()->willReturn($family);
        $family->getAttributeCodes()->willReturn(['attr1', 'attr2']);
        $productModel->getVariationLevel()->willReturn(1);
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
