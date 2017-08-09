<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductPropertiesNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ProductPropertiesNormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer)
    {
        $serializer->implement(NormalizerInterface::class);
        $this->setSerializer($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductPropertiesNormalizer::class);
    }

    function it_support_products_and_variant_products(
        ProductInterface $product,
        VariantProductInterface $variantProduct
    ) {
        $this->supportsNormalization($product, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($product, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);

        $this->supportsNormalization($variantProduct, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($variantProduct, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);

        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
    }

    function it_normalizes_product_properties_with_minimum_filled_fields_and_values(
        $serializer,
        ProductInterface $product,
        ValueCollectionInterface $valueCollection,
        Collection $completenesses
    ) {
        $product->getId()->willReturn(67);
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $family = null;

        $product->getIdentifier()->willReturn('sku-001');
        $product->getFamily()->willReturn($family);
        $serializer->normalize($family, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(null);

        $product->getCreated()->willReturn($now);
        $serializer->normalize(
            $product->getWrappedObject()->getCreated(),
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        )->willReturn($now->format('c'));

        $product->getUpdated()->willReturn($now);
        $serializer->normalize(
            $product->getWrappedObject()->getUpdated(),
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        )->willReturn($now->format('c'));

        $product->isEnabled()->willReturn(false);
        $product->getValues()->willReturn($valueCollection);
        $product->getFamily()->willReturn(null);
        $product->getGroupCodes()->willReturn([]);
        $product->getCategoryCodes()->willReturn([]);
        $valueCollection->isEmpty()->willReturn(true);

        $product->getCompletenesses()->willReturn($completenesses);
        $completenesses->isEmpty()->willReturn(false);

        $serializer->normalize($completenesses, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX, [])->willReturn(['the completenesses']);

        $this->normalize($product, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'id'            => '67',
                'identifier'    => 'sku-001',
                'created'       => $now->format('c'),
                'updated'       => $now->format('c'),
                'family' => null,
                'family_variant' => null,
                'enabled'       => false,
                'categories'    => [],
                'groups'        => [],
                'completeness'  => ['the completenesses'],
                'values'        => [],
            ]
        );
    }

    function it_normalizes_product_properties_with_fields_and_values(
        $serializer,
        ProductInterface $product,
        ValueCollectionInterface $valueCollection,
        FamilyInterface $family,
        Collection $completenesses
    ) {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $product->getId()->willReturn(67);
        $product->getIdentifier()->willReturn('sku-001');

        $product->getCreated()->willReturn($now);
        $serializer->normalize(
            $product->getWrappedObject()->getCreated(),
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        )->willReturn($now->format('c'));

        $product->getUpdated()->willReturn($now);
        $serializer->normalize(
            $product->getWrappedObject()->getUpdated(),
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        )->willReturn($now->format('c'));

        $product->getFamily()->willReturn($family);
        $serializer
            ->normalize($family, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn([
                'code'   => 'family',
                'labels' => [
                    'fr_FR' => 'Une famille',
                    'en_US' => 'A family',
                ],
            ]);

        $product->isEnabled()->willReturn(true);
        $product->getGroupCodes()->willReturn(['first_group', 'second_group', 'a_variant_group']);
        $product->getCategoryCodes()->willReturn(
            [
                'first_category',
                'second_category',
            ]
        );

        $completenesses->isEmpty()->willReturn(false);
        $product->getCompletenesses()->willReturn($completenesses);
        $serializer->normalize($completenesses, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX, [])->willReturn(
            [
                'ecommerce' => [
                    'en_US' => [
                        66
                    ]
                ]
            ]
        );

        $product->getValues()
            ->shouldBeCalledTimes(2)
            ->willReturn($valueCollection);
        $valueCollection->isEmpty()->willReturn(false);
        $serializer->normalize($valueCollection, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX, [])
            ->willReturn(
                [
                    'a_size-decimal' => [
                        '<all_channels>' => [
                            '<all_locales>' => '10.51',
                        ],
                    ],
                ]
            );

        $this->normalize($product, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
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
                'family_variant' => null,
                'enabled'       => true,
                'categories'    => ['first_category', 'second_category'],
                'groups'        => ['first_group', 'second_group', 'a_variant_group'],
                'in_group' => [
                    'first_group'     => true,
                    'second_group'    => true,
                    'a_variant_group' => true,
                ],
                'completeness'  => [
                    'ecommerce' => [
                        'en_US' => [
                            66,
                        ],
                    ],
                ],
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

    function it_normalizes_variant_product_properties_with_minimum_filled_fields_and_values(
        $serializer,
        VariantProductInterface $variantProduct,
        ValueCollectionInterface $valueCollection,
        Collection $completenesses,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant
    ) {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $variantProduct->getId()->willReturn(67);
        $variantProduct->getIdentifier()->willReturn('sku-001');

        $variantProduct->getCreated()->willReturn($now);
        $serializer->normalize(
            $variantProduct->getWrappedObject()->getCreated(),
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        )->willReturn($now->format('c'));

        $variantProduct->getUpdated()->willReturn($now);
        $serializer->normalize(
            $variantProduct->getWrappedObject()->getUpdated(),
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        )->willReturn($now->format('c'));

        $familyVariant->getCode()->willReturn('family_variant_A');
        $variantProduct->getFamily()->willReturn($family);
        $variantProduct->getFamilyVariant()->willReturn($familyVariant);
        $variantProduct->isEnabled()->willReturn(false);
        $variantProduct->getValues()->willReturn($valueCollection);
        $variantProduct->getGroupCodes()->willReturn([]);
        $variantProduct->getCategoryCodes()->willReturn([]);
        $valueCollection->isEmpty()->willReturn(true);

        $serializer->normalize($family, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->willReturn(
            [
                'code'   => 'family',
                'labels' => [
                    'fr_FR' => 'Une famille',
                    'en_US' => 'A family',
                ],
            ]
        );

        $variantProduct->getCompletenesses()->willReturn($completenesses);
        $completenesses->isEmpty()->willReturn(false);

        $serializer->normalize($completenesses, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX, [])->willReturn(['the completenesses']);

        $this->normalize($variantProduct, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
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
                'family_variant' => 'family_variant_A',
                'enabled'       => false,
                'categories'    => [],
                'groups'        => [],
                'completeness'  => ['the completenesses'],
                'values'        => [],
            ]
        );
    }

    function it_normalizes_variant_product_properties_with_fields_and_values(
        $serializer,
        VariantProductInterface $variantProduct,
        ValueCollectionInterface $valueCollection,
        Collection $completenesses,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant
    ) {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $variantProduct->getId()->willReturn(67);
        $variantProduct->getIdentifier()->willReturn('sku-001');

        $variantProduct->getCreated()->willReturn($now);
        $serializer->normalize(
            $variantProduct->getWrappedObject()->getCreated(),
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        )->willReturn($now->format('c'));

        $variantProduct->getUpdated()->willReturn($now);
        $serializer->normalize(
            $variantProduct->getWrappedObject()->getUpdated(),
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        )->willReturn($now->format('c'));

        $variantProduct->getFamily()->willReturn($family);
        $serializer
            ->normalize($family, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn([
                'code'   => 'family',
                'labels' => [
                    'fr_FR' => 'Une famille',
                    'en_US' => 'A family',
                ],
            ]);

        $familyVariant->getCode()->willReturn('family_variant_A');
        $variantProduct->getFamily()->willReturn($family);
        $variantProduct->getFamilyVariant()->willReturn($familyVariant);
        $variantProduct->isEnabled()->willReturn(true);
        $variantProduct->getGroupCodes()->willReturn(['first_group', 'second_group', 'a_variant_group']);
        $variantProduct->getCategoryCodes()->willReturn(
            [
                'first_category',
                'second_category',
            ]
        );

        $completenesses->isEmpty()->willReturn(false);
        $variantProduct->getCompletenesses()->willReturn($completenesses);
        $serializer->normalize($completenesses, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX, [])->willReturn(
            [
                'ecommerce' => [
                    'en_US' => [
                        66
                    ]
                ]
            ]
        );

        $variantProduct->getValues()
            ->shouldBeCalledTimes(2)
            ->willReturn($valueCollection);
        $valueCollection->isEmpty()->willReturn(false);
        $serializer->normalize($valueCollection, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX, [])
            ->willReturn(
                [
                    'a_size-decimal' => [
                        '<all_channels>' => [
                            '<all_locales>' => '10.51',
                        ],
                    ],
                ]
            );

        $this->normalize($variantProduct, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
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
                'family_variant' => 'family_variant_A',
                'enabled'       => true,
                'categories'    => ['first_category', 'second_category'],
                'groups'        => ['first_group', 'second_group', 'a_variant_group'],
                'in_group' => [
                    'first_group'     => true,
                    'second_group'    => true,
                    'a_variant_group' => true,
                ],
                'completeness'  => [
                    'ecommerce' => [
                        'en_US' => [
                            66,
                        ],
                    ],
                ],
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
