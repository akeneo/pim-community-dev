<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\StringUri;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringUri\StringUriFromImageAttributeValueExtractor;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;
use Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\ValueExtractorTestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 *
 * @covers \Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringUri\StringUriFromImageAttributeValueExtractor
 */
class StringUriFromImageAttributeValueExtractorTest extends ValueExtractorTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testItReturnsTheCorrectType(): void
    {
        $this->assertInstanceOf(
            self::TARGET_TYPES_INTERFACES_MAPPING[self::getContainer()->get(StringUriFromImageAttributeValueExtractor::class)->getSupportedTargetType()],
            self::getContainer()->get(StringUriFromImageAttributeValueExtractor::class),
        );
    }

    public function testItReturnsTheValueForImageAttribute(): void
    {
        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'picture' => [
                    'ecommerce' => [
                        'en_US' => '2/7/6/3/276381a2e49e6cabb1017a80f8d699bf0b4bf2dd_my_picture.jpeg',
                    ],
                ],
            ],
        ];

        $result = self::getContainer()->get(StringUriFromImageAttributeValueExtractor::class)->extract(
            product: $product,
            code: 'picture',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: [],
        );

        $this->assertEquals(
            'http://localhost:8080/api/rest/v1/media-files/2/7/6/3/276381a2e49e6cabb1017a80f8d699bf0b4bf2dd_my_picture.jpeg/download',
            $result,
        );
    }

    public function testItReturnsNullIfNotFound(): void
    {
        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'picture' => [
                    'ecommerce' => [
                        'en_US' => '2/7/6/3/276381a2e49e6cabb1017a80f8d699bf0b4bf2dd_my_picture.jpeg',
                    ],
                ],
            ],
        ];

        $result = self::getContainer()->get(StringUriFromImageAttributeValueExtractor::class)->extract(
            product: $product,
            code: 'picture',
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
                'picture' => [
                    'ecommerce' => [
                        'en_US' => ['2/7/6/3/276381a2e49e6cabb1017a80f8d699bf0b4bf2dd_my_picture.jpeg'],
                    ],
                ],
            ],
        ];

        $result = self::getContainer()->get(StringUriFromImageAttributeValueExtractor::class)->extract(
            product: $product,
            code: 'picture',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: [],
        );

        $this->assertNull($result);
    }
}
