<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\Product;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Normalizer\Indexing\Product\ProductNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\Product\PropertiesNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class PropertiesNormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer)
    {
        $serializer->implement(NormalizerInterface::class);
        $this->setSerializer($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PropertiesNormalizer::class);
    }

    function it_support_products(ProductInterface $product)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(false);
        $this->supportsNormalization($product, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($product, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(true);
    }

    function it_normalizes_product_properties_with_empty_fields_and_values(
        $serializer,
        ProductInterface $product,
        ValueCollectionInterface $valueCollection,
        Collection $completenesses
    ) {
        $product->getId()->willReturn(67);
        $family = null;
        $product->getFamily()->willReturn($family);
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $product->getIdentifier()->willReturn('sku-001');
        $product->getFamily()->willReturn($family);
        $product->getCreated()->willReturn($now);
        $serializer
            ->normalize($family, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn(null);
        $serializer
            ->normalize($product->getWrappedObject()->getCreated(), ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn($now->format('c'));
        $product->getUpdated()->willReturn($now);
        $serializer
            ->normalize($product->getWrappedObject()->getUpdated(), ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn($now->format('c'));
        $product->isEnabled()->willReturn(false);
        $product->getValues()->willReturn($valueCollection);
        $product->getFamily()->willReturn(null);
        $product->getGroupCodes()->willReturn([]);
        $product->getCategoryCodes()->willReturn([]);
        $valueCollection->isEmpty()->willReturn(true);

        $product->isVariant()->willReturn(false);
        $product->getCompletenesses()->willReturn($completenesses);
        $completenesses->isEmpty()->willReturn(true);

        $this->normalize($product, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(
            [
                'id'            => '67',
                'identifier'    => 'sku-001',
                'created'       => $now->format('c'),
                'updated'       => $now->format('c'),
                'family'        => null,
                'enabled'       => false,
                'categories'    => [],
                'groups'        => [],
                'completeness'  => [],
                'values'        => [],
                'label'         => [],
                'ancestors'     => ['ids' => [], 'codes' => []],
            ]
        );
    }

    function it_normalizes_product_with_completenesses(
        $serializer,
        ProductInterface $product,
        ValueCollectionInterface $valueCollection,
        Collection $completenesses,
        AttributeInterface $sku
    ) {
        $product->getId()->willReturn(67);
        $family = null;
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $product->getIdentifier()->willReturn('sku-001');
        $product->getFamily()->willReturn($family);

        $product->getFamily()->willReturn($family);
        $serializer->normalize($family, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->willReturn($family);

        $product->getCreated()->willReturn($now);
        $serializer->normalize(
            $product->getWrappedObject()->getCreated(),
            ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX
        )->willReturn($now->format('c'));

        $product->getUpdated()->willReturn($now);
        $serializer->normalize(
            $product->getWrappedObject()->getUpdated(),
            ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX
        )->willReturn($now->format('c'));

        $product->isEnabled()->willReturn(false);
        $product->getValues()->willReturn($valueCollection);
        $product->getFamily()->willReturn(null);
        $product->getGroupCodes()->willReturn([]);
        $product->getCategoryCodes()->willReturn([]);
        $valueCollection->isEmpty()->willReturn(true);

        $product->isVariant()->willReturn(false);
        $product->getCompletenesses()->willReturn($completenesses);
        $completenesses->isEmpty()->willReturn(false);

        $serializer->normalize($completenesses, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX, [])->willReturn(['the completenesses']);

        $this->normalize($product, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(
            [
                'id'            => '67',
                'identifier'    => 'sku-001',
                'created'       => $now->format('c'),
                'updated'       => $now->format('c'),
                'family'        => null,
                'enabled'       => false,
                'categories'    => [],
                'groups'        => [],
                'completeness'  => ['the completenesses'],
                'values'        => [],
                'label'         => [],
                'ancestors'     => ['ids' => [], 'codes' => []],
            ]
        );
    }

    function it_normalizes_product_fields_and_values(
        $serializer,
        ProductInterface $product,
        ValueCollectionInterface $valueCollection,
        FamilyInterface $family,
        Collection $completenesses,
        AttributeInterface $sku
    ) {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $product->getId()->willReturn(67);
        $product->getIdentifier()->willReturn('sku-001');
        $product->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($sku);
        $sku->getCode()->willReturn('sku');

        $product->getCreated()->willReturn($now);
        $serializer->normalize(
            $product->getWrappedObject()->getCreated(),
            ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX
        )->willReturn($now->format('c'));

        $product->getUpdated()->willReturn($now);
        $serializer->normalize(
            $product->getWrappedObject()->getUpdated(),
            ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX
        )->willReturn($now->format('c'));

        $product->getFamily()->willReturn($family);
        $serializer
            ->normalize($family, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn([
                'code'   => 'family',
                'labels' => [
                    'fr_FR' => 'Une famille',
                    'en_US' => 'A family',
                ],
            ]);
        $product->isEnabled()->willReturn(true);
        $product->getGroupCodes()->willReturn(['first_group', 'second_group']);
        $product->getCategoryCodes()->willReturn(
            [
                'first_category',
                'second_category',
            ]
        );

        $completenesses->isEmpty()->willReturn(false);
        $product->getCompletenesses()->willReturn($completenesses);
        $serializer->normalize($completenesses, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX, [])->willReturn(
            [
                'ecommerce' => [
                    'en_US' => [
                        66
                    ]
                ]
            ]
        );

        $product->isVariant()->willReturn(false);
        $product->getValues()
            ->shouldBeCalledTimes(4)
            ->willReturn($valueCollection);
        $valueCollection->isEmpty()->willReturn(false);

        $serializer->normalize($valueCollection, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX, [])
            ->willReturn(
                [
                    'a_size-decimal' => [
                        '<all_channels>' => [
                            '<all_locales>' => '10.51',
                        ],
                    ],
                    'sku-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'sku label',
                        ],
                    ],
                ]
            );

        $this->normalize($product, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(
            [
                'id' => '67',
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
                'enabled' => true,
                'categories' => ['first_category', 'second_category'],
                'groups' => ['first_group', 'second_group'],
                'in_group' => [
                    'first_group' => true,
                    'second_group' => true,
                ],
                'completeness' => [
                    'ecommerce' => [
                        'en_US' => [
                            66,
                        ],
                    ],
                ],
                'values' => [
                    'a_size-decimal' => [
                        '<all_channels>' => [
                            '<all_locales>' => '10.51',
                        ],
                    ],
                    'sku-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'sku label',
                        ],
                    ],
                ],
                'label' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'sku label',
                    ],
                ],
                'ancestors' => ['ids' => [], 'codes' => []],
            ]
        );

        $family->getAttributeAsLabel()->willReturn(null);

        $serializer->normalize($valueCollection, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX, [])
            ->willReturn(
                [
                    'a_size-decimal' => [
                        '<all_channels>' => [
                            '<all_locales>' => '10.51',
                        ],
                    ],
                ]
            );

        $this->normalize($product, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(
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
                'enabled'       => true,
                'categories'    => ['first_category', 'second_category'],
                'groups'        => ['first_group', 'second_group'],
                'in_group' => [
                    'first_group'     => true,
                    'second_group'    => true,
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
                'label'         => [],
                'ancestors'     => ['ids' => [], 'codes' => []],
            ]
        );
    }

    function it_normalizes_variant_product_updated_at_with_youngest_date_in_ancestors(
        $serializer,
        ProductInterface $product,
        ProductModelInterface $subProductModel,
        ProductModelInterface $rootProductModel,
        FamilyInterface $family,
        ValueCollectionInterface $valueCollection,
        Collection $completenesses,
        AttributeInterface $sku
    ) {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $date1 = new \DateTime('now', new \DateTimeZone('UTC'));
        $date1->modify('-1 day');

        $date2 = new \DateTime('now', new \DateTimeZone('UTC'));
        $date2->modify('-2 day');

        $product->isVariant()->willReturn(true);
        $product->getUpdated()->willReturn($date2);
        $subProductModel->getUpdated()->willReturn($date1);
        $rootProductModel->getUpdated()->willReturn($now);

        $product->getParent()->willReturn($subProductModel);
        $subProductModel->getParent()->willReturn($rootProductModel);
        $rootProductModel->getParent()->willReturn(null);
        $subProductModel->getId()->willReturn(2);
        $rootProductModel->getId()->willReturn(1);
        $subProductModel->getCode()->willReturn('sub_pm_2');
        $rootProductModel->getCode()->willReturn('root_pm_1');

        $serializer->normalize(
            $now,
            ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX
        )->willReturn($now->format('c'));

        $product->getId()->willReturn(67);
        $product->getIdentifier()->willReturn('sku-001');

        $product->getCreated()->willReturn($now);
        $serializer->normalize(
            $product->getWrappedObject()->getCreated(),
            ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX
        )->willReturn($now->format('c'));

        $product->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($sku);
        $sku->getCode()->willReturn('sku');

        $serializer
            ->normalize($family, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn([
                'code'   => 'family',
                'labels' => [
                    'fr_FR' => 'Une famille',
                    'en_US' => 'A family',
                ],
            ]);
        $product->isEnabled()->willReturn(true);
        $product->getGroupCodes()->willReturn([]);
        $product->getCategoryCodes()->willReturn([]);

        $completenesses->isEmpty()->willReturn(true);
        $product->isVariant()->willReturn(true);
        $product->getCompletenesses()->willReturn($completenesses);
        $valueCollection->isEmpty()->willReturn(true);
        $product->getValues()->willReturn($valueCollection);

        $this->normalize($product, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(
            [
                'id'           => '67',
                'identifier'   => 'sku-001',
                'created'      => $now->format('c'),
                'updated'      => $now->format('c'),
                'family'       => [
                    'code'   => 'family',
                    'labels' => [
                        'fr_FR' => 'Une famille',
                        'en_US' => 'A family',
                    ],
                ],
                'enabled'      => true,
                'categories'   => [],
                'groups'       => [],
                'completeness' => [],
                'values'       => [],
                'label'        => [],
                'ancestors'    => ['ids' => ['product_model_2', 'product_model_1'], 'codes' => ['sub_pm_2', 'root_pm_1']],
            ]
        );
    }
}
