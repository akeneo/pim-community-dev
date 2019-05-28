<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\AdditionalProperty;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Oro\Bundle\PimDataGridBundle\Normalizer\ProductAndProductModelRowNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductAndProductModelRowNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer, ImageNormalizer $imageNormalizer)
    {
        $this->beConstructedWith($imageNormalizer);

        $normalizer->implement(NormalizerInterface::class);
        $this->setNormalizer($normalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductAndProductModelRowNormalizer::class);
        $this->shouldBeAnInstanceOf(NormalizerAwareInterface::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_datagrid_format_and_row()
    {
        $row = Row::fromProduct(
            'identifier',
            'family label',
            ['group_1', 'group_2'],
            true,
            new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
            'label',
            null,
            90,
            1,
            'parent_code',
            new WriteValueCollection([])
        );

        $this->supportsNormalization($row, 'datagrid')->shouldReturn(true);
        $this->supportsNormalization($row, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'datagrid')->shouldReturn(false);
    }

    function it_normalizes_a_row(
        $normalizer,
        $imageNormalizer
    ) {
        $values = new WriteValueCollection([ScalarValue::value('scalar_attribute', 'data')]);

        $row = Row::fromProduct(
            'identifier',
            'family label',
            ['group_1', 'group_2'],
            true,
            new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
            'data',
            MediaValue::value('media_attribute', new FileInfo()),
            90,
            1,
            'parent_code',
            $values
        );
        $row = $row->addAdditionalProperty(new AdditionalProperty('name', 'value'));

        $context = [
            'locales'      => ['en_US'],
            'channels'     => ['ecommerce'],
            'data_locale'  => 'en_US',
        ];

        $normalizer->normalize($values, 'datagrid', $context)->willReturn([
            'scalar_attribute' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'data',
                ]
            ]
        ]);

        $normalizer->normalize($row->created(), 'datagrid', $context)->willReturn('2018-05-23T15:55:50+01:00');
        $normalizer->normalize($row->updated(), 'datagrid', $context)->willReturn('2018-05-23T15:55:50+01:00');

        $imageNormalizer->normalize($row->image(), 'en_US')->willReturn([
            'filePath'         => '/p/i/m/4/all.png',
            'originalFileName' => 'all.png',
        ]);

        $data = [
            'identifier'   => 'identifier',
            'family'       => 'family label',
            'groups'       => 'group_1,group_2',
            'enabled'      => true,
            'values'       => [
                'scalar_attribute' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'data',
                    ]
                ]
            ],
            'created'      => '2018-05-23T15:55:50+01:00',
            'updated'      => '2018-05-23T15:55:50+01:00',
            'label'        => 'data',
            'image'        => [
                'filePath'         => '/p/i/m/4/all.png',
                'originalFileName' => 'all.png',
            ],
            'completeness' => 90,
            'document_type' => 'product',
            'technical_id' => 1,
            'id'           => 1,
            'search_id' => 'product_1',
            'is_checked' => true,
            'complete_variant_product' => [],
            'parent' => 'parent_code',
            'name' => 'value',
        ];

        $this->normalize($row, 'datagrid', $context)->shouldReturn($data);
    }
}
