<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Attribute;

use Akeneo\Catalogs\Application\Service\AttributeValueExtractor\AttributeValueExtractorRegistry;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Attribute\FindOneAttributeByCodeQuery
 */
class TextExtractorTest extends IntegrationTestCase
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
        $product = [
            'raw_values' => [
                'name' => [
                    'ecommerce' => [
                        'en_EN' => 'Product name'
                    ]
                ]
            ]
        ];

        $result = $this->registry->extract(
            product: $product,
            attributeCode: 'name',
            attributeType: 'pim_catalog_text',
            locale: 'en_EN',
            scope: 'ecommerce',
            parameters: [],
        );

        $this->assertEquals('Product name', $result);
    }

    public function testItReturnsNullIfNotFound(): void
    {
        $product = [
            'raw_values' => [
                'name' => [
                    'ecommerce' => [
                        'en_EN' => 'Product name'
                    ]
                ]
            ]
        ];

        $result = $this->registry->extract(
            product: $product,
            attributeCode: 'name',
            attributeType: 'pim_catalog_text',
            locale: '<all_locales>',
            scope: '<all_channels>',
            parameters: [],
        );

        $this->assertEquals(null, $result);
    }
}
