<?php

namespace Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\Number;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\Number\NumberFromMetricAttributeValueExtractor;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductsQueryInterface;
use Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\ValueExtractorTestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawProduct from GetRawProductsQueryInterface
 *
 * @covers \Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\Number\NumberFromPriceCollectionAttributeValueExtractor
 */
class NumberFromMetricAttributeValueExtractorTest extends ValueExtractorTestCase
{
    private ?NumberFromMetricAttributeValueExtractor $extractor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->extractor = self::getContainer()->get(NumberFromMetricAttributeValueExtractor::class);
    }

    public function testItReturnTheCorrectType(): void
    {
        $this->assertInstanceOf(
            self::TARGET_TYPES_INTERFACES_MAPPING[$this->extractor->getSupportedTargetType()],
            $this->extractor,
        );
    }

    /**
     * @dataProvider productWithRawValuesDataProvider
     * @param RawProduct $product
     */
    public function testItReturnsTheValueForMetricAttribute(array $product, int|float $expectedValue): void
    {
        $this->createAttribute([
            'code' => 'weight',
            'type' => 'pim_catalog_metric',
            'scopable' => false,
            'localizable' => false,
            'metric_family' => 'Weight',
            'default_metric_unit' => 'KILOGRAM',
        ]);

        $result = $this->extractor->extract(
            product: $product,
            code: 'weight',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: ['unit' => 'KILOGRAM'],
        );

        $this->assertSame($expectedValue, $result);
    }

    /**
     * @dataProvider inconsistentRawValuesDataProvider
     * @param RawProduct $product
     */
    public function testItReturnsNullIfInconsistentRawValue(array $product): void
    {
        $this->createAttribute([
            'code' => 'weight',
            'type' => 'pim_catalog_metric',
            'scopable' => false,
            'localizable' => false,
            'metric_family' => 'Weight',
            'default_metric_unit' => 'KILOGRAM',
        ]);

        $result = $this->extractor->extract(
            product: $product,
            code: 'weight',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: ['unit' => 'OHM'],
        );

        $this->assertNull($result);
    }

    public function testItReturnsNullWithMissingParameter(): void
    {
        $this->createAttribute([
            'code' => 'weight',
            'type' => 'pim_catalog_metric',
            'scopable' => false,
            'localizable' => false,
            'metric_family' => 'Weight',
            'default_metric_unit' => 'KILOGRAM',
        ]);

        /** @var RawProduct $product */
        $product = [
          'raw_values' => [
              'weight' => [
                  'ecommerce' => [
                      'en_US' => [
                          'unit' => 'GRAM',
                          'amount' => 51,
                      ],
                  ],
              ],
          ],
        ];
        $result = $this->extractor->extract(
            product: $product,
            code: 'weight',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: [],
        );

        $this->assertNull($result);
    }

    public function testItReturnsNullIfNotFound(): void
    {
        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'weight' => [
                    'ecommerce' => [
                        'en_US' => [
                            'unit' => 'GRAM',
                            'amount' => 51,
                        ],
                    ],
                ],
            ],
        ];
        $result = $this->extractor->extract(
            product: $product,
            code: 'weight',
            locale: 'fr_FR',
            scope: 'journal',
            parameters: ['unit' => 'GRAM'],
        );

        $this->assertNull($result);
    }

    public function inconsistentRawValuesDataProvider(): array
    {
        return [
            'metric value not an array' => [
                'product' => [
                    'raw_values' => [
                        'weight' => [
                            'ecommerce' => [
                                'en_US' => 'philodendron',
                            ],
                        ],
                    ],
                ],
            ],
            'invalid metric data structure' => [
                'product' => [
                    'raw_values' => [
                        'weight' => [
                            'ecommerce' => [
                                'en_US' => [
                                    'philodendron' => 'blue',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'invalid metric unit' => [
                'product' => [
                    'raw_values' => [
                        'weight' => [
                            'ecommerce' => [
                                'en_US' => [
                                    'unit' => 52,
                                    'amount' => 45,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'invalid metric amount' => [
                'product' => [
                    'raw_values' => [
                        'weight' => [
                            'ecommerce' => [
                                'unit' => 'CENTIMETER',
                                'amount' => 'apple pie',
                            ],
                        ],
                    ],
                ],
            ],
            'no data in unit' => [
                'product' => [
                    'raw_values' => [
                        'weight' => [
                            'ecommerce' => [
                                'unit' => null,
                                'amount' => 21,
                            ],
                        ],
                    ],
                ],
            ],
            'no data in amount' => [
                'product' => [
                    'raw_values' => [
                        'weight' => [
                            'ecommerce' => [
                                'unit' => 'GRAM',
                                'amount' => null,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function productWithRawValuesDataProvider(): array
    {
        return [
            'integer metric value' => [
                'product' => [
                    'raw_values' => [
                        'weight' => [
                            'ecommerce' => [
                                'en_US' => [
                                    'amount' => 24,
                                    'unit' => 'KILOGRAM',
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValue' => 24,
            ],
            'float metric value' => [
                'product' => [
                    'raw_values' => [
                        'weight' => [
                            'ecommerce' => [
                                'en_US' => [
                                    'amount' => 31.5,
                                    'unit' => 'KILOGRAM',
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValue' => 31.5,
            ],
            'string integer metric value' => [
                'product' => [
                    'raw_values' => [
                        'weight' => [
                            'ecommerce' => [
                                'en_US' => [
                                    'amount' => '35',
                                    'unit' => 'KILOGRAM',
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValue' => 35,
            ],
            'float string metric value' => [
                'product' => [
                    'raw_values' => [
                        'weight' => [
                            'ecommerce' => [
                                'en_US' => [
                                    'amount' => '12.7',
                                    'unit' => 'KILOGRAM',
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValue' => 12.7,
            ],
            'string float with two digit metric value' => [
                'product' => [
                    'raw_values' => [
                        'weight' => [
                            'ecommerce' => [
                                'en_US' => [
                                    'amount' => '21.45',
                                    'unit' => 'KILOGRAM',
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValue' => 21.45,
            ],
            'string integer with digit metric value' => [
                'product' => [
                    'raw_values' => [
                        'weight' => [
                            'ecommerce' => [
                                'en_US' => [
                                    'amount' => '25.00',
                                    'unit' => 'KILOGRAM',
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValue' => 25,
            ],
        ];
    }
}
