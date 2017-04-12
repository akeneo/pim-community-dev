<?php

namespace spec\Pim\Bundle\DataGridBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\Completeness;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyTranslationInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\GroupTranslationInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductValueCollectionInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ProductNormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer, CollectionFilterInterface $filter)
    {
        $this->beConstructedWith($filter);

        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->setSerializer($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGridBundle\Normalizer\ProductNormalizer');
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\SerializerAwareInterface');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_datagrid_format_and_product_value(ProductInterface $product)
    {
        $this->supportsNormalization($product, 'datagrid')->shouldReturn(true);
        $this->supportsNormalization($product, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'datagrid')->shouldReturn(false);
    }

    function it_normalizes_a_product_with_label(
        $serializer,
        $filter,
        ProductInterface $product,
        GroupInterface $promotion,
        GroupTranslationInterface $promotionEN,
        FamilyInterface $family,
        FamilyTranslationInterface $familyEN,
        ProductValueCollectionInterface $productValues,
        Completeness $completeness,
        LocaleInterface $localeEN,
        ChannelInterface $channelEcommerce
    ) {
        $context = [
            'filter_types' => ['pim.transform.product_value.structured'],
            'locales' => ['en_US'], 'channels' => ['ecommerce']
        ];

        $filter->filterCollection($productValues, 'pim.transform.product_value.structured', $context)
            ->willReturn($productValues);

        $product->getGroups()->willReturn([$promotion]);
        $promotion->getCode()->willReturn('promotion');
        $promotion->getTranslation('en_US')->willReturn($promotionEN);
        $promotionEN->getLabel()->willReturn('Promotion');

        $product->getFamily()->willReturn($family);
        $family->getCode()->willReturn('tshirt');
        $family->getTranslation('en_US')->willReturn($familyEN);
        $familyEN->getLabel()->willReturn('Tshirt');

        $product->getIdentifier()->willReturn('purple_tshirt');
        $product->isEnabled()->willReturn(true);
        $product->getValues()->willReturn($productValues);
        $serializer->normalize($productValues, 'datagrid', $context)->willReturn([
            'text' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'my text',
                ]
            ]
        ]);

        $created = new \DateTime('2017-01-01T01:03:34+01:00');
        $product->getCreated()->willReturn($created);
        $serializer->normalize($created, 'datagrid', $context)->willReturn('2017-01-01T01:03:34+01:00');

        $updated = new \DateTime('2017-01-01T01:04:34+01:00');
        $product->getUpdated()->willReturn($updated);
        $serializer->normalize($updated, 'datagrid', $context)->willReturn('2017-01-01T01:04:34+01:00');
        $product->getLabel('en_US')->willReturn('Purple tshirt');
        $product->getCompletenesses()->willReturn([$completeness]);
        $completeness->getLocale()->willReturn($localeEN);
        $completeness->getChannel()->willReturn($channelEcommerce);
        $completeness->getRatio()->willReturn(76);

        $localeEN->getCode()->willReturn('en_US');
        $channelEcommerce->getCode()->willReturn('ecommerce');

        $data = [
            'identifier'   => 'purple_tshirt',
            'family'       => 'Tshirt',
            'groups'       => 'Promotion',
            'enabled'      => true,
            'values'       => [
                'text' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'my text',
                    ]
                ]
            ],
            'created'      => '2017-01-01T01:03:34+01:00',
            'updated'      => '2017-01-01T01:04:34+01:00',
            'label'        => 'Purple tshirt',
            'completeness' => 76
        ];

        $this->normalize($product, 'datagrid', ['locales' => ['en_US'], 'channels' => ['ecommerce']])->shouldReturn($data);
    }

    function it_normalizes_a_product_without_label(
        $serializer,
        $filter,
        ProductInterface $product,
        GroupInterface $promotion,
        GroupTranslationInterface $promotionEN,
        FamilyInterface $family,
        FamilyTranslationInterface $familyEN,
        ProductValueCollectionInterface $productValues,
        Completeness $completeness,
        LocaleInterface $localeEN,
        ChannelInterface $channelEcommerce
    ) {
        $context = [
            'filter_types' => ['pim.transform.product_value.structured'],
            'locales' => ['en_US'], 'channels' => ['ecommerce']
        ];

        $filter->filterCollection($productValues, 'pim.transform.product_value.structured', $context)
            ->willReturn($productValues);

        $product->getGroups()->willReturn([$promotion]);
        $promotion->getCode()->willReturn('promotion');
        $promotion->getTranslation('en_US')->willReturn($promotionEN);
        $promotionEN->getLabel()->willReturn(null);

        $product->getFamily()->willReturn($family);
        $family->getCode()->willReturn('tshirt');
        $family->getTranslation('en_US')->willReturn($familyEN);
        $familyEN->getLabel()->willReturn(null);

        $product->getIdentifier()->willReturn('purple_tshirt');
        $product->isEnabled()->willReturn(true);
        $product->getValues()->willReturn($productValues);
        $serializer->normalize($productValues, 'datagrid', $context)->willReturn([
            'text' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'my text',
                ]
            ]
        ]);

        $created = new \DateTime('2017-01-01T01:03:34+01:00');
        $product->getCreated()->willReturn($created);
        $serializer->normalize($created, 'datagrid', $context)->willReturn('2017-01-01T01:03:34+01:00');

        $updated = new \DateTime('2017-01-01T01:04:34+01:00');
        $product->getUpdated()->willReturn($updated);
        $serializer->normalize($updated, 'datagrid', $context)->willReturn('2017-01-01T01:04:34+01:00');
        $product->getLabel('en_US')->willReturn('Purple tshirt');
        $product->getCompletenesses()->willReturn([$completeness]);
        $completeness->getLocale()->willReturn($localeEN);
        $completeness->getChannel()->willReturn($channelEcommerce);
        $completeness->getRatio()->willReturn(76);

        $localeEN->getCode()->willReturn('en_US');
        $channelEcommerce->getCode()->willReturn('ecommerce');

        $data = [
            'identifier'   => 'purple_tshirt',
            'family'       => '[tshirt]',
            'groups'       => '[promotion]',
            'enabled'      => true,
            'values'       => [
                'text' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'my text',
                    ]
                ]
            ],
            'created'      => '2017-01-01T01:03:34+01:00',
            'updated'      => '2017-01-01T01:04:34+01:00',
            'label'        => 'Purple tshirt',
            'completeness' => 76
        ];

        $this->normalize($product, 'datagrid', ['locales' => ['en_US'], 'channels' => ['ecommerce']])->shouldReturn($data);
    }
}
