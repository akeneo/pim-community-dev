<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\String;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\String\StringFromSimpleSelectAttributeValueExtractor;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;
use Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\ValueExtractorTestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 *
 * @covers \Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\String\StringFromSimpleSelectAttributeValueExtractor
 */
class StringFromSimpleSelectAttributeValueExtractorTest extends ValueExtractorTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItReturnsTheCorrectType(): void
    {
        $this->assertInstanceOf(
            self::TARGET_TYPES_INTERFACES_MAPPING[self::getContainer()->get(StringFromSimpleSelectAttributeValueExtractor::class)->getSupportedTargetType()],
            self::getContainer()->get(StringFromSimpleSelectAttributeValueExtractor::class),
        );
    }

    public function testItReturnsTheValueForSimpleSelectAttribute(): void
    {
        $this->createAttribute([
            'code' => 'color',
            'type' => 'pim_catalog_simpleselect',
            'options' => ['Red', 'Blue'],
            'scopable' => true,
            'localizable' => true,
        ]);

        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'color' => [
                    'ecommerce' => [
                        'en_US' => 'red',
                    ],
                ],
            ],
        ];

        $result = self::getContainer()->get(StringFromSimpleSelectAttributeValueExtractor::class)->extract(
            product: $product,
            code: 'color',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: ['label_locale' => 'en_US'],
        );

        $this->assertEquals('Red', $result);
    }

    public function testItReturnsNullIfNotFound(): void
    {
        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'color' => [
                    'ecommerce' => [
                        'en_US' => 'red',
                    ],
                ],
            ],
        ];

        $result = self::getContainer()->get(StringFromSimpleSelectAttributeValueExtractor::class)->extract(
            product: $product,
            code: 'color',
            locale: '<all_locales>',
            scope: '<all_channels>',
            parameters: ['label_locale' => 'en_US'],
        );

        $this->assertNull($result);
    }

    public function testItReturnsNullIfInconsistentRawValue(): void
    {
        $this->createAttribute([
            'code' => 'color',
            'type' => 'pim_catalog_simpleselect',
            'options' => ['Red', 'Blue'],
            'scopable' => true,
            'localizable' => true,
        ]);

        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'color' => [
                    'ecommerce' => [
                        'en_US' => 10,
                    ],
                ],
            ],
        ];

        $result = self::getContainer()->get(StringFromSimpleSelectAttributeValueExtractor::class)->extract(
            product: $product,
            code: 'color',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: ['label_locale' => 'en_US'],
        );

        $this->assertNull($result);
    }

    public function testItReturnsTheSelectValueCodeIfNoTranslation(): void
    {
        $this->createAttribute([
            'code' => 'color',
            'type' => 'pim_catalog_simpleselect',
            'options' => ['Red', 'Blue'],
            'scopable' => true,
            'localizable' => true,
        ]);

        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'color' => [
                    'ecommerce' => [
                        'en_US' => 'red',
                    ],
                ],
            ],
        ];

        $result = self::getContainer()->get(StringFromSimpleSelectAttributeValueExtractor::class)->extract(
            product: $product,
            code: 'color',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: ['label_locale' => 'en_GB'],
        );

        $this->assertEquals('[red]', $result);
    }
}
