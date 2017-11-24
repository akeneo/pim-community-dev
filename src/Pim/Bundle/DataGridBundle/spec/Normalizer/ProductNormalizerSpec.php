<?php

namespace spec\Pim\Bundle\DataGridBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\DataGridBundle\Normalizer\ProductNormalizer;
use Pim\Bundle\EnrichBundle\Normalizer\ImageNormalizer;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\Completeness;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyTranslationInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\GroupTranslationInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer, CollectionFilterInterface $filter, ImageNormalizer $imageNormalizer)
    {
        $this->beConstructedWith($filter, $imageNormalizer);

        $normalizer->implement(NormalizerInterface::class);
        $this->setNormalizer($normalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductNormalizer::class);
        $this->shouldBeAnInstanceOf(NormalizerAwareInterface::class);
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
        $normalizer,
        $filter,
        $imageNormalizer,
        ProductInterface $product,
        GroupInterface $promotion,
        GroupTranslationInterface $promotionEN,
        FamilyInterface $family,
        FamilyTranslationInterface $familyEN,
        ValueCollectionInterface $values,
        Completeness $completeness,
        LocaleInterface $localeEN,
        ChannelInterface $channelEcommerce,
        ValueInterface $image
    ) {
        $context = [
            'filter_types' => ['pim.transform.product_value.structured'],
            'locales'      => ['en_US'],
            'channels'     => ['ecommerce'],
            'data_locale'  => 'en_US',
        ];

        $product->getId()->willReturn(78);
        $filter->filterCollection($values, 'pim.transform.product_value.structured', $context)
            ->willReturn($values);

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
        $product->getValues()->willReturn($values);
        $normalizer->normalize($values, 'datagrid', $context)->willReturn([
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
        $normalizer->normalize($created, 'datagrid', $context)->willReturn('2017-01-01T01:03:34+01:00');

        $updated = new \DateTime('2017-01-01T01:04:34+01:00');
        $product->getUpdated()->willReturn($updated);
        $normalizer->normalize($updated, 'datagrid', $context)->willReturn('2017-01-01T01:04:34+01:00');
        $product->getLabel('en_US', 'ecommerce')->willReturn('Purple tshirt');
        $product->getCompletenesses()->willReturn([$completeness]);
        $product->getImage()->willReturn($image);
        $imageNormalizer->normalize($image, Argument::any())->willReturn([
            'filePath'         => '/p/i/m/4/all.png',
            'originalFileName' => 'all.png',
        ]);
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
            'image'        => [
                'filePath'         => '/p/i/m/4/all.png',
                'originalFileName' => 'all.png',
            ],
            'completeness' => 76,
            'document_type' => 'product',
            'technical_id' => 78,
            'search_id' => 'product_78',
            'complete_variant_product' => null,
        ];

        $this->normalize($product, 'datagrid', $context)->shouldReturn($data);
    }

    function it_normalizes_a_product_without_label(
        $normalizer,
        $filter,
        $imageNormalizer,
        ProductInterface $product,
        GroupInterface $promotion,
        GroupTranslationInterface $promotionEN,
        FamilyInterface $family,
        FamilyTranslationInterface $familyEN,
        ValueCollectionInterface $productValues,
        Completeness $completeness,
        LocaleInterface $localeEN,
        ChannelInterface $channelEcommerce,
        ValueInterface $image
    ) {
        $context = [
            'filter_types' => ['pim.transform.product_value.structured'],
            'locales'      => ['en_US'],
            'channels'     => ['ecommerce'],
            'data_locale'  => 'en_US',
        ];

        $product->getId()->willReturn(78);
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
        $normalizer->normalize($productValues, 'datagrid', $context)->willReturn([
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
        $normalizer->normalize($created, 'datagrid', $context)->willReturn('2017-01-01T01:03:34+01:00');

        $updated = new \DateTime('2017-01-01T01:04:34+01:00');
        $product->getUpdated()->willReturn($updated);
        $normalizer->normalize($updated, 'datagrid', $context)->willReturn('2017-01-01T01:04:34+01:00');
        $product->getLabel('en_US', 'ecommerce')->willReturn('Purple tshirt');
        $product->getCompletenesses()->willReturn([$completeness]);
        $product->getImage()->willReturn($image);
        $imageNormalizer->normalize($image, Argument::any())->willReturn([
            'filePath'         => '/p/i/m/4/all.png',
            'originalFileName' => 'all.png'
        ]);
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
            'image'        => [
                'filePath'         => '/p/i/m/4/all.png',
                'originalFileName' => 'all.png',
            ],
            'completeness' => 76,
            'document_type' => 'product',
            'technical_id' => 78,
            'search_id' => 'product_78',
            'complete_variant_product' => null,
        ];

        $this->normalize($product, 'datagrid', $context)->shouldReturn($data);
    }
}
