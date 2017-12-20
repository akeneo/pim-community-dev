<?php

namespace spec\Pim\Component\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\ConvertedField;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AssociationColumnsResolver;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnsResolver;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ColumnsMapper;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ColumnsMerger;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\FieldConverter;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter\ValueConverterInterface;
use Pim\Component\Connector\Exception\StructureArrayConversionException;

class ProductSpec extends ObjectBehavior
{
    function let(
        AssociationColumnsResolver $assocColumnsResolver,
        AttributeColumnsResolver $attrColumnsResolver,
        FieldConverter $fieldConverter,
        ColumnsMerger $columnsMerger,
        ColumnsMapper $columnsMapper,
        FieldsRequirementChecker $fieldChecker,
        AttributeRepositoryInterface $attributeRepository,
        ArrayConverterInterface $productValueConverter
    ) {
        $this->beConstructedWith(
            $assocColumnsResolver,
            $attrColumnsResolver,
            $fieldConverter,
            $columnsMerger,
            $columnsMapper,
            $fieldChecker,
            $attributeRepository,
            $productValueConverter
        );
    }

    function it_converts(
        $fieldConverter,
        $columnsMerger,
        $columnsMapper,
        $attrColumnsResolver,
        $assocColumnsResolver,
        $attributeRepository,
        $productValueConverter,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        AttributeInterface $attribute3,
        AttributeInterface $attribute4,
        AttributeInterface $attribute5,
        AttributeInterface $attribute6,
        AttributeInterface $attribute7,
        ConvertedField $categories,
        ConvertedField $enable,
        ConvertedField $xSellGroup,
        ConvertedField $xSellProduct,
        ConvertedField $substitution
    ) {
        $item = [
            'sku'                    => '1069978',
            '7'                      => 'foo',
            'categories'             => 'audio_video_sales,loudspeakers,sony',
            'enabled'                => '1',
            'name'                   => 'Sony SRS-BTV25',
            'release_date-ecommerce' => '2011-08-21',
            'release_date-print'     => '2011-07-15',
            'price-EUR'              => '15',
            'price-USD'              => '10',
            'X_SELL-groups'          => 'group-A',
            'X_SELL-products'        => 'sku-A, sku-B',
            'SUBSTITUTION-products'  => 'sku-C'
        ];

        $itemMerged = [
            'sku'                    => '1069978',
            '7'                      => 'foo',
            'categories'             => 'audio_video_sales,loudspeakers,sony',
            'enabled'                => '1',
            'name'                   => 'Sony SRS-BTV25',
            'release_date-ecommerce' => '2011-08-21',
            'release_date-print'     => '2011-07-15',
            'price'                  => '15 EUR, 10 USD',
            'X_SELL-groups'          => 'group-A',
            'X_SELL-products'        => 'sku-A, sku-B',
            'SUBSTITUTION-products'  => 'sku-C'
        ];

        $columnsMapper->map($item)->willReturn($item);

        $attrColumnsResolver->resolveAttributeColumns()->willReturn(
            ['sku', 'name', 'release_date-ecommerce', 'release_date-print', 7, 'price', 'price-EUR', 'price-USD']
        );
        $assocColumnsResolver->resolveAssociationColumns()->willReturn(
            [
                'X_SELL-groups',
                'X_SELL-products',
                'SUBSTITUTION-products'
            ]
        );

        $columnsMerger->merge($item)->willReturn($itemMerged);

        $attrColumnsResolver->resolveIdentifierField()->willReturn('sku');

        $fieldConverter->supportsColumn('sku')->willReturn(false);
        $fieldConverter->supportsColumn('categories')->willReturn(true);
        $fieldConverter->supportsColumn('enabled')->willReturn(true);
        $fieldConverter->supportsColumn('name')->willReturn(false);
        $fieldConverter->supportsColumn('release_date-ecommerce')->willReturn(false);
        $fieldConverter->supportsColumn('release_date-print')->willReturn(false);
        $fieldConverter->supportsColumn('7')->willReturn(false);
        $fieldConverter->supportsColumn('price')->willReturn(false);
        $fieldConverter->supportsColumn('X_SELL-groups')->willReturn(true);
        $fieldConverter->supportsColumn('X_SELL-products')->willReturn(true);
        $fieldConverter->supportsColumn('SUBSTITUTION-products')->willReturn(true);

        $fieldConverter->convert('categories', 'audio_video_sales,loudspeakers,sony')->willReturn($categories);
        $categories->appendTo([])->willReturn(['categories' => ['audio_video_sales', 'loudspeakers', 'sony']]);

        $fieldConverter->convert('enabled', '1')->willReturn($enable);
        $enable->appendTo(['categories' => ['audio_video_sales', 'loudspeakers', 'sony']])
            ->willReturn([
                'categories' => ['audio_video_sales', 'loudspeakers', 'sony'],
                'enabled' => true
            ]);

        $fieldConverter->convert('X_SELL-groups', 'group-A')->willReturn($xSellGroup);
        $xSellGroup->appendTo([
            'categories' => ['audio_video_sales', 'loudspeakers', 'sony'],
            'enabled' => true
        ])->willReturn([
            'categories' => ['audio_video_sales', 'loudspeakers', 'sony'],
            'enabled' => true,
            'associations' => ['X_SELL' => ['groups' => ['group-A']]]
        ]);

        $fieldConverter->convert('X_SELL-products', 'sku-A, sku-B')->willReturn($xSellProduct);
        $xSellProduct->appendTo([
            'categories' => ['audio_video_sales', 'loudspeakers', 'sony'],
            'enabled' => true,
            'associations' => ['X_SELL' => ['groups' => ['group-A']]]
        ])->willReturn([
            'categories' => ['audio_video_sales', 'loudspeakers', 'sony'],
            'enabled' => true,
            'associations' => [
                'X_SELL' => [
                    'groups' => ['group-A'],
                    'products' => ['sku-A', 'sku-B'],
                ],
            ]
        ]);

        $fieldConverter->convert('SUBSTITUTION-products', 'sku-C')->willReturn($substitution);
        $substitution->appendTo([
            'categories' => ['audio_video_sales', 'loudspeakers', 'sony'],
            'enabled' => true,
            'associations' => [
                'X_SELL' => [
                    'groups' => ['group-A'],
                    'products' => ['sku-A', 'sku-B'],
                ],
            ]
        ])->willReturn([
            'categories' => ['audio_video_sales', 'loudspeakers', 'sony'],
            'enabled' => true,
            'associations' => [
                'X_SELL' => [
                    'groups' => ['group-A'],
                    'products' => ['sku-A', 'sku-B'],
                ],
                'SUBSTITUTION' => ['products' => ['sku-C']]
            ]
        ]);

        $attribute1->getType()->willReturn('sku');
        $attribute2->getType()->willReturn('categories');
        $attribute3->getType()->willReturn('enabled');
        $attribute4->getType()->willReturn('name');
        $attribute5->getType()->willReturn('release_date');
        $attribute6->getType()->willReturn('7');
        $attribute7->getType()->willReturn('price');

        $attributeRepository->getIdentifierCode()->willReturn('sku');

        $flatProductValues = [
            'sku'                    => '1069978',
            '7'                      => 'foo',
            'name'                   => 'Sony SRS-BTV25',
            'release_date-ecommerce' => '2011-08-21',
            'release_date-print'     => '2011-07-15',
            'price'                  => '15 EUR, 10 USD'
        ];

        $standardProductValues = [
            'sku' => [
                [
                    'locale' => '',
                    'scope'  => '',
                    'data'   => 1069978,
                ]
            ],
            7 => [
                [
                    'locale' => '',
                    'scope'  => '',
                    'data'   => 'foo'
                ]
            ],
            'name' => [
                [
                    'locale' => '',
                    'scope'  => '',
                    'data'   => 'Sony SRS-BTV25',
                ]
            ],
            'release_date' => [
                [
                    'locale' => '',
                    'scope'  => 'ecommerce',
                    'data'   => '2011-08-21'
                ],
                [
                    'locale' => '',
                    'scope'  => 'print',
                    'data'   => '2011-07-15'
                ]
            ],
            'price' => [
                'locale' => '',
                'scope'  => '',
                'data'   => [['amount' => 15, 'currency' => 'EUR'], ['amount' => 10, 'currency' => 'USD']]
            ]
        ];
        $productValueConverter->convert($flatProductValues)->willReturn($standardProductValues);

        $result = [
            'categories'   => ['audio_video_sales', 'loudspeakers', 'sony'],
            'enabled'      => true,
            'associations' => [
                'X_SELL' => [
                    'groups'   => ['group-A'],
                    'products' => ['sku-A', 'sku-B'],
                ],
                'SUBSTITUTION' => [
                    'products' => ['sku-C'],
                ],
            ],
            'values'       => [
                'sku'          => [
                    [
                        'locale' => '',
                        'scope'  => '',
                        'data'   => 1069978,
                    ]
                ],
                7              => [
                    [
                        'locale' => '',
                        'scope'  => '',
                        'data'   => 'foo',
                    ]
                ],
                'name'         => [
                    [
                        'locale' => '',
                        'scope'  => '',
                        'data'   => 'Sony SRS-BTV25',
                    ]
                ],
                'release_date' => [
                    [
                        'locale' => '',
                        'scope'  => 'ecommerce',
                        'data'   => '2011-08-21'
                    ],
                    [
                        'locale' => '',
                        'scope'  => 'print',
                        'data'   => '2011-07-15'
                    ],

                ],
                'price'        => [
                    'locale' => '',
                    'scope'  => '',
                    'data'   => [['amount' => 15, 'currency' => 'EUR'], ['amount' => 10, 'currency' => 'USD']]
                ],
            ],
            'identifier'   => 1069978
        ];

        $this
            ->convert($item, [])
            ->shouldReturn($result);
    }

