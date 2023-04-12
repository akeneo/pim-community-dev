<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\Boolean;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\Boolean\BooleanFromBooleanAttributeValueExtractor;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;
use Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\ValueExtractorTestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 *
 * @covers \Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\Boolean\BooleanFromBooleanAttributeValueExtractor
 */
class BooleanFromBooleanAttributeValueExtractorTest extends ValueExtractorTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testItReturnsTheCorrectType(): void
    {
        $this->assertInstanceOf(
            self::TARGET_TYPES_INTERFACES_MAPPING[self::getContainer()->get(BooleanFromBooleanAttributeValueExtractor::class)->getSupportedTargetType()],
            self::getContainer()->get(BooleanFromBooleanAttributeValueExtractor::class),
        );
    }

    public function testItReturnsTheValueForBooleanAttribute(): void
    {
        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'is_released' => [
                    'ecommerce' => [
                        'en_US' => true,
                    ],
                ],
            ],
        ];

        $result = self::getContainer()->get(BooleanFromBooleanAttributeValueExtractor::class)->extract(
            product: $product,
            code: 'is_released',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: [],
        );

        $this->assertTrue($result);
    }

    public function testItReturnsNullIfNotFound(): void
    {
        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'is_released' => [
                    'ecommerce' => [
                        'en_US' => true,
                    ],
                ],
            ],
        ];

        $result = self::getContainer()->get(BooleanFromBooleanAttributeValueExtractor::class)->extract(
            product: $product,
            code: 'is_released',
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
                'is_released' => [
                    'ecommerce' => [
                        'en_US' => 'true',
                    ],
                ],
            ],
        ];

        $result = self::getContainer()->get(BooleanFromBooleanAttributeValueExtractor::class)->extract(
            product: $product,
            code: 'is_released',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: [],
        );

        $this->assertNull($result);
    }
}
