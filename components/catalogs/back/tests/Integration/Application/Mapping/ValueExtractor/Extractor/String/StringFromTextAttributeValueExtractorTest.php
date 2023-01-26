<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\String;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\String\StringFromTextAttributeValueExtractor;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;
use Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\ValueExtractorTestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 *
 * @covers \Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\String\StringFromTextAttributeValueExtractor
 */
class StringFromTextAttributeValueExtractorTest extends ValueExtractorTestCase
{
    private ?StringFromTextAttributeValueExtractor $extractor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extractor = self::getContainer()->get(StringFromTextAttributeValueExtractor::class);
    }

    public function testItReturnsTheCorrectType(): void
    {
        $this->assertInstanceOf(
            self::TARGET_TYPES_INTERFACES_MAPPING[$this->extractor->getSupportedTargetType()],
            $this->extractor,
        );
    }

    public function testItReturnsTheValueForTextAttribute(): void
    {
        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'name' => [
                    'ecommerce' => [
                        'en_US' => 'Product name',
                    ],
                ],
            ],
        ];

        $result = $this->extractor->extract(
            product: $product,
            code: 'name',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: [],
        );

        $this->assertEquals('Product name', $result);
    }

    public function testItReturnsNullIfNotFound(): void
    {
        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'name' => [
                    'ecommerce' => [
                        'en_US' => 'Product name',
                    ],
                ],
            ],
        ];

        $result = $this->extractor->extract(
            product: $product,
            code: 'name',
            locale: '<all_locales>',
            scope: '<all_channels>',
            parameters: [],
        );

        $this->assertEquals(null, $result);
    }
}
