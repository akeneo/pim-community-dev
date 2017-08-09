<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use spec\Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModelFormat\ProductNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ProductModelPropertiesNormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer)
    {
        $serializer->implement(NormalizerInterface::class);
        $this->setSerializer($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(\Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductModelPropertiesNormalizer::class);
    }

    function it_support_product_models(
        ProductModelInterface $productModel
    ) {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), \Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($productModel, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($productModel, \Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
    }

    function it_normalizes_product_model_properties_with_empty_fields_and_values(
        $serializer,
        ProductModelInterface $productModel,
        ValueCollectionInterface $productValueCollection,
        Collection $completenesses
    ) {
//        $productModel->getId()->willReturn(67);
//        $family = null;
//        $productModel->getFamily()->willReturn($family);
//        $now = new \DateTime('now', new \DateTimeZone('UTC'));
//
//        $productModel->getIdentifier()->willReturn('sku-001');
//        $productModel->getCreated()->willReturn($now);
//        $serializer
//            ->normalize($family, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
//            ->willReturn(null);
//        $serializer
//            ->normalize($productModel->getWrappedObject()->getCreated(), ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
//            ->willReturn($now->format('c'));
//        $productModel->getUpdated()->willReturn($now);
//        $serializer
//            ->normalize($productModel->getWrappedObject()->getUpdated(), ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
//            ->willReturn($now->format('c'));
//        $productModel->isEnabled()->willReturn(false);
//        $productModel->getValues()->willReturn($productValueCollection);
//        $productModel->getFamily()->willReturn(null);
//        $productModel->getGroupCodes()->willReturn([]);
//        $productModel->getVariantGroup()->willReturn(null);
//        $productModel->getCategoryCodes()->willReturn([]);
//        $productValueCollection->isEmpty()->willReturn(true);
//
//        $productModel->getCompletenesses()->willReturn($completenesses);
//        $completenesses->isEmpty()->willReturn(true);
//
//        $this->normalize($productModel, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(
//            [
//                'id'            => '67',
//                'identifier'    => 'sku-001',
//                'created'       => $now->format('c'),
//                'updated'       => $now->format('c'),
//                'family'        => null,
//                'enabled'       => false,
//                'categories'    => [],
//                'groups'        => [],
//                'variant_group' => null,
//                'completeness'  => [],
//                'values'        => [],
//            ]
//        );
    }
}
