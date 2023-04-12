<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\Number;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\Number\NumberFromNumberAttributeValueExtractor;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;
use Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\ValueExtractorTestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 *
 * @covers \Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\Number\NumberFromNumberAttributeValueExtractor
 */
class NumberFromNumberAttributeValueExtractorTest extends ValueExtractorTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testItReturnsTheCorrectType(): void
    {
        $this->assertInstanceOf(
            self::TARGET_TYPES_INTERFACES_MAPPING[self::getContainer()->get(NumberFromNumberAttributeValueExtractor::class)->getSupportedTargetType()],
            self::getContainer()->get(NumberFromNumberAttributeValueExtractor::class),
        );
    }

    /**
     * @dataProvider numberRawValueProvider
     */
    public function testItReturnsTheValueForNumberAttribute(string|int|float $rawValue, int|float $expected): void
    {
        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'optical_zoom' => [
                    'ecommerce' => [
                        'en_US' => $rawValue,
                    ],
                ],
            ],
        ];

        $result = self::getContainer()->get(NumberFromNumberAttributeValueExtractor::class)->extract(
            product: $product,
            code: 'optical_zoom',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: [],
        );

        $this->assertEquals($expected, $result);
    }

    public function numberRawValueProvider(): array
    {
        return [
            'with int' => [
                'rawValue' => 42,
                'expected' => 42,
            ],
            'with float' => [
                'rawValue' => 42.7,
                'expected' => 42.7,
            ],
            'with int as string' => [
                'rawValue' => '42',
                'expected' => 42,
            ],
            'with float as string' => [
                'rawValue' => '42.7',
                'expected' => 42.7,
            ],
        ];
    }

    public function testItReturnsNullIfNotFound(): void
    {
        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'optical_zoom' => [
                    'ecommerce' => [
                        'en_US' => 3.7,
                    ],
                ],
            ],
        ];

        $result = self::getContainer()->get(NumberFromNumberAttributeValueExtractor::class)->extract(
            product: $product,
            code: 'optical_zoom',
            locale: '<all_locales>',
            scope: '<all_channels>',
            parameters: [],
        );

        $this->assertNull($result);
    }

    public function testItReturnsNullIfInconsistentRawValue(): void
    {
        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'optical_zoom' => [
                    'ecommerce' => [
                        'en_US' => 'trois',
                    ],
                ],
            ],
        ];

        $result = self::getContainer()->get(NumberFromNumberAttributeValueExtractor::class)->extract(
            product: $product,
            code: 'optical_zoom',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: [],
        );

        $this->assertNull($result);
    }
}
