<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Attribute;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;
use Akeneo\Catalogs\Application\Service\AttributeValueExtractor\AttributeValueExtractorRegistry;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Attribute\FindOneAttributeByCodeQuery
 */
class SelectExtractorTest extends IntegrationTestCase
{
    private ?AttributeValueExtractorRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->registry = self::getContainer()->get(AttributeValueExtractorRegistry::class);
    }

    public function testItReturnsTheAttributeValue(): void
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

        $result = $this->registry->extract(
            product: $product,
            attributeCode: 'color',
            attributeType: 'pim_catalog_simpleselect',
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
                'name' => [
                    'ecommerce' => [
                        'en_US' => 'red',
                    ],
                ],
            ],
        ];

        $result = $this->registry->extract(
            product: $product,
            attributeCode: 'name',
            attributeType: 'pim_catalog_simpleselect',
            locale: '<all_locales>',
            scope: '<all_channels>',
            parameters: ['label_locale' => 'en_US'],
        );

        $this->assertEquals(null, $result);
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

        $result = $this->registry->extract(
            product: $product,
            attributeCode: 'color',
            attributeType: 'pim_catalog_simpleselect',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: ['label_locale' => 'en_GB'],
        );

        $this->assertEquals('[red]', $result);
    }
}
