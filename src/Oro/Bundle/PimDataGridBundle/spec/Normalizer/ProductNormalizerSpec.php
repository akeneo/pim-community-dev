<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Normalizer;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupTranslationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyTranslationInterface;
use Oro\Bundle\PimDataGridBundle\Normalizer\ProductNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        CollectionFilterInterface $filter,
        ImageNormalizer $imageNormalizer,
        GetProductCompletenesses $getProductCompletenesses
    ) {
        $this->beConstructedWith($filter, $imageNormalizer, $getProductCompletenesses);

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
        $getProductCompletenesses,
        ProductInterface $product,
        GroupInterface $promotion,
        GroupTranslationInterface $promotionEN,
        FamilyInterface $family,
        FamilyTranslationInterface $familyEN,
        WriteValueCollection $values,
        ValueInterface $image
    ) {
        $context = [
            'filter_types' => ['pim.transform.product_value.structured'],
            'locales'      => ['en_US'],
            'channels'     => ['ecommerce'],
            'data_locale'  => 'en_US',
        ];

        $product->isVariant()->willReturn(false);
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

        $getProductCompletenesses->fromProductId(78)->willReturn(new ProductCompletenessCollection(78, [
            new ProductCompleteness('ecommerce', 'en_US', 10, 1)
        ]));

        $product->getImage()->willReturn($image);
        $imageNormalizer->normalize($image, Argument::any())->willReturn([
            'filePath'         => '/p/i/m/4/all.png',
            'originalFileName' => 'all.png',
        ]);

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
            'completeness' => 90,
            'document_type' => 'product',
            'technical_id' => 78,
            'search_id' => 'product_78',
            'is_checked' => false,
            'complete_variant_product' => null,
            'parent' => null,
        ];

        $this->normalize($product, 'datagrid', $context)->shouldReturn($data);
    }

    function it_normalizes_a_product_without_label(
        $normalizer,
        $filter,
        $imageNormalizer,
        $getProductCompletenesses,
        ProductInterface $product,
        GroupInterface $promotion,
        GroupTranslationInterface $promotionEN,
        FamilyInterface $family,
        FamilyTranslationInterface $familyEN,
        WriteValueCollection $productValues,
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

        $product->isVariant()->willReturn(false);
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

        $getProductCompletenesses->fromProductId(78)->willReturn(new ProductCompletenessCollection(78, [
            new ProductCompleteness('ecommerce', 'en_US', 10, 1)
        ]));
        $product->getImage()->willReturn($image);
        $imageNormalizer->normalize($image, Argument::any())->willReturn([
            'filePath'         => '/p/i/m/4/all.png',
            'originalFileName' => 'all.png'
        ]);

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
            'completeness' => 90,
            'document_type' => 'product',
            'technical_id' => 78,
            'search_id' => 'product_78',
            'is_checked' => false,
            'complete_variant_product' => null,
            'parent' => null,
        ];

        $this->normalize($product, 'datagrid', $context)->shouldReturn($data);
    }

    function it_normalizes_a_product_with_parent(
        $normalizer,
        $filter,
        $imageNormalizer,
        $getProductCompletenesses,
        ProductInterface $product,
        ProductModelInterface $productModel,
        GroupInterface $promotion,
        GroupTranslationInterface $promotionEN,
        FamilyInterface $family,
        FamilyTranslationInterface $familyEN,
        WriteValueCollection $productValues,
        ValueInterface $image
    ) {
        $context = [
            'filter_types' => ['pim.transform.product_value.structured'],
            'locales'      => ['en_US'],
            'channels'     => ['ecommerce'],
            'data_locale'  => 'en_US',
        ];

        $productModel->getCode()->willReturn('parent_code');
        $product->getParent()->willReturn($productModel);

        $product->isVariant()->willReturn(true);
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

        $getProductCompletenesses->fromProductId(78)->willReturn(new ProductCompletenessCollection(78, [
            new ProductCompleteness('ecommerce', 'en_US', 10, 1)
        ]));
        $product->getImage()->willReturn($image);
        $imageNormalizer->normalize($image, Argument::any())->willReturn([
            'filePath'         => '/p/i/m/4/all.png',
            'originalFileName' => 'all.png'
        ]);

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
            'completeness' => 90,
            'document_type' => 'product',
            'technical_id' => 78,
            'search_id' => 'product_78',
            'is_checked' => false,
            'complete_variant_product' => null,
            'parent' => 'parent_code',
        ];

        $this->normalize($product, 'datagrid', $context)->shouldReturn($data);
    }
}