    function it_converts_without_associations_depending_on_options(
        $attrColumnsResolver,
        $assocColumnsResolver,
        $fieldConverter,
        $columnsMerger,
        $attributeRepository,
        $productValueConverter,
        ConvertedField $enable,
        AttributeInterface $attribute
    ) {
        $item = ['sku' => '1069978', 'enabled' => true, 'unknown-products' => ['sku2'], 'unknown-groups' => 'groupcode'];
        $filteredItem = ['sku' => '1069978', 'enabled' => true];

        $attrColumnsResolver->resolveAttributeColumns()->willReturn(['sku']);
        $assocColumnsResolver->resolveAssociationColumns()->willReturn([]);

        $columnsMerger->merge($filteredItem)->willReturn($filteredItem);

        $attrColumnsResolver->resolveIdentifierField()->willReturn('sku');

        $attribute->getType()->willReturn('sku');
        $fieldConverter->supportsColumn('sku')->willReturn(false);
        $fieldConverter->supportsColumn('enabled')->willReturn(true);

        $fieldConverter->convert('enabled', true)->willReturn($enable);
        $enable->appendTo([])
            ->willReturn([
                'enabled' => true
            ]);

        $attributeRepository->getIdentifierCode()->willReturn('sku');

        $productValueConverter->convert(['sku' => '1069978'])->willReturn(
            [
                'sku' => [
                    [
                        'locale' => '',
                        'scope'  => '',
                        'data'   => 1069978
                    ]
                ]
            ]
        );

        $result = [
            'enabled'    => true,
            'values'     => [
                'sku' => [
                    [
                        'locale' => '',
                        'scope'  => '',
                        'data'   => 1069978,
                    ]
                ],
            ],
            'identifier' => 1069978,
        ];

        $this
            ->convert($item, ['with_associations' => false])
            ->shouldReturn($result);
    }

