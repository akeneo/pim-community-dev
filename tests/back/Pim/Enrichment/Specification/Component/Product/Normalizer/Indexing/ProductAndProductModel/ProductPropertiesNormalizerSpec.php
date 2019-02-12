<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductPropertiesNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ProductPropertiesNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductPropertiesNormalizer::class);
    }

    function it_support_products_and_variant_products(
        ProductInterface $product,
        ProductInterface $variantProduct
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
        SerializerInterface $serializer,
        ProductInterface $product,
        ValueCollectionInterface $valueCollection,
        Collection $completenesses
    ) {
        $serializer->implement(NormalizerInterface::class);
        $this->setSerializer($serializer);

        $product->getId()->willReturn(67);
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $family = null;

        $product->isVariant()->willReturn(false);
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

        $serializer->normalize($completenesses, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
            [])->willReturn(['the completenesses']);

        $this->normalize($product, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'id'                      => 'product_67',
                'identifier'              => 'sku-001',
                'created'                 => $now->format('c'),
                'updated'                 => $now->format('c'),
                'family'                  => null,
                'enabled'                 => false,
                'categories'              => [],
                'categories_of_ancestors' => [],
                'groups'                  => [],
                'completeness'            => ['the completenesses'],
                'family_variant'          => null,
                'parent'                  => null,
                'values'                  => [],
                'ancestors'               => [
                    'ids'   => [],
                    'codes' => [],
                ],
                'label'                   => [],
            ]
        );
    }

    function it_normalizes_product_properties_with_fields_and_values(
        SerializerInterface $serializer,
        ProductInterface $product,
        ValueCollectionInterface $valueCollection,
        FamilyInterface $family,
        AttributeInterface $sku,
        Collection $completenesses
    ) {
        $serializer->implement(NormalizerInterface::class);
        $this->setSerializer($serializer);

        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $product->isVariant()->willReturn(false);
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
        $family->getAttributeAsLabel()->willReturn($sku);
        $sku->getCode()->willReturn('sku');
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
        $product->getGroupCodes()->willReturn(['first_group', 'second_group', 'another_group']);
        $product->getCategoryCodes()->willReturn(
            [
                'first_category',
                'second_category',
            ]
        );

        $completenesses->isEmpty()->willReturn(false);
        $product->getCompletenesses()->willReturn($completenesses);
        $serializer->normalize($completenesses, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
            [])->willReturn(
            [
                'ecommerce' => [
                    'en_US' => [
                        66,
                    ],
                ],
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
                'id'                      => 'product_67',
                'identifier'              => 'sku-001',
                'created'                 => $now->format('c'),
                'updated'                 => $now->format('c'),
                'family'                  => [
                    'code'   => 'family',
                    'labels' => [
                        'fr_FR' => 'Une famille',
                        'en_US' => 'A family',
                    ],
                ],
                'enabled'                 => true,
                'categories'              => ['first_category', 'second_category'],
                'categories_of_ancestors' => [],
                'groups'                  => ['first_group', 'second_group', 'another_group'],
                'in_group'                => [
                    'first_group'   => true,
                    'second_group'  => true,
                    'another_group' => true,
                ],
                'completeness'            => [
                    'ecommerce' => [
                        'en_US' => [
                            66,
                        ],
                    ],
                ],
                'family_variant'          => null,
                'parent'                  => null,
                'values'                  => [
                    'a_size-decimal' => [
                        '<all_channels>' => [
                            '<all_locales>' => '10.51',
                        ],
                    ],
                ],
                'ancestors'               => [
                    'ids'   => [],
                    'codes' => [],
                ],
                'label'                   => [],
            ]
        );
    }

    function it_normalizes_variant_product_properties_with_fields_and_values(
        SerializerInterface $serializer,
        ProductInterface $variantProduct,
        ValueCollectionInterface $valueCollection,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant,
        Collection $completenesses,
        ProductModelInterface $subProductModel,
        ProductModelInterface $rootProductModel
    ) {
        $serializer->implement(NormalizerInterface::class);
        $this->setSerializer($serializer);

        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $variantProduct->isVariant()->willReturn(true);
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

        $variantProduct->isEnabled()->willReturn(true);
        $variantProduct->getGroupCodes()->willReturn(['first_group', 'second_group', 'another_group']);
        $variantProduct->getCategoryCodes()->willReturn(
            [
                'first_category',
                'second_category',
            ]
        );

        $completenesses->isEmpty()->willReturn(false);
        $variantProduct->getCompletenesses()->willReturn($completenesses);
        $serializer->normalize($completenesses, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
            [])->willReturn(
            [
                'ecommerce' => [
                    'en_US' => [
                        66,
                    ],
                ],
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

        $variantProduct->getFamilyVariant()->willReturn($familyVariant);

        $variantProduct->getParent()->willReturn($subProductModel);
        $subProductModel->getId()->willReturn(4);
        $subProductModel->getCode()->willReturn('model-tshirt-xs');
        $subProductModel->getParent()->willReturn($rootProductModel);
        $subProductModel->getCategoryCodes()->willReturn(['second_category']);
        $rootProductModel->getId()->willReturn(1);
        $rootProductModel->getCode()->willReturn('model-tshirt');
        $rootProductModel->getParent()->willReturn(null);

        $this->normalize($variantProduct,
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'id'                      => 'product_67',
                'identifier'              => 'sku-001',
                'created'                 => $now->format('c'),
                'updated'                 => $now->format('c'),
                'family'                  => [
                    'code'   => 'family',
                    'labels' => [
                        'fr_FR' => 'Une famille',
                        'en_US' => 'A family',
                    ],
                ],
                'enabled'                 => true,
                'categories'              => ['first_category', 'second_category'],
                'categories_of_ancestors' => ['second_category'],
                'groups'                  => ['first_group', 'second_group', 'another_group'],
                'in_group'                => [
                    'first_group'   => true,
                    'second_group'  => true,
                    'another_group' => true,
                ],
                'completeness'            => [
                    'ecommerce' => [
                        'en_US' => [
                            66,
                        ],
                    ],
                ],
                'family_variant'          => null,
                'parent'                  => 'model-tshirt-xs',
                'values'                  => [
                    'a_size-decimal' => [
                        '<all_channels>' => [
                            '<all_locales>' => '10.51',
                        ],
                    ],
                ],
                'ancestors'               => [
                    'ids'   => ['product_model_4', 'product_model_1'],
                    'codes' => ['model-tshirt-xs', 'model-tshirt'],
                ],
                'label'                   => [],
            ]
        );
    }

    function it_normalizes_variant_product_properties_with_minimum_filled_fields_and_values(
        SerializerInterface $serializer,
        ProductInterface $variantProduct,
        ValueCollectionInterface $valueCollection,
        Collection $completenesses,
        FamilyInterface $family,
        AttributeInterface $sku,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $subProductModel,
        ProductModelInterface $rootProductModel
    ) {
        $serializer->implement(NormalizerInterface::class);
        $this->setSerializer($serializer);

        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $variantProduct->isVariant()->willReturn(true);
        $variantProduct->getId()->willReturn(67);
        $variantProduct->getIdentifier()->willReturn('sku-001');
        $variantProduct->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($sku);
        $sku->getCode()->willReturn('sku');

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

        $serializer->normalize($family,
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->willReturn(
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
        $serializer->normalize($completenesses, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX, [])
            ->willReturn(['the completenesses']);

        $variantProduct->getParent()->willReturn($subProductModel);
        $subProductModel->getId()->willReturn(4);
        $subProductModel->getCode()->willReturn('model-tshirt-xs');
        $subProductModel->getParent()->willReturn($rootProductModel);
        $subProductModel->getCategoryCodes()->willReturn([]);
        $rootProductModel->getId()->willReturn(1);
        $rootProductModel->getCode()->willReturn('model-tshirt');
        $rootProductModel->getParent()->willReturn(null);

        $this->normalize($variantProduct,
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
                'id'                      => 'product_67',
                'identifier'              => 'sku-001',
                'created'                 => $now->format('c'),
                'updated'                 => $now->format('c'),
                'family'                  => [
                    'code'   => 'family',
                    'labels' => [
                        'fr_FR' => 'Une famille',
                        'en_US' => 'A family',
                    ],
                ],
                'enabled'                 => false,
                'categories'              => [],
                'categories_of_ancestors' => [],
                'groups'                  => [],
                'completeness'            => ['the completenesses'],
                'family_variant'          => 'family_variant_A',
                'parent'                  => 'model-tshirt-xs',
                'values'                  => [],
                'ancestors'               => [
                    'ids'   => ['product_model_4', 'product_model_1'],
                    'codes' => ['model-tshirt-xs', 'model-tshirt'],
                ],
                'label'                   => [],
            ]
        );
    }

    function it_normalizes_variant_product_properties_with_fields_and_values_and_its_parents_values(
        SerializerInterface $serializer,
        ProductInterface $variantProduct,
        ValueCollectionInterface $valueCollection,
        Collection $completenesses,
        FamilyInterface $family,
        AttributeInterface $sku,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $rootProductModel
    ) {
        $serializer->implement(NormalizerInterface::class);
        $this->setSerializer($serializer);

        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $variantProduct->isVariant()->willReturn(true);
        $variantProduct->getId()->willReturn(67);
        $variantProduct->getIdentifier()->willReturn('sku-001');
        $variantProduct->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($sku);
        $sku->getCode()->willReturn('sku');

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
        $variantProduct->getGroupCodes()->willReturn(['first_group', 'second_group', 'another_group']);
        $variantProduct->getCategoryCodes()->willReturn(
            [
                'first_category',
                'second_category',
            ]
        );

        $completenesses->isEmpty()->willReturn(false);
        $variantProduct->getCompletenesses()->willReturn($completenesses);
        $serializer->normalize($completenesses, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
            [])->willReturn(
            [
                'ecommerce' => [
                    'en_US' => [
                        66,
                    ],
                ],
            ]
        );

        $variantProduct->getValues()
            ->shouldBeCalledTimes(2)
            ->willReturn($valueCollection);
        $valueCollection->isEmpty()->willReturn(false);
        $serializer->normalize($valueCollection, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
            [])
            ->willReturn(
                [
                    'a_size-decimal'         => [
                        '<all_channels>' => [
                            '<all_locales>' => '10.51',
                        ],
                    ],
                    'a_date-date'            => [
                        '<all_channels>' => [
                            '<all_locales>' => '2017-05-05',
                        ],
                    ],
                    'a_simple_select-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'OPTION_A',
                        ],
                    ],
                ]
            );

        $variantProduct->getParent()->willReturn($rootProductModel);
        $rootProductModel->getId()->willReturn(1);
        $rootProductModel->getCode()->willReturn('model-tshirt');
        $rootProductModel->getParent()->willReturn(null);
        $rootProductModel->getCategoryCodes()->willReturn(['first_category']);

        $this->normalize($variantProduct,
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'id'                      => 'product_67',
                'identifier'              => 'sku-001',
                'created'                 => $now->format('c'),
                'updated'                 => $now->format('c'),
                'family'                  => [
                    'code'   => 'family',
                    'labels' => [
                        'fr_FR' => 'Une famille',
                        'en_US' => 'A family',
                    ],
                ],
                'enabled'                 => true,
                'categories'              => ['first_category', 'second_category'],
                'categories_of_ancestors' => ['first_category'],
                'groups'                  => ['first_group', 'second_group', 'another_group'],
                'in_group'                => [
                    'first_group'   => true,
                    'second_group'  => true,
                    'another_group' => true,
                ],
                'completeness'            => [
                    'ecommerce' => [
                        'en_US' => [
                            66,
                        ],
                    ],
                ],
                'family_variant'          => 'family_variant_A',
                'parent'                  => 'model-tshirt',
                'values'                  => [
                    'a_size-decimal'         => [
                        '<all_channels>' => [
                            '<all_locales>' => '10.51',
                        ],
                    ],
                    'a_date-date'            => [
                        '<all_channels>' => [
                            '<all_locales>' => '2017-05-05',
                        ],
                    ],
                    'a_simple_select-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'OPTION_A',
                        ],
                    ],
                ],
                'ancestors'               => [
                    'ids'   => ['product_model_1'],
                    'codes' => ['model-tshirt'],
                ],
                'label'                   => [],
            ]
        );
    }

    function it_adds_extra_data_with_optionnal_normalizers(
        NormalizerInterface $normalizer1,
        NormalizerInterface $normalizer2,
        ProductInterface $product,
        ValueCollectionInterface $valueCollection,
        Collection $completenesses,
        SerializerInterface $serializer
    ) {
        $serializer->implement(NormalizerInterface::class);
        $this->beConstructedWith([$normalizer1, $normalizer2]);
        $this->setSerializer($serializer);

        $product->getId()->willReturn(67);
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $family = null;

        $product->isVariant()->willReturn(false);
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

        $serializer->normalize($completenesses, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
            [])->willReturn(['the completenesses']);

        $normalizer1
            ->normalize($product, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX, [])
            ->willReturn(['extraData1' => ['extraData1' => true]]);

        $normalizer2
            ->normalize($product, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX, [])
            ->willReturn(['extraData2' => ['extraData2' => false]]);

        $this->normalize($product, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'id'                      => 'product_67',
                'identifier'              => 'sku-001',
                'created'                 => $now->format('c'),
                'updated'                 => $now->format('c'),
                'family'                  => null,
                'enabled'                 => false,
                'categories'              => [],
                'categories_of_ancestors' => [],
                'groups'                  => [],
                'completeness'            => ['the completenesses'],
                'family_variant'          => null,
                'parent'                  => null,
                'values'                  => [],
                'ancestors'               => [
                    'ids'   => [],
                    'codes' => [],
                ],
                'label'                   => [],
                'extraData1'              => ['extraData1' => true],
                'extraData2'              => ['extraData2' => false],
            ]
        );
    }
}
