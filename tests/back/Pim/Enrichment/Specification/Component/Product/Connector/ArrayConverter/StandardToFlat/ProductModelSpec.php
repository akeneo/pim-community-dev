<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\QualityScoreConverter;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithQualityScoresInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ProductValueConverter;

class ProductModelSpec extends ObjectBehavior
{
    function let(
        ProductValueConverter $valueConverter,
        QualityScoreConverter $qualityScoreConverter,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $identifierAttribute
    ) {
        $attributeRepository->getIdentifier()->willReturn($identifierAttribute);
        $identifierAttribute->getCode()->willReturn('sku');

        $this->beConstructedWith($valueConverter, $qualityScoreConverter, $attributeRepository);
    }

    function it_converts_from_standard_to_flat_format($valueConverter, $qualityScoreConverter)
    {
        $valueConverter->convertAttribute('weight',
            [
                [
                    'locale' => 'de_DE',
                    'scope'  => 'print',
                    'data'   => [
                        'unit'   => 'KILOGRAM',
                        'amount' => '100'
                    ]
                ]
            ]
        )->willReturn([
            'weight-de_DE-print' => '100',
            'weight-de_DE-print-unit' => 'KILOGRAM',
        ]);

        $qualityScoreConverter->convert([
            "ecommerce" => [
                'en_US' => "B",
                'fr_FR' => "C"
            ]
        ])->willReturn([
            sprintf('%s-en_US-ecommerce', GetProductsWithQualityScoresInterface::FLAT_FIELD_PREFIX) => 'B',
            sprintf('%s-fr_FR-ecommerce', GetProductsWithQualityScoresInterface::FLAT_FIELD_PREFIX) => 'C',
        ]);

        $expected = [
            'code'                               => 'apollon',
            'categories'                         => 'audio_video_sales,loudspeakers,sony',
            'family_variant'                     => 'soundspeaker_color',
            'parent'                             => 'parent_model_code',
            'weight-de_DE-print'                 => '100',
            'weight-de_DE-print-unit'            => 'KILOGRAM',
            'PACK-products'                      => '',
            'PACK-products-quantity'             => '',
            'PACK-product_models'                => '',
            'PACK-product_models-quantity'       => '',
            'PRODUCTSET-products'                => 'bag,socks',
            'PRODUCTSET-products-quantity'       => '2|8',
            'PRODUCTSET-product_models'          => 'braided-hat,tall_antelope',
            'PRODUCTSET-product_models-quantity' => '12|24',
            sprintf('%s-en_US-ecommerce', GetProductsWithQualityScoresInterface::FLAT_FIELD_PREFIX) => 'B',
            sprintf('%s-fr_FR-ecommerce', GetProductsWithQualityScoresInterface::FLAT_FIELD_PREFIX) => 'C',
        ];

        $item = [
            'code'              => 'apollon',
            'categories'        => ['audio_video_sales', 'loudspeakers', 'sony'],
            'family_variant'    => 'soundspeaker_color',
            'parent'            => 'parent_model_code',
            'values'            => [
                'weight'            => [
                    [
                        'locale' => 'de_DE',
                        'scope'  => 'print',
                        'data'   => [
                            'unit' => 'KILOGRAM',
                            'amount' => '100'
                        ]
                    ]
                ],
            ],
            'quantified_associations' => [
                'PACK' => [
                    'products' => [],
                    'product_models' => [],
                ],
                'PRODUCTSET' => [
                    'products' => [
                        [
                            'identifier' => 'bag',
                            'quantity' => 2
                        ],
                        [
                            'identifier' => 'socks',
                            'quantity' => 8
                        ]
                    ],
                    'product_models' => [
                        [
                            'identifier' => 'braided-hat',
                            'quantity' => 12
                        ],
                        [
                            'identifier' => 'tall_antelope',
                            'quantity' => 24
                        ]
                    ]
                ]
            ],
            'quality_scores' => [
                "ecommerce" => [
                    'en_US' => "B",
                    'fr_FR' => "C"
                ]
            ],
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
