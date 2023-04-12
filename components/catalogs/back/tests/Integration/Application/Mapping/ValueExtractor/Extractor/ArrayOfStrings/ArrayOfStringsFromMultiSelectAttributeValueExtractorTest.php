<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\ArrayOfStrings;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\ArrayOfStrings\ArrayOfStringsFromMultiSelectAttributeValueExtractor;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;
use Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\ValueExtractorTestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 *
 * @covers \Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\ArrayOfStrings\ArrayOfStringsFromMultiSelectAttributeValueExtractor
 */
class ArrayOfStringsFromMultiSelectAttributeValueExtractorTest extends ValueExtractorTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItReturnsTheCorrectType(): void
    {
        $this->assertInstanceOf(
            self::TARGET_TYPES_INTERFACES_MAPPING[self::getContainer()->get(ArrayOfStringsFromMultiSelectAttributeValueExtractor::class)->getSupportedTargetType()],
            self::getContainer()->get(ArrayOfStringsFromMultiSelectAttributeValueExtractor::class),
        );
    }

    public function testItReturnsTheAttributeValue(): void
    {
        $this->createAttribute([
            'code' => 'video_output',
            'type' => 'pim_catalog_multiselect',
            'scopable' => true,
            'localizable' => true,
            'options' => ['VGA', 'HDMI', 'DisplayPort', 'miniHDMI', 'miniDisplayPort'],
        ]);
        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'video_output' => [
                    'ecommerce' => [
                        'en_US' => [
                            'minihdmi',
                            'minidisplayport',
                        ],
                    ],
                ],
            ],
        ];

        $result = self::getContainer()->get(ArrayOfStringsFromMultiSelectAttributeValueExtractor::class)->extract(
            product: $product,
            code: 'video_output',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: ['label_locale' => 'en_US'],
        );

        $this->assertEquals(['miniHDMI', 'miniDisplayPort'], $result);
    }

    public function testItReturnsNullIfNotFound(): void
    {
        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'video_output' => [
                    'ecommerce' => [
                        'en_US' => [
                            'HDMI',
                        ],
                    ],
                ],
            ],
        ];

        $result = self::getContainer()->get(ArrayOfStringsFromMultiSelectAttributeValueExtractor::class)->extract(
            product: $product,
            code: 'video_output',
            locale: '<all_locales>',
            scope: '<all_channels>',
            parameters: ['label_locale' => 'en_US'],
        );

        $this->assertNull($result);
    }

    public function testItReturnsNullIfInconsistentRawValue(): void
    {
        $this->createAttribute([
            'code' => 'video_output',
            'type' => 'pim_catalog_multiselect',
            'scopable' => true,
            'localizable' => true,
            'options' => ['VGA', 'HDMI', 'DisplayPort', 'miniHDMI', 'miniDisplayPort'],
        ]);

        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'video_output' => [
                    'ecommerce' => [
                        'en_US' => [
                            10,
                        ],
                    ],
                ],
            ],
        ];

        $result = self::getContainer()->get(ArrayOfStringsFromMultiSelectAttributeValueExtractor::class)->extract(
            product: $product,
            code: 'video_output',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: ['label_locale' => 'en_US'],
        );

        $this->assertNull($result);
    }

    public function testItReturnsTheSelectValueCodeIfNoTranslation(): void
    {
        $this->createAttribute([
            'code' => 'video_output',
            'type' => 'pim_catalog_multiselect',
            'scopable' => true,
            'localizable' => true,
            'options' => ['VGA', 'HDMI', 'DisplayPort', 'miniHDMI', 'miniDisplayPort'],
        ]);

        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'video_output' => [
                    'ecommerce' => [
                        'en_US' => [
                            'VGA',
                            'HDMI',
                        ],
                    ],
                ],
            ],
        ];

        $result = self::getContainer()->get(ArrayOfStringsFromMultiSelectAttributeValueExtractor::class)->extract(
            product: $product,
            code: 'video_output',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: ['label_locale' => 'en_GB'],
        );

        $this->assertEquals(['[VGA]', '[HDMI]'], $result);
    }
}
