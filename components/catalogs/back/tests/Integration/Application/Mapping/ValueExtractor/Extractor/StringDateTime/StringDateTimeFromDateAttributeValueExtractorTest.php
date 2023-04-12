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
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItReturnsTheCorrectType(): void
    {
        $this->assertInstanceOf(
            self::TARGET_TYPES_INTERFACES_MAPPING[self::getContainer()->get(
                StringDateTimeFromDateAttributeValueExtractor::class,
            )->getSupportedTargetType()],
            self::getContainer()->get(StringDateTimeFromDateAttributeValueExtractor::class),
        );
    }

    public function testItReturnsTheValueForDateAttribute(): void
    {
        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'release_date' => [
                    'ecommerce' => [
                        '<all_locales>' => '2012-04-08T00:00:00+00:00',
                    ],
                ],
                'end_of_life' => [
                    'ecommerce' => [
                        'en_US' => '2016-01-01T00:00:00+00:00',
                    ],
                ],
            ],
        ];

        $releaseDate = self::getContainer()->get(StringDateTimeFromDateAttributeValueExtractor::class)->extract(
            product: $product,
            code: 'release_date',
            locale: '<all_locales>',
            scope: 'ecommerce',
            parameters: [],
        );

        $this->assertEquals('2012-04-08T00:00:00+00:00', $releaseDate);

        $endOfLife = self::getContainer()->get(StringDateTimeFromDateAttributeValueExtractor::class)->extract(
            product: $product,
            code: 'end_of_life',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: [],
        );

        $this->assertEquals('2016-01-01T00:00:00+00:00', $endOfLife);
    }

    public function testItReturnsNullIfNotFound(): void
    {
        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'release_date' => [
                    'ecommerce' => [
                        '<all_locales>' => '2012-04-08T00:00:00+00:00',
                    ],
                ],
            ],
        ];

        $this->createAttribute([
            'code' => 'release_date',
            'type' => 'pim_catalog_date',
            'group' => 'other',
            'scopable' => true,
            'localizable' => false,
        ]);

        $result = self::getContainer()->get(StringDateTimeFromDateAttributeValueExtractor::class)->extract(
            product: $product,
            code: 'release_date',
            locale: null,
            scope: 'print',
            parameters: [],
        );

        $this->assertNull($result);
    }

    public function testItReturnsNullIfInconsistentRawValue(): void
    {
        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'release_date' => [
                    'ecommerce' => [
                        'en_US' => 20230131,
                    ],
                ],
            ],
        ];

        $result = self::getContainer()->get(StringDateTimeFromDateAttributeValueExtractor::class)->extract(
            product: $product,
            code: 'release_date',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: [],
        );

        $this->assertNull($result);
    }
}
