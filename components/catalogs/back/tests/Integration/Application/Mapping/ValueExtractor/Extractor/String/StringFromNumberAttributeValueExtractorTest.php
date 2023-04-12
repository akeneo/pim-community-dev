<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\String;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\String\StringFromNumberAttributeValueExtractor;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;
use Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\ValueExtractorTestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 *
 * @covers \Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\String\StringFromNumberAttributeValueExtractor
 */
class StringFromNumberAttributeValueExtractorTest extends ValueExtractorTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testItReturnsTheCorrectType(): void
    {
        $this->assertInstanceOf(
            self::TARGET_TYPES_INTERFACES_MAPPING[self::getContainer()->get(StringFromNumberAttributeValueExtractor::class)->getSupportedTargetType()],
            self::getContainer()->get(StringFromNumberAttributeValueExtractor::class),
        );
    }

    public function testItReturnsTheValueForNumberAttribute(): void
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

        $result = self::getContainer()->get(StringFromNumberAttributeValueExtractor::class)->extract(
            product: $product,
            code: 'optical_zoom',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: [],
        );

        $this->assertEquals('3.7', $result);
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

        $result = self::getContainer()->get(StringFromNumberAttributeValueExtractor::class)->extract(
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

        $result = self::getContainer()->get(StringFromNumberAttributeValueExtractor::class)->extract(
            product: $product,
            code: 'optical_zoom',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: [],
        );

        $this->assertNull($result);
    }
}
