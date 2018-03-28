<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\ProductModel;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Normalizer\Indexing\ProductModel\ProductModelNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\ProductModel\ProductModelPropertiesNormalizer;
use Pim\Component\Catalog\ProductAndProductModel\Query\CompleteFilterData;
use Pim\Component\Catalog\ProductAndProductModel\Query\CompleteFilterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ProductModelPropertiesNormalizerSpec extends ObjectBehavior
{
    function let(
        SerializerInterface $serializer,
        CompleteFilterInterface $completenessGridFilter,
        CompleteFilterData $completenessGridFilterData
    ) {
        $this->beConstructedWith($completenessGridFilter);

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

    function it_support_product_models(
        ProductModelInterface $productModel
    ) {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($productModel, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($productModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
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

        $productModel->getId()->willReturn('67');
        $productModel->getCode()->willReturn('sku-001');
        $productModel->getFamily()->willReturn(null);
        $productModel->getCreated()->willReturn($now);
        $serializer
            ->normalize($family, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
            ->willReturn(null);
        $serializer
            ->normalize($productModel->getWrappedObject()->getCreated(),
                ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
            ->willReturn($now->format('c'));
        $productModel->getUpdated()->willReturn($now);
        $serializer
            ->normalize($productModel->getWrappedObject()->getUpdated(),
                ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
            ->willReturn($now->format('c'));
        $productModel->getValues()->willReturn($productValueCollection);

        $familyVariant->getCode()->willReturn('family_variant_1');
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($sku);
        $sku->getCode()->willReturn('sku');
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $serializer
            ->normalize($family, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
            ->willReturn('family_A');

        $productValueCollection->isEmpty()->willReturn(true);

        $productModel->getCategoryCodes()->willReturn(['category_A', 'category_B']);

        $completenessGridFilter->findCompleteFilterData($productModel)->willReturn($completenessGridFilterData);

        $this->normalize($productModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)->shouldReturn(
            [
                'id'             => '67',
                'identifier'     => 'sku-001',
                'created'        => $now->format('c'),
                'updated'        => $now->format('c'),
                'family'         => 'family_A',
                'family_variant' => 'family_variant_1',
                'categories'     => ['category_A', 'category_B'],
                'parent'         => null,
                'values'         => [],
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
                'ancestors'     => ['ids' => [], 'codes' => []],
                'label'          => [],
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
        $productModel->getFamily()->willReturn(null);

        $productModel->getParent()->willReturn(null);

        $productModel->getCreated()->willReturn($now);
        $serializer->normalize(
            $productModel->getWrappedObject()->getCreated(),
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX
        )->willReturn($now->format('c'));

        $productModel->getUpdated()->willReturn($now);
        $serializer->normalize(
            $productModel->getWrappedObject()->getUpdated(),
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX
        )->willReturn($now->format('c'));

        $familyVariant->getCode()->willReturn('family_variant_B');
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($sku);
        $sku->getCode()->willReturn('sku');
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $serializer
            ->normalize($family, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
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

        $serializer->normalize($productValueCollection, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX,
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

        $this->normalize($productModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)->shouldReturn(
            [
                'id'             => '67',
                'identifier'     => 'sku-001',
                'created'        => $now->format('c'),
                'updated'        => $now->format('c'),
                'family'         => [
                    'code'   => 'family',
                    'labels' => [
                        'fr_FR' => 'Une famille',
                        'en_US' => 'A family',
                    ],
                ],
                'family_variant' => 'family_variant_B',
                'categories'     => ['category_A', 'category_B'],
                'parent'         => null,
                'values'         => [
                    'a_size-decimal' => [
                        '<all_channels>' => [
                            '<all_locales>' => '10.51',
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
                'ancestors'     => ['ids' => [], 'codes' => []],
                'label'          => [],
            ]
        );
    }

    function it_normalizes_a_product_model_fields_and_values_with_its_parents_values(
        $serializer,
        $completenessGridFilter,
        $completenessGridFilterData,
        ProductModelInterface $productModel,
        ProductModelInterface $parent,
        ValueCollectionInterface $valueCollection,
        FamilyInterface $family,
        AttributeInterface $sku,
        FamilyVariantInterface $familyVariant
    ) {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $productModel->getId()->willReturn(67);
        $productModel->getCode()->willReturn('sku-001');
        $productModel->getFamily()->willReturn(null);

        $productModel->getParent()->willReturn($parent);
        $parent->getId()->willReturn(1);
        $parent->getCode()->willReturn('parent_A');
        $parent->getParent()->willReturn(null);

        $productModel->getCreated()->willReturn($now);
        $serializer->normalize(
            $productModel->getWrappedObject()->getCreated(),
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX
        )->willReturn($now->format('c'));

        $productModel->getUpdated()->willReturn($now);
        $serializer->normalize(
            $productModel->getWrappedObject()->getUpdated(),
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX
        )->willReturn($now->format('c'));

        $familyVariant->getCode()->willReturn('family_variant_B');
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($sku);
        $sku->getCode()->willReturn('sku');
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $serializer
            ->normalize($family, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
            ->willReturn([
                'code'   => 'family',
                'labels' => [
                    'fr_FR' => 'Une famille',
                    'en_US' => 'A family',
                ],
            ]);

        $productModel->getValues()->shouldBeCalledTimes(2)->willReturn($valueCollection);
        $valueCollection->isEmpty()->willReturn(false);

        $productModel->getCategoryCodes()->willReturn(['category_A', 'category_B']);

        $serializer->normalize($valueCollection, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX, [])
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

        $completenessGridFilter->findCompleteFilterData($productModel)->willReturn($completenessGridFilterData);

        $this->normalize($productModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)->shouldReturn(
            [
                'id'             => '67',
                'identifier'     => 'sku-001',
                'created'        => $now->format('c'),
                'updated'        => $now->format('c'),
                'family'         => [
                    'code'   => 'family',
                    'labels' => [
                        'fr_FR' => 'Une famille',
                        'en_US' => 'A family',
                    ],
                ],
                'family_variant' => 'family_variant_B',
                'categories'     => ['category_A', 'category_B'],
                'parent'         => 'parent_A',
                'values'         => [
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
                'ancestors'     => ['ids' => ['product_model_1'], 'codes' => ['parent_A']],
                'label'          => [],
            ]
        );
    }
}
