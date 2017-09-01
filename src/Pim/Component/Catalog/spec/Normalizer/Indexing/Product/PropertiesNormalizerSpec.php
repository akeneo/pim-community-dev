<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\Product;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
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
        $this->supportsNormalization(new \stdClass(), 'indexing')->shouldReturn(false);
        $this->supportsNormalization($product, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($product, 'indexing')->shouldReturn(true);
    }

    function it_normalizes_product_properties_with_empty_fields_and_values(
        $serializer,
        ProductInterface $product,
        ValueCollectionInterface $productValueCollection,
        Collection $completenesses
    ) {
        $product->getId()->willReturn(67);
        $family = null;
        $product->getFamily()->willReturn($family);
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $product->getIdentifier()->willReturn('sku-001');
        $product->getCreated()->willReturn($now);
        $serializer
            ->normalize($family, 'indexing')
            ->willReturn(null);
        $serializer
            ->normalize($product->getWrappedObject()->getCreated(), 'indexing')
            ->willReturn($now->format('c'));
        $product->getUpdated()->willReturn($now);
        $serializer
            ->normalize($product->getWrappedObject()->getUpdated(), 'indexing')
            ->willReturn($now->format('c'));
        $product->isEnabled()->willReturn(false);
        $product->getValues()->willReturn($productValueCollection);
        $product->getFamily()->willReturn(null);
        $product->getGroupCodes()->willReturn([]);
        $product->getVariantGroup()->willReturn(null);
        $product->getCategoryCodes()->willReturn([]);
        $productValueCollection->isEmpty()->willReturn(true);

        $product->getCompletenesses()->willReturn($completenesses);
        $completenesses->isEmpty()->willReturn(true);

        $this->normalize($product, 'indexing')->shouldReturn(
            [
                'id'            => '67',
                'identifier'    => 'sku-001',
                'created'       => $now->format('c'),
                'updated'       => $now->format('c'),
                'family'        => null,
                'enabled'       => false,
                'categories'    => [],
                'groups'        => [],
                'variant_group' => null,
                'completeness'  => [],
                'values'        => [],
            ]
        );
    }

    function it_normalizes_product_with_completenesses(
        $serializer,
        ProductInterface $product,
        ValueCollectionInterface $productValueCollection,
        Collection $completenesses
    ) {
        $product->getId()->willReturn(67);
        $family = null;
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $product->getIdentifier()->willReturn('sku-001');

        $product->getFamily()->willReturn($family);
        $serializer->normalize($family, 'indexing')->willReturn($family);

        $product->getCreated()->willReturn($now);
        $serializer->normalize(
            $product->getWrappedObject()->getCreated(),
            'indexing'
        )->willReturn($now->format('c'));

        $product->getUpdated()->willReturn($now);
        $serializer->normalize(
            $product->getWrappedObject()->getUpdated(),
            'indexing'
        )->willReturn($now->format('c'));

        $product->isEnabled()->willReturn(false);
        $product->getValues()->willReturn($productValueCollection);
        $product->getFamily()->willReturn(null);
        $product->getGroupCodes()->willReturn([]);
        $product->getVariantGroup()->willReturn(null);
        $product->getCategoryCodes()->willReturn([]);
        $productValueCollection->isEmpty()->willReturn(true);

        $product->getCompletenesses()->willReturn($completenesses);
        $completenesses->isEmpty()->willReturn(false);

        $serializer->normalize($completenesses, 'indexing', [])->willReturn(['the completenesses']);

        $this->normalize($product, 'indexing')->shouldReturn(
            [
                'id'            => '67',
                'identifier'    => 'sku-001',
                'created'       => $now->format('c'),
                'updated'       => $now->format('c'),
                'family'        => null,
                'enabled'       => false,
                'categories'    => [],
                'groups'        => [],
                'variant_group' => null,
                'completeness'  => ['the completenesses'],
                'values'        => [],
            ]
        );
    }

    function it_normalizes_product_fields_and_values(
        $serializer,
        ProductInterface $product,
        ValueCollectionInterface $productValueCollection,
        FamilyInterface $family,
        Collection $completenessCollection,
        Group $variantGroup
    ) {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $product->getId()->willReturn(67);
        $product->getIdentifier()->willReturn('sku-001');

        $product->getCreated()->willReturn($now);
        $serializer->normalize(
            $product->getWrappedObject()->getCreated(),
            'indexing'
        )->willReturn($now->format('c'));

        $product->getUpdated()->willReturn($now);
        $serializer->normalize(
            $product->getWrappedObject()->getUpdated(),
            'indexing'
        )->willReturn($now->format('c'));

        $product->getFamily()->willReturn($family);
        $serializer
            ->normalize($family, 'indexing')
            ->willReturn([
                'code'   => 'family',
                'labels' => [
                    'fr_FR' => 'Une famille',
                    'en_US' => 'A family',
                ],
            ]);
        $family->getCode()->willReturn('a_family');
        $product->isEnabled()->willReturn(true);
        $product->getGroupCodes()->willReturn(['first_group', 'second_group', 'a_variant_group']);
        $product->getVariantGroup()->willReturn($variantGroup);
        $variantGroup->getCode()->willReturn('a_variant_group');
        $product->getCategoryCodes()->willReturn(
            [
                'first_category',
                'second_category',
            ]
        );

        $completenessCollection->isEmpty()->willReturn(false);
        $product->getCompletenesses()->willReturn($completenessCollection);
        $serializer->normalize($completenessCollection, 'indexing', [])->willReturn(
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
            ->willReturn($productValueCollection);
        $productValueCollection->isEmpty()->willReturn(false);

        $serializer->normalize($productValueCollection, 'indexing', [])
            ->willReturn(
                [
                    'a_size-decimal' => [
                        '<all_channels>' => [
                            '<all_locales>' => '10.51',
                        ],
                    ],
                ]
            );

        $this->normalize($product, 'indexing')->shouldReturn(
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
                'groups'        => ['first_group', 'second_group', 'a_variant_group'],
                'variant_group' => 'a_variant_group',
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
