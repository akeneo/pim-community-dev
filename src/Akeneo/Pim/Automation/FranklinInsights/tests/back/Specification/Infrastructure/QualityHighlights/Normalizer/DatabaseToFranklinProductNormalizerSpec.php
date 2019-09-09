<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\QualityHighlights\Normalizer;

use Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights\Normalizer\ProductNormalizerInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeType;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Model\Read\Attribute;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Model\Read\Product;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectSupportedAttributesByFamilyQueryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PhpSpec\ObjectBehavior;

class DatabaseToFranklinProductNormalizerSpec extends ObjectBehavior
{
    function let(SelectSupportedAttributesByFamilyQueryInterface $selectAttributesByFamilyQuery)
    {
        $this->beConstructedWith($selectAttributesByFamilyQuery);
    }

    function it_is_a_product_normalizer()
    {
        $this->shouldImplement(ProductNormalizerInterface::class);
    }

    function it_normalizes_a_product_from_database_to_franklin(SelectSupportedAttributesByFamilyQueryInterface $selectAttributesByFamilyQuery)
    {
        $selectAttributesByFamilyQuery->execute(new FamilyCode('mugs'))->willReturn([
            'text' => new Attribute(new AttributeCode('text'), new AttributeType(AttributeTypes::TEXT)),
            'empty_text' => new Attribute(new AttributeCode('empty_text'), new AttributeType(AttributeTypes::TEXT)),
            'textarea' => new Attribute(new AttributeCode('textarea'), new AttributeType(AttributeTypes::TEXTAREA)),
            'simple_select' => new Attribute(new AttributeCode('simple_select'), new AttributeType(AttributeTypes::OPTION_SIMPLE_SELECT)),
            'multi_select' => new Attribute(new AttributeCode('multi_select'), new AttributeType(AttributeTypes::OPTION_MULTI_SELECT)),
            'number' => new Attribute(new AttributeCode('number'), new AttributeType(AttributeTypes::NUMBER)),
            'bool_true' => new Attribute(new AttributeCode('bool_true'), new AttributeType(AttributeTypes::BOOLEAN)),
            'bool_false' => new Attribute(new AttributeCode('bool_false'), new AttributeType(AttributeTypes::BOOLEAN)),
            'metric' => new Attribute(new AttributeCode('metric'), new AttributeType(AttributeTypes::METRIC)),
        ]);

        $product = new Product(
            new ProductId(42),
            new FamilyCode('mugs'),
            [
                'text' => [
                    '<all_channels>' => [
                        'en_US' => 'Product test',
                        'en_CA' => 'Produit test'
                    ]
                ],
                'empty_text' => [
                    '<all_channels>' => [
                        'en_US' => null,
                    ]
                ],
                'textarea' => [
                    'ecommerce' => [
                        'en_US' => 'A product test',
                        'en_CA' => 'Un produit test'
                    ],
                    'mobile' => [
                        'en_US' => 'ProductTest'
                    ]
                ],
                'simple_select' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'red'
                    ]
                ],
                'multi_select' => [
                    '<all_channels>' => [
                        '<all_locales>' => ['wood', 'metal']
                    ]
                ],
                'number' => [
                    '<all_channels>' => [
                        '<all_locales>' => 123
                    ]
                ],
                'bool_true' => [
                    '<all_channels>' => [
                        '<all_locales>' => true
                    ]
                ],
                'bool_false' => [
                    '<all_channels>' => [
                        '<all_locales>' => false
                    ]
                ],
                'metric' => [
                    '<all_channels>' => [
                        '<all_locales>' => [
                            'unit' => 'MILLIMETER',
                            'amount'=> 12,
                            'family' => 'Length',
                            'base_data' => '0.01234',
                            'base_unit' => 'METER'
                        ]
                    ]
                ]
            ]
        );

        $expectedNormalizedProduct = [
            'id' => '42',
            'family' => 'mugs',
            'attributes' => [
                'text' => [
                    [
                        'value' => 'Product test',
                        'locale' => 'en_US',
                        'channel' => null,
                    ],
                    [
                        'value' => 'Produit test',
                        'locale' => 'en_CA',
                        'channel' => null,
                    ]
                ],
                'empty_text' => [
                    [
                        'value' => '',
                        'locale' => 'en_US',
                        'channel' => null,
                    ],
                ],
                'textarea' => [
                    [
                        'value' => 'A product test',
                        'locale' => 'en_US',
                        'channel' => 'ecommerce',
                    ],
                    [
                        'value' => 'Un produit test',
                        'locale' => 'en_CA',
                        'channel' => 'ecommerce',
                    ],
                    [
                        'value' => 'ProductTest',
                        'locale' => 'en_US',
                        'channel' => 'mobile',
                    ],
                ],
                'simple_select' => [
                    [
                        'value' => 'red',
                        'locale' => null,
                        'channel' => null,
                    ]
                ],
                'multi_select' => [
                    [
                        'value' => 'wood,metal',
                        'locale' => null,
                        'channel' => null,
                    ],
                ],
                'number' => [
                    [
                        'value' => '123',
                        'locale' => null,
                        'channel' => null,
                    ],
                ],
                'bool_true' => [
                    [
                        'value' => 'Yes',
                        'locale' => null,
                        'channel' => null,
                    ],
                ],
                'bool_false' => [
                    [
                        'value' => 'No',
                        'locale' => null,
                        'channel' => null,
                    ],
                ],
                'metric' => [
                    [
                        'value' => '12 MILLIMETER',
                        'locale' => null,
                        'channel' => null,
                    ],
                ],
            ],
        ];

        $this->normalize($product)->shouldReturn($expectedNormalizedProduct);
    }

    function it_removes_unsupported_attributes(SelectSupportedAttributesByFamilyQueryInterface $selectAttributesByFamilyQuery)
    {
        $selectAttributesByFamilyQuery->execute(new FamilyCode('mugs'))->willReturn([
            'name' => new Attribute(new AttributeCode('name'), new AttributeType(AttributeTypes::TEXT)),
        ]);

        $product = new Product(
            new ProductId(42),
            new FamilyCode('mugs'),
            [
                'name' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'Product test',
                    ]
                ],
                'sku' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'product_test'
                    ]
                ],
            ]
        );

        $expectedNormalizedProduct = [
            'id' => '42',
            'family' => 'mugs',
            'attributes' => [
                'name' => [
                    [
                        'value' => 'Product test',
                        'locale' => null,
                        'channel' => null,
                    ],
                ],
            ],
        ];

        $this->normalize($product)->shouldReturn($expectedNormalizedProduct);
    }

    function it_removes_unsupported_locales(SelectSupportedAttributesByFamilyQueryInterface $selectAttributesByFamilyQuery)
    {
        $selectAttributesByFamilyQuery->execute(new FamilyCode('mugs'))->willReturn([
            'name' => new Attribute(new AttributeCode('name'), new AttributeType(AttributeTypes::TEXT)),
        ]);

        $product = new Product(
            new ProductId(42),
            new FamilyCode('mugs'),
            [
                'name' => [
                    '<all_channels>' => [
                        'en_US' => 'Product test',
                        'en_CA' => 'Product test!!',
                        'fr_FR' => 'Produit test',
                        'de_DE' => 'Testprodukt',
                    ]
                ],
            ]
        );

        $expectedNormalizedProduct = [
            'id' => '42',
            'family' => 'mugs',
            'attributes' => [
                'name' => [
                    [
                        'value' => 'Product test',
                        'locale' => 'en_US',
                        'channel' => null,
                    ],
                    [
                        'value' => 'Product test!!',
                        'locale' => 'en_CA',
                        'channel' => null,
                    ],
                ],
            ],
        ];

        $this->normalize($product)->shouldReturn($expectedNormalizedProduct);
    }
}
