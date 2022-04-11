<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Rows;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\LinkedProductsNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class LinkedProductsNormalizerSpec extends ObjectBehavior
{
    function let(ImageNormalizer $imageNormalizer)
    {
        $this->beConstructedWith($imageNormalizer);
    }

    function it_is_a_linked_products_normalizer()
    {
        $this->shouldBeAnInstanceOf(LinkedProductsNormalizer::class);
    }

    function it_normalizes_product_and_product_model_rows($imageNormalizer)
    {
        $values = new WriteValueCollection([ScalarValue::value('scalar_attribute', 'data')]);

        $productRow = Row::fromProduct(
            'identifier',
            'family label',
            ['group_1', 'group_2'],
            true,
            new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
            'data',
            MediaValue::value('media_attribute', new FileInfo()),
            90,
            '54162e35-ff81-48f1-96d5-5febd3f00fd5',
            'parent_code',
            $values
        );

        $productModelRow = Row::fromProductModel(
            'identifier_model',
            'family label model',
            new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
            'data model',
            null,
            2,
            [
                'complete' => 0,
                'total' => 1
            ],
            'parent_code',
            $values
        );

        $imageNormalizer->normalize($productRow->image(), 'en_US', 'ecommerce')->willReturn([
            'filePath'         => '/p/i/m/4/all.png',
            'originalFileName' => 'all.png',
        ]);

        $imageNormalizer->normalize(null, 'en_US', 'ecommerce')->willReturn(null);

        $expected = [
            [
                'id'           => '54162e35-ff81-48f1-96d5-5febd3f00fd5',
                'identifier'   => 'identifier',
                'label'        => 'data',
                'document_type' => 'product',
                'image'        => [
                    'filePath'         => '/p/i/m/4/all.png',
                    'originalFileName' => 'all.png',
                ],
                'completeness' => 90,
                'variant_product_completenesses' => null
            ],  [
                'id'           => 2,
                'identifier'   => 'identifier_model',
                'label'        => 'data model',
                'document_type' => 'product_model',
                'image'        => null,
                'completeness' => null,
                'variant_product_completenesses' => [
                    'completeChildren' => 0,
                    'totalChildren' => 1
                ]
            ]
        ];

        $this->normalize(new Rows([$productRow, $productModelRow], 2, 2, 0), 'ecommerce', 'en_US')
            ->shouldReturn($expected);
    }
}
