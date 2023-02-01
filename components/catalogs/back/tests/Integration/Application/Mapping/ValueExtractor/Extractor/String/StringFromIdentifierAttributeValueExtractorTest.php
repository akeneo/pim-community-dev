<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\String;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\String\StringFromIdentifierAttributeValueExtractor;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;
use Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\ValueExtractorTestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 *
 * @covers \Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\String\StringFromIdentifierAttributeValueExtractor
 */
class StringFromIdentifierAttributeValueExtractorTest extends ValueExtractorTestCase
{
    private ?StringFromIdentifierAttributeValueExtractor $extractor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extractor = self::getContainer()->get(StringFromIdentifierAttributeValueExtractor::class);
    }

    public function testItReturnsTheCorrectType(): void
    {
        $this->assertInstanceOf(
            self::TARGET_TYPES_INTERFACES_MAPPING[$this->extractor->getSupportedTargetType()],
            $this->extractor,
        );
    }

    public function testItReturnsTheValueForIdentifierAttribute(): void
    {
        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'sku' => [
                    '<all_channels>' => [
                        '<all_locales>' => 't-shirt blue',
                    ],
                ],
            ],
            'identifier' => 't-shirt blue',
        ];

        $result = $this->extractor->extract(
            product: $product,
            code: 'sku',
            locale: '<all_locales>',
            scope: '<all_channels>',
            parameters: [],
        );

        $this->assertEquals('t-shirt blue', $result);
    }

    public function testItReturnsNullIfNotFound(): void
    {
        /** @var RawProduct $product */
        $product = [
            'raw_values' => [],
        ];

        $result = $this->extractor->extract(
            product: $product,
            code: 'sku',
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
                'sku' => [
                    '<all_channels>' => [
                        '<all_locales>' => ['t-shirt blue'],
                    ],
                ],
            ],
            'identifier' => 't-shirt blue',
        ];

        $result = $this->extractor->extract(
            product: $product,
            code: 'sku',
            locale: '<all_locales>',
            scope: '<all_channels>',
            parameters: [],
        );

        $this->assertNull($result);
    }
}
