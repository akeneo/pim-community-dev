<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductModelPropertiesNormalizer;
use Pim\Component\Catalog\ProductAndProductModel\Query\CompleteFilterData;
use Pim\Component\Catalog\ProductAndProductModel\Query\CompleteFilterInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ProductModelPropertiesNormalizerSpec extends ObjectBehavior
{
    function let(
        SerializerInterface $serializer,
        CompleteFilterInterface $completenessGridFilter,
        CompleteFilterData $completenessGridFilterData,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->beConstructedWith($completenessGridFilter, $channelRepository, $localeRepository);

        $completenessGridFilterData->allIncomplete()->willReturn([
            'ecommerce' => [
                'fr_FR' => 0
            ]
        ]);

        $completenessGridFilterData->allComplete()->willReturn([
            'ecommerce' => [
                'fr_FR' => 0
            ]
        ]);

        $serializer->implement(NormalizerInterface::class);
        $this->setSerializer($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelPropertiesNormalizer::class);
    }

    function it_supports_product_models(
        ProductModelInterface $productModel
    ) {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($productModel, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($productModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
    }

    function it_normalizes_a_root_product_model_properties_with_minimum_filled_fields_and_values(
        $serializer,
        $completenessGridFilter,
        $completenessGridFilterData,
        ProductModelInterface $productModel,
        ValueCollectionInterface $productValueCollection,
        FamilyInterface $family,
        AttributeInterface $sku,
        FamilyVariantInterface $familyVariant
    ) {
        $productModel->getId()->willReturn(67);
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $productModel->getParent()->willReturn(null);

        $productModel->getCode()->willReturn('sku-001');
        $productModel->getFamily()->willReturn($family);
        $productModel->getValue('sku')->willReturn(null);
        $family->getAttributeAsLabel()->willReturn($sku);
        $sku->getCode()->willReturn('sku');
        $sku->isScopable()->willReturn(false);
        $sku->isLocalizable()->willReturn(false);
        $productModel->getCreated()->willReturn($now);
        $serializer
            ->normalize($family, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(null);
        $serializer
            ->normalize($productModel->getWrappedObject()->getCreated(),
                ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn($now->format('c'));
        $productModel->getUpdated()->willReturn($now);
        $serializer
            ->normalize($productModel->getWrappedObject()->getUpdated(),
                ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn($now->format('c'));
        $productModel->getValues()->willReturn($productValueCollection);

        $familyVariant->getCode()->willReturn('family_variant_1');
        $familyVariant->getFamily()->willReturn($family);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $serializer
            ->normalize($family, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn('family_A');

        $productModel->getCategoryCodes()->willReturn(['category_A', 'category_B']);

        $productValueCollection->isEmpty()->willReturn(true);

        $completenessGridFilter->findCompleteFilterData($productModel)->willReturn($completenessGridFilterData);

        $this->normalize($productModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'id' => 'product_model_67',
                'identifier' => 'sku-001',
                'created' => $now->format('c'),
                'updated' => $now->format('c'),
                'family' => 'family_A',
                'family_variant' => 'family_variant_1',
                'categories' => ['category_A', 'category_B'],
                'categories_of_ancestors' => [],
                'parent' => null,
                'values' => [],
                'all_complete' => [
                    'ecommerce' => [
                        'fr_FR' => 0,
                    ],
                ],
                'all_incomplete' => [
                    'ecommerce' => [
                        'fr_FR' => 0,
                    ],
                ],
                'ancestors' => [
                    'ids' => [],
                    'codes' => [],
                    'labels' => [],
                ],
                'label' => [],
            ]
        );
    }

    function it_normalizes_a_root_product_model_fields_and_values(
        $serializer,
        $completenessGridFilter,
        $completenessGridFilterData,
        ProductModelInterface $productModel,
        ValueCollectionInterface $productValueCollection,
        FamilyInterface $family,
        AttributeInterface $sku,
        FamilyVariantInterface $familyVariant
    ) {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $productModel->getId()->willReturn(67);
        $productModel->getCode()->willReturn('sku-001');
        $productModel->getFamily()->willReturn($family);
        $productModel->getValue('sku')->willReturn(null);
        $family->getAttributeAsLabel()->willReturn($sku);
        $sku->getCode()->willReturn('sku');
        $sku->isScopable()->willReturn(false);
        $sku->isLocalizable()->willReturn(false);

        $productModel->getParent()->willReturn(null);

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

        $productModel->getCategoryCodes()->willReturn(['category_A', 'category_B']);

        $serializer->normalize($productValueCollection, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
            [])
            ->willReturn(
                [
                    'a_size-decimal' => [
                        '<all_channels>' => [
                            '<all_locales>' => '10.51',
                        ],
                    ],
                ]
            );

        $completenessGridFilter->findCompleteFilterData($productModel)->willReturn($completenessGridFilterData);

        $this->normalize($productModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'id' => 'product_model_67',
                'identifier' => 'sku-001',
                'created' => $now->format('c'),
                'updated' => $now->format('c'),
                'family' => [
                    'code' => 'family',
                    'labels' => [
                        'fr_FR' => 'Une famille',
                        'en_US' => 'A family',
                    ],
                ],
                'family_variant' => 'family_variant_B',
                'categories' => ['category_A', 'category_B'],
                'categories_of_ancestors' => [],
                'parent' => null,
                'values' => [
                    'a_size-decimal' => [
                        '<all_channels>' => [
                            '<all_locales>' => '10.51',
                        ],
                    ],
                ],
                'all_complete' => [
                    'ecommerce' => [
                        'fr_FR' => 0,
                    ],
                ],
                'all_incomplete' => [
                    'ecommerce' => [
                        'fr_FR' => 0,
                    ],
                ],
                'ancestors' => [
                    'ids' => [],
                    'codes' => [],
                    'labels' => [],
                ],
                'label' => [],
            ]
        );
    }

    function it_normalizes_a_product_model_fields_and_values_with_its_parents_values_and_a_localizable_scopable_label(
        $serializer,
        $completenessGridFilter,
        $completenessGridFilterData,
        $localeRepository,
        $channelRepository,
        ProductModelInterface $productModel,
        ProductModelInterface $parent,
        ValueCollectionInterface $valueCollection,
        FamilyInterface $family,
        AttributeInterface $sku,
        FamilyVariantInterface $familyVariant,
        ValueInterface $frEcomSku,
        ValueInterface $frPrintSku,
        ValueInterface $enEcomSku,
        ValueInterface $enPrintSku
    ) {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $productModel->getId()->willReturn(67);
        $productModel->getCode()->willReturn('sku-001');
        $productModel->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($sku);
        $sku->getCode()->willReturn('sku');
        $sku->isScopable()->willReturn(true);
        $sku->isLocalizable()->willReturn(true);

        $productModel->getParent()->willReturn($parent);
        $parent->getCode()->willReturn('parent_A');
        $parent->getId()->willReturn(1);
        $parent->getParent()->willReturn(null);
        $parent->getCategoryCodes()->willReturn(['category_A']);

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
                'code' => 'family',
                'labels' => [
                    'fr_FR' => 'Une famille',
                    'en_US' => 'A family',
                ],
            ]);

        $productModel->getValues()->shouldBeCalledTimes(2)->willReturn($valueCollection);
        $valueCollection->isEmpty()->willReturn(false);

        $productModel->getCategoryCodes()->willReturn(['category_A', 'category_B']);

        $serializer->normalize($valueCollection, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX, [])
            ->willReturn(
                [
                    'a_size-decimal' => [
                        '<all_channels>' => [
                            '<all_locales>' => '10.51',
                        ],
                    ],
                    'a_date-date' => [
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

        $completenessGridFilter->findCompleteFilterData($productModel)->willReturn($completenessGridFilterData);

        $localeRepository->getActivatedLocaleCodes()->willReturn(['fr_FR', 'en_US']);
        $channelRepository->getChannelCodes()->willReturn(['ecommerce', 'print']);

        $productModel->getValue('sku', 'fr_FR', 'ecommerce')->willReturn($frEcomSku);
        $frEcomSku->getData()->willReturn('Un sku FR ecommerce');

        $productModel->getValue('sku', 'fr_FR', 'print')->willReturn($frPrintSku);
        $frPrintSku->getData()->willReturn('Un sku FR print');

        $productModel->getValue('sku', 'en_US', 'ecommerce')->willReturn($enEcomSku);
        $enEcomSku->getData()->willReturn('Sku EN ecommerce');

        $productModel->getValue('sku', 'en_US', 'print')->willReturn($enPrintSku);
        $enPrintSku->getData()->willReturn('Sku EN print');

        $this->normalize($productModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'id' => 'product_model_67',
                'identifier' => 'sku-001',
                'created' => $now->format('c'),
                'updated' => $now->format('c'),
                'family' => [
                    'code' => 'family',
                    'labels' => [
                        'fr_FR' => 'Une famille',
                        'en_US' => 'A family',
                    ],
                ],
                'family_variant' => 'family_variant_B',
                'categories' => ['category_A', 'category_B'],
                'categories_of_ancestors' => ['category_A'],
                'parent' => 'parent_A',
                'values' => [
                    'a_size-decimal' => [
                        '<all_channels>' => [
                            '<all_locales>' => '10.51',
                        ],
                    ],
                    'a_date-date' => [
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
                'all_complete' => [
                    'ecommerce' => [
                        'fr_FR' => 0
                    ]
                ],
                'all_incomplete' => [
                    'ecommerce' => [
                        'fr_FR' => 0
                    ]
                ],
                'ancestors' => [
                    'ids' => ['product_model_1'],
                    'codes' => ['parent_A'],
                    'labels' => [
                        'ecommerce' => [
                            'fr_FR' => 'Un sku FR ecommerce',
                            'en_US' => 'Sku EN ecommerce',
                        ],
                        'print' => [
                            'fr_FR' => 'Un sku FR print',
                            'en_US' => 'Sku EN print',
                        ]
                    ],
                ],
                'label' => []
            ]
        );
    }

    function it_normalizes_a_product_model_fields_and_values_with_its_parents_values_and_a_localizable_label(
        $serializer,
        $completenessGridFilter,
        $completenessGridFilterData,
        $localeRepository,
        ProductModelInterface $productModel,
        ProductModelInterface $parent,
        ValueCollectionInterface $valueCollection,
        FamilyInterface $family,
        AttributeInterface $sku,
        FamilyVariantInterface $familyVariant,
        ValueInterface $frSku,
        ValueInterface $enSku
    ) {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $productModel->getId()->willReturn(67);
        $productModel->getCode()->willReturn('sku-001');
        $productModel->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($sku);
        $sku->getCode()->willReturn('sku');
        $sku->isScopable()->willReturn(false);
        $sku->isLocalizable()->willReturn(true);

        $productModel->getParent()->willReturn($parent);
        $parent->getCode()->willReturn('parent_A');
        $parent->getId()->willReturn(1);
        $parent->getParent()->willReturn(null);
        $parent->getCategoryCodes()->willReturn(['category_A']);

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
                'code' => 'family',
                'labels' => [
                    'fr_FR' => 'Une famille',
                    'en_US' => 'A family',
                ],
            ]);

        $productModel->getValues()->willReturn($valueCollection);
        $valueCollection->isEmpty()->willReturn(true);

        $productModel->getCategoryCodes()->willReturn(['category_A', 'category_B']);

        $completenessGridFilter->findCompleteFilterData($productModel)->willReturn($completenessGridFilterData);

        $localeRepository->getActivatedLocaleCodes()->willReturn(['fr_FR', 'en_US']);

        $productModel->getValue('sku', 'fr_FR')->willReturn($frSku);
        $frSku->getData()->willReturn('fr_FR SKU');

        $productModel->getValue('sku', 'en_US')->willReturn($enSku);
        $enSku->getData()->willReturn('en_US SKU');

        $this->normalize($productModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'id' => 'product_model_67',
                'identifier' => 'sku-001',
                'created' => $now->format('c'),
                'updated' => $now->format('c'),
                'family' => [
                    'code' => 'family',
                    'labels' => [
                        'fr_FR' => 'Une famille',
                        'en_US' => 'A family',
                    ],
                ],
                'family_variant' => 'family_variant_B',
                'categories' => ['category_A', 'category_B'],
                'categories_of_ancestors' => ['category_A'],
                'parent' => 'parent_A',
                'values' => [],
                'all_complete' => [
                    'ecommerce' => [
                        'fr_FR' => 0
                    ]
                ],
                'all_incomplete' => [
                    'ecommerce' => [
                        'fr_FR' => 0
                    ]
                ],
                'ancestors' => [
                    'ids' => ['product_model_1'],
                    'codes' => ['parent_A'],
                    'labels' => [
                        '<all_channels>' => [
                            'fr_FR' => 'fr_FR SKU',
                            'en_US' => 'en_US SKU',
                        ],
                    ],
                ],
                'label' => []
            ]
        );
    }

    function it_normalizes_a_product_model_fields_and_values_with_its_parents_values_and_a_scopable_label(
        $serializer,
        $completenessGridFilter,
        $completenessGridFilterData,
        $channelRepository,
        ProductModelInterface $productModel,
        ProductModelInterface $parent,
        ValueCollectionInterface $valueCollection,
        FamilyInterface $family,
        AttributeInterface $sku,
        FamilyVariantInterface $familyVariant,
        ValueInterface $ecommerceSku,
        ValueInterface $printSku
    ) {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $productModel->getId()->willReturn(67);
        $productModel->getCode()->willReturn('sku-001');
        $productModel->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($sku);
        $sku->getCode()->willReturn('sku');
        $sku->isScopable()->willReturn(true);
        $sku->isLocalizable()->willReturn(false);

        $productModel->getParent()->willReturn($parent);
        $parent->getCode()->willReturn('parent_A');
        $parent->getId()->willReturn(1);
        $parent->getParent()->willReturn(null);
        $parent->getCategoryCodes()->willReturn(['category_A']);

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
                'code' => 'family',
                'labels' => [
                    'fr_FR' => 'Une famille',
                    'en_US' => 'A family',
                ],
            ]);

        $productModel->getValues()->willReturn($valueCollection);
        $valueCollection->isEmpty()->willReturn(true);

        $productModel->getCategoryCodes()->willReturn(['category_A', 'category_B']);

        $completenessGridFilter->findCompleteFilterData($productModel)->willReturn($completenessGridFilterData);

        $channelRepository->getChannelCodes()->willReturn(['ecommerce', 'print']);

        $productModel->getValue('sku', null, 'ecommerce')->willReturn($ecommerceSku);
        $ecommerceSku->getData()->willReturn('ecommerce SKU');

        $productModel->getValue('sku', null, 'print')->willReturn($printSku);
        $printSku->getData()->willReturn('print SKU');

        $this->normalize($productModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'id' => 'product_model_67',
                'identifier' => 'sku-001',
                'created' => $now->format('c'),
                'updated' => $now->format('c'),
                'family' => [
                    'code' => 'family',
                    'labels' => [
                        'fr_FR' => 'Une famille',
                        'en_US' => 'A family',
                    ],
                ],
                'family_variant' => 'family_variant_B',
                'categories' => ['category_A', 'category_B'],
                'categories_of_ancestors' => ['category_A'],
                'parent' => 'parent_A',
                'values' => [],
                'all_complete' => [
                    'ecommerce' => [
                        'fr_FR' => 0
                    ]
                ],
                'all_incomplete' => [
                    'ecommerce' => [
                        'fr_FR' => 0
                    ]
                ],
                'ancestors' => [
                    'ids' => ['product_model_1'],
                    'codes' => ['parent_A'],
                    'labels' => [
                        'ecommerce' => [
                            '<all_locales>' => 'ecommerce SKU',
                        ],
                        'print' => [
                            '<all_locales>' => 'print SKU',
                        ]
                    ],
                ],
                'label' => []
            ]
        );
    }
}
