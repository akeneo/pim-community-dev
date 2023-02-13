<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\Number;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\Number\NumberFromPriceCollectionAttributeValueExtractor;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;
use Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\ValueExtractorTestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 *
 * @covers \Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\Number\NumberFromPriceCollectionAttributeValueExtractor
 */
class NumberFromPriceCollectionAttributeValueExtractorTest extends ValueExtractorTestCase
{
    private ?NumberFromPriceCollectionAttributeValueExtractor $extractor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extractor = self::getContainer()->get(NumberFromPriceCollectionAttributeValueExtractor::class);
    }

    public function testItReturnsTheCorrectType(): void
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
    public function testItReturnsTheValueForPriceCollectionAttribute(array $product, int|float $expectedValue): void
    {
        $result = $this->extractor->extract(
            product: $product,
            code: 'price',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: ['currency' => 'EUR'],
        );

        $this->assertSame($expectedValue, $result);
    }

    public function productWithRawValuesDataProvider(): array
    {
        return [
              'integer price value' => [
                  'product' => [
                      'raw_values' => [
                          'price' => [
                              'ecommerce' => [
                                  'en_US' => [
                                      ['amount' => 12, 'currency' => 'USD'],
                                      ['amount' => 21, 'currency' => 'EUR'],
                                  ],
                              ],
                          ],
                      ],
                  ],
                  'expectedValue' => 21,
              ],
            'float price value' => [
                'product' => [
                    'raw_values' => [
                        'price' => [
                            'ecommerce' => [
                                'en_US' => [
                                    ['amount' => 21.6, 'currency' => 'EUR'],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValue' => 21.6,
            ],
            'string float price value' => [
                'product' => [
                    'raw_values' => [
                        'price' => [
                            'ecommerce' => [
                                'en_US' => [
                                    ['amount' => '21.6', 'currency' => 'EUR'],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValue' => 21.6,
            ],
            'string integer price value' => [
                'product' => [
                    'raw_values' => [
                        'price' => [
                            'ecommerce' => [
                                'en_US' => [
                                    ['amount' => '56', 'currency' => 'EUR'],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValue' => 56,
            ],
            'string float that is an integer price value' => [
                'product' => [
                    'raw_values' => [
                        'price' => [
                            'ecommerce' => [
                                'en_US' => [
                                    ['amount' => '56.00', 'currency' => 'EUR'],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValue' => 56,
            ],
            'string float with two digit precision price value' => [
                'product' => [
                    'raw_values' => [
                        'price' => [
                            'ecommerce' => [
                                'en_US' => [
                                    ['amount' => '56.20', 'currency' => 'EUR'],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValue' => 56.2,
            ],
        ];
    }

    /**
     * @dataProvider inconsistentRawValuesDataProvider
     * @param RawProduct $product
     */
    public function testItReturnsNullIfInconsistentRawValue(array $product): void
    {
        $result = $this->extractor->extract(
            product: $product,
            code: 'price',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: ['currency' => 'EUR'],
        );

        $this->assertNull($result);
    }
    public function inconsistentRawValuesDataProvider(): array
    {
        return [
            'price collection not a an array' => [
                [
                    'raw_values' => [
                        'price' => [
                            'ecommerce' => [
                                'en_US' => 'bonjour',
                            ],
                        ],
                    ],
                ],
            ],
            'price collection value is not a price value structure' => [
                [
                    'raw_values' => [
                        'price' => [
                            'ecommerce' => [
                                'en_US' => [
                                    ['foo' => 'bar'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testItReturnsNullIfNotFound(): void
    {
        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'price' => [
                    'ecommerce' => [
                        'en_US' => [
                            ['amount' => 12, 'currency' => 'USD'],
                            ['amount' => 21, 'currency' => 'EUR'],
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->extractor->extract(
            product: $product,
            code: 'price',
            locale: '<all_locales>',
            scope: '<all_channels>',
            parameters: ['currency' => 'EUR'],
        );

        $this->assertNull($result);
    }

    public function testItReturnsNullWithMissingParameter(): void
    {
        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'price' => [
                    'ecommerce' => [
                        'en_US' => [
                            ['amount' => 12, 'currency' => 'USD'],
                            ['amount' => 21, 'currency' => 'EUR'],
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->extractor->extract(
            product: $product,
            code: 'price',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: [],
        );

        $this->assertNull($result);
    }
}