    function it_throws_an_exception_when_field_does_not_exist(
        $attrColumnsResolver,
        $assocColumnsResolver,
        $fieldConverter,
        $columnsMerger,
        AttributeInterface $attribute
    ) {
        $item = ['sku' => '1069978', 'enabled' => true, 'unknown_field' => 'foo', 'other_unknown_field' => 'bar'];

        $attrColumnsResolver->resolveAttributeColumns()->willReturn(['sku']);
        $assocColumnsResolver->resolveAssociationColumns()->willReturn([]);

        $columnsMerger->merge($item)->willReturn($item);

        $attrColumnsResolver->resolveIdentifierField()->willReturn('sku');

        $attribute->getType()->willReturn('sku');
        $fieldConverter->supportsColumn('sku')->willReturn(false);

        $this->shouldThrow(
            new StructureArrayConversionException('The fields "unknown_field, other_unknown_field" do not exist')
        )->during(
            'convert',
            [$item],
            ['with_associations' => false]
        );
    }

    function it_throws_an_exception_when_association_field_does_not_exist(
        $attrColumnsResolver,
        $assocColumnsResolver,
        $fieldConverter,
        $columnsMerger,
        AttributeInterface $attribute
    ) {
        $item = ['sku' => '1069978', 'enabled' => true, 'unknown-products' => ['sku2'], 'unknown-groups' => 'groupcode'];

        $attrColumnsResolver->resolveAttributeColumns()->willReturn(['sku']);
        $assocColumnsResolver->resolveAssociationColumns()->willReturn([]);

        $columnsMerger->merge($item)->willReturn($item);

        $attrColumnsResolver->resolveIdentifierField()->willReturn('sku');

        $attribute->getType()->willReturn('sku');

        $fieldConverter->supportsColumn('sku')->willReturn(true);

        $this->shouldThrow(
            new StructureArrayConversionException('The fields "unknown-products, unknown-groups" do not exist')
        )->during(
            'convert',
            [$item],
            ['with_associations' => true]
        );
    }
}
