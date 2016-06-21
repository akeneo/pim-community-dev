<?php

namespace spec\Pim\Component\Connector\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\StandardToFlat\Product\ProductValueConverter;

class VariantGroupSpec extends ObjectBehavior
{
    function let(
        ProductValueConverter $valueConverter,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $identifierAttribute
    ) {
        $attributeRepository->getIdentifier()->willReturn($identifierAttribute);
        $identifierAttribute->getCode()->willReturn('sku');

        $this->beConstructedWith($valueConverter, $attributeRepository);
    }

    function it_converts_from_standard_to_flat_format($valueConverter)
    {
        $valueConverter->convertAttribute('blade_length', [
            [
                'locale' => null,
                'scope'  => null,
                'data'   => [
                    'data' => '80',
                    'unit' => 'CENTIMETER'
                ]
            ]
        ])->willReturn([
            'blade_length'      => '80',
            'blade_length-unit' => 'CENTIMETER',
        ]);

        $valueConverter->convertAttribute('description', [
            [
                'locale' => 'fr_FR',
                'scope'  => 'ecommerce',
                'data'   => '<p>description FR</p>',
            ],
            [
                'locale' => 'en_US',
                'scope'  => 'ecommerce',
                'data'   => '<p>description EN</p>',
            ]
        ])->willReturn([
            'description-fr_FR-ecommerce' => '<p>description FR</p>',
            'description-en_US-ecommerce' => '<p>description EN</p>',
        ]);

        $expected = [
            'code'                        => 'swords',
            'label-en_US'                 => 'Swords',
            'label-fr_FR'                 => 'Épées',
            'axis'                        => 'blade_length,color',
            'type'                        => 'VARIANT',
            'blade_length'                => '80',
            'blade_length-unit'           => 'CENTIMETER',
            'description-fr_FR-ecommerce' => '<p>description FR</p>',
            'description-en_US-ecommerce' => '<p>description EN</p>',
        ];

        $item = [
            'code'   => 'swords',
            'labels' => [
                'en_US' => 'Swords',
                'fr_FR' => 'Épées'
            ],
            'axis'   => ['blade_length', 'color'],
            'type'   => 'VARIANT',
            'values' => [
                'blade_length'   => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => [
                            'data' => '80',
                            'unit' => 'CENTIMETER'
                        ]
                    ]
                ],
                'description'    => [
                    [
                        'locale' => 'fr_FR',
                        'scope'  => 'ecommerce',
                        'data'   => '<p>description FR</p>',
                    ],
                    [
                        'locale' => 'en_US',
                        'scope'  => 'ecommerce',
                        'data'   => '<p>description EN</p>',
                    ]
                ]
            ]
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
