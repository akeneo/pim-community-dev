<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\StringDateTime;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringDateTime\StringDateTimeFromDateAttributeValueExtractor;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;
use Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\ValueExtractorTestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 *
 * @covers \Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringDateTime\StringDateTimeFromDateAttributeValueExtractor
 */
class StringDateTimeFromDateAttributeValueExtractorTest extends ValueExtractorTestCase
{
    private ?StringDateTimeFromDateAttributeValueExtractor $extractor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extractor = self::getContainer()->get(StringDateTimeFromDateAttributeValueExtractor::class);
    }

    public function testItReturnsTheCorrectType(): void
    {
        $this->assertInstanceOf(
            self::TARGET_TYPES_INTERFACES_MAPPING[$this->extractor->getSupportedTargetType()],
            $this->extractor,
        );
    }

    public function testItReturnsTheValueForNumberAttribute(): void
    {
        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'release_date' => [
                    'ecommerce' => [
                        'en_US' => '2023-01-31',
                    ],
                ],
            ],
        ];

        $result = $this->extractor->extract(
            product: $product,
            code: 'release_date',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: [],
        );

        $this->assertEquals('2023-01-31', $result);
    }

    public function testItReturnsNullIfNotFound(): void
    {
        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'release_date' => [
                    'ecommerce' => [
                        'en_US' => '2023-01-31',
                    ],
                ],
            ],
        ];

        $result = $this->extractor->extract(
            product: $product,
            code: 'release_date',
            locale: '<all_locales>',
            scope: '<all_channels>',
            parameters: [],
        );

        $this->assertEquals(null, $result);
    }
}
