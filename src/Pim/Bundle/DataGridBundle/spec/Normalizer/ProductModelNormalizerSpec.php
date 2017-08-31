<?php

namespace spec\Pim\Bundle\DataGridBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\DataGridBundle\Normalizer\ProductModelNormalizer;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyTranslationInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer, CollectionFilterInterface $filter)
    {
        $this->beConstructedWith($filter);

        $normalizer->implement(NormalizerInterface::class);
        $this->setNormalizer($normalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelNormalizer::class);
        $this->shouldBeAnInstanceOf(NormalizerAwareInterface::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_datagrid_format_and_product_value(ProductModelInterface $product)
    {
        $this->supportsNormalization($product, 'datagrid')->shouldReturn(true);
        $this->supportsNormalization($product, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'datagrid')->shouldReturn(false);
    }

    function it_normalizes_a_product_model_with_label(
        $normalizer,
        $filter,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        FamilyTranslationInterface $familyEN,
        ValueCollectionInterface $values,
        LocaleInterface $localeEN,
        ChannelInterface $channelEcommerce,
        ValueInterface $image
    ) {
        $context = [
            'filter_types' => ['pim.transform.product_value.structured'],
            'locales'      => ['en_US'],
            'channels'     => ['ecommerce'],
        ];

        $productModel->getId()->willReturn(78);
        $filter->filterCollection($values, 'pim.transform.product_value.structured', $context)
            ->willReturn($values);

        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getFamily()->willReturn($family);
        $family->getCode()->willReturn('tshirt');
        $family->getTranslation('en_US')->willReturn($familyEN);
        $familyEN->getLabel()->willReturn('Tshirt');

        $productModel->getCode()->willReturn('purple_tshirt');
        $productModel->getValues()->willReturn($values);
        $normalizer->normalize($values, 'datagrid', $context)->willReturn([
            'text' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'my text',
                ],
            ],
        ]);

        $created = new \DateTime('2017-01-01T01:03:34+01:00');
        $productModel->getCreated()->willReturn($created);
        $normalizer->normalize($created, 'datagrid', $context)->willReturn('2017-01-01T01:03:34+01:00');

        $updated = new \DateTime('2017-01-01T01:04:34+01:00');
        $productModel->getUpdated()->willReturn($updated);
        $normalizer->normalize($updated, 'datagrid', $context)->willReturn('2017-01-01T01:04:34+01:00');

        $productModel->getLabel('en_US')->willReturn('Purple tshirt');

        $productModel->getImage()->willReturn($image);
        $normalizer->normalize($image, Argument::any(), Argument::any())->willReturn([
            'data' => [
                'filePath'         => '/p/i/m/4/all.png',
                'originalFileName' => 'all.png',
            ],
        ]);

        $localeEN->getCode()->willReturn('en_US');

        $channelEcommerce->getCode()->willReturn('ecommerce');

        $data = [
            'identifier' => 'purple_tshirt',
            'family'     => 'Tshirt',
            'values'     => [
                'text' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'my text',
                    ],
                ],
            ],
            'created'    => '2017-01-01T01:03:34+01:00',
            'updated'    => '2017-01-01T01:04:34+01:00',
            'label'      => 'Purple tshirt',
            'image'      => [
                'filePath'         => '/p/i/m/4/all.png',
                'originalFileName' => 'all.png',
            ],
            'variant_products' => '',
            'groups' => null,
            'enabled'      => null,
            'completeness' => null,
            'document_type' => 'product_model',
            'technical_id' => 78,
            'search_id' => 'product_model_78',
        ];

        $this->normalize($productModel, 'datagrid',
            ['locales' => ['en_US'], 'channels' => ['ecommerce']])->shouldReturn($data);
    }

    function it_normalizes_a_product_model_without_label(
        $normalizer,
        $filter,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        FamilyTranslationInterface $familyEN,
        ValueCollectionInterface $values,
        LocaleInterface $localeEN,
        ChannelInterface $channelEcommerce,
        ValueInterface $image
    ) {
        $context = [
            'filter_types' => ['pim.transform.product_value.structured'],
            'locales'      => ['en_US'],
            'channels'     => ['ecommerce'],
        ];

        $filter->filterCollection($values, 'pim.transform.product_value.structured', $context)
            ->willReturn($values);

        $productModel->getId()->willReturn(78);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getFamily()->willReturn($family);
        $family->getCode()->willReturn('tshirt');
        $family->getTranslation('en_US')->willReturn($familyEN);
        $familyEN->getLabel()->willReturn(null);

        $productModel->getCode()->willReturn('purple_tshirt');
        $productModel->getValues()->willReturn($values);
        $normalizer->normalize($values, 'datagrid', $context)->willReturn([
            'text' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'my text',
                ],
            ],
        ]);

        $created = new \DateTime('2017-01-01T01:03:34+01:00');
        $productModel->getCreated()->willReturn($created);
        $normalizer->normalize($created, 'datagrid', $context)->willReturn('2017-01-01T01:03:34+01:00');

        $updated = new \DateTime('2017-01-01T01:04:34+01:00');
        $productModel->getUpdated()->willReturn($updated);
        $normalizer->normalize($updated, 'datagrid', $context)->willReturn('2017-01-01T01:04:34+01:00');

        $productModel->getLabel('en_US')->willReturn('Purple tshirt');

        $productModel->getImage()->willReturn($image);
        $normalizer->normalize($image, Argument::any(), Argument::any())->willReturn([
            'data' => [
                'filePath'         => '/p/i/m/4/all.png',
                'originalFileName' => 'all.png',
            ],
        ]);

        $localeEN->getCode()->willReturn('en_US');

        $channelEcommerce->getCode()->willReturn('ecommerce');

        $data = [
            'identifier'   => 'purple_tshirt',
            'family'       => '[tshirt]',
            'values'       => [
                'text' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'my text',
                    ],
                ],
            ],
            'created'      => '2017-01-01T01:03:34+01:00',
            'updated'      => '2017-01-01T01:04:34+01:00',
            'label'        => 'Purple tshirt',
            'image'        => [
                'filePath'         => '/p/i/m/4/all.png',
                'originalFileName' => 'all.png',
            ],
            'variant_products' => '',
            'groups'       => null,
            'enabled'      => null,
            'completeness' => null,
            'document_type' => 'product_model',
            'technical_id' => 78,
            'search_id' => 'product_model_78',
        ];

        $this->normalize($productModel, 'datagrid', ['locales' => ['en_US'], 'channels' => ['ecommerce']])
            ->shouldReturn($data);
    }
}
