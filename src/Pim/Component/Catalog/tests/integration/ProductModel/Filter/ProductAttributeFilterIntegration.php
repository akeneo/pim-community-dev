<?php

namespace Pim\Component\Catalog\tests\integration\ProductModel\Filter;

use Akeneo\Test\Integration\TestCase;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductAttributeFilterIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    public function testFilterAttributesNotComingFromFamily()
    {
        $expected = [
            'identifier' => 'shoes',
            'parent' => 'brooksblue',
            'family' => 'shoes',
            'values' => [],
        ];

        $product = [
            'identifier' => 'shoes',
            'parent' => 'brooksblue',
            'family' => 'shoes',
            'values' => [
                'wash_temperature' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'HOT',
                    ]
                ]
            ]
        ];

        $filteredProduct = $this
            ->get('pim_connector.processor.attribute_filter.product')
            ->filter($product);

        $this->assertSame($expected, $filteredProduct);
    }

    public function testFilterAttributesNotComingFromFamilyVariant()
    {
        $expected = [
            'identifier' => 'shoes',
            'parent' => 'brooksblue',
            'family' => 'shoes',
            'values' => [],
        ];

        $product = [
            'identifier' => 'shoes',
            'parent' => 'brooksblue',
            'family' => 'shoes',
            'values' => [
                'brand' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'Mike',
                    ],
                ],
            ]
        ];

        $filteredProduct = $this
            ->get('pim_connector.processor.attribute_filter.product')
            ->filter($product);

        $this->assertSame($expected, $filteredProduct);
    }

    public function testKeepAttributeAndAxesComingFromFamilyVariant()
    {
        $expected = [
            'identifier' => 'shoes',
            'parent' => 'brooksblue',
            'family' => 'shoes',
            'values' => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'shoes',
                    ],
                ],
                'eu_shoes_size' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => '43',
                    ]
                ],
                'weight' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => [
                            'amount' => '600.0000',
                            'unit'   => 'GRAM'
                        ]
                    ]
                ]
            ],
        ];

        $product = [
            'identifier' => 'shoes',
            'parent' => 'brooksblue',
            'family' => 'shoes',
            'values' => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'shoes',
                    ],
                ],
                'eu_shoes_size' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => '43',
                    ]
                ],
                'weight' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => [
                            'amount' => '600.0000',
                            'unit'   => 'GRAM'
                        ]
                    ]
                ]
            ]
        ];

        $filteredProduct = $this
            ->get('pim_connector.processor.attribute_filter.product')
            ->filter($product);

        $this->assertSame($expected, $filteredProduct);
    }
}
