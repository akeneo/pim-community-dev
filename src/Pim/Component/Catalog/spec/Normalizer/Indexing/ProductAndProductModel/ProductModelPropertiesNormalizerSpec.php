<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductModelPropertiesNormalizer;
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
        $this->shouldHaveType(ProductModelPropertiesNormalizer::class);
    }

    function it_support_product_models(
        ProductModelInterface $productModel
    ) {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($productModel, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($productModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
    }

    function it_normalizes_product_model_properties_with_minimum_filled_fields_and_values(
        $serializer,
        ProductModelInterface $productModel,
        ValueCollectionInterface $productValueCollection,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant
    ) {
        $productModel->getId()->willReturn(67);
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $productModel->getCode()->willReturn('sku-001');
        $productModel->getCreated()->willReturn($now);
        $serializer
            ->normalize($family, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(null);
        $serializer
            ->normalize($productModel->getWrappedObject()->getCreated(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn($now->format('c'));
        $productModel->getUpdated()->willReturn($now);
        $serializer
            ->normalize($productModel->getWrappedObject()->getUpdated(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn($now->format('c'));
        $productModel->getValues()->willReturn($productValueCollection);

        $familyVariant->getCode()->willReturn('family_variant_1');
        $familyVariant->getFamily()->willReturn($family);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $serializer
            ->normalize($family, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn('family_A');

        $productValueCollection->isEmpty()->willReturn(true);

        $this->normalize($productModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'id'            => '67',
                'identifier'    => 'sku-001',
                'created'       => $now->format('c'),
                'updated'       => $now->format('c'),
                'family'        => 'family_A',
                'family_variant' => 'family_variant_1',
                'values'        => [],
            ]
        );
    }

    function it_normalizes_product_model_fields_and_values(
        $serializer,
        ProductModelInterface $productModel,
        ValueCollectionInterface $productValueCollection,
        Collection $completenessCollection,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant
    ) {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $productModel->getId()->willReturn(67);
        $productModel->getCode()->willReturn('sku-001');

        $productModel->getCreated()->willReturn($now);
        $serializer->normalize(
            $productModel->getWrappedObject()->getCreated(),
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        )->willReturn($now->format('c'));

        $productModel->getUpdated()->willReturn($now);
        $serializer->normalize(
            $productModel->getWrappedObject()->getUpdated(),
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        )->willReturn($now->format('c'));

        $familyVariant->getCode()->willReturn('family_variant_B');
        $familyVariant->getFamily()->willReturn($family);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $serializer
            ->normalize($family, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn([
                'code'   => 'family',
                'labels' => [
                    'fr_FR' => 'Une famille',
                    'en_US' => 'A family',
                ],
            ]);

        $productModel->getValues()->shouldBeCalledTimes(2)->willReturn($productValueCollection);
        $productValueCollection->isEmpty()->willReturn(false);

        $serializer->normalize($productValueCollection, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX, [])
            ->willReturn(
                [
                    'a_size-decimal' => [
                        '<all_channels>' => [
                            '<all_locales>' => '10.51',
                        ],
                    ],
                ]
            );

        $this->normalize($productModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'id'            => '67',
                'identifier'    => 'sku-001',
                'created'       => $now->format('c'),
                'updated'       => $now->format('c'),
                'family' => [
                    'code'   => 'family',
                    'labels' => [
                        'fr_FR' => 'Une famille',
                        'en_US' => 'A family',
                    ],
                ],
                'family_variant' => 'family_variant_B',
                'values'        => [
                    'a_size-decimal' => [
                        '<all_channels>' => [
                            '<all_locales>' => '10.51',
                        ],
                    ],
                ],
            ]
        );
    }
}
