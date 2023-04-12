<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Application\Mapping;

use Akeneo\Catalogs\Application\Mapping\ProductMapper;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Application\Mapping\ProductMapper
 */
class ProductMapperTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItMapsProducts(): void
    {
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
            'scopable' => true,
            'localizable' => true,
        ]);

        $product = [
            'uuid' => Uuid::fromString('8985de43-08bc-484d-aee0-4489a56ba02d'),
            'identifier' => 'webcam_logi_tek_x01_de',
            'is_enabled' => true,
            'product_model_code' => null,
            'created' => new \DateTimeImmutable('2023-01-26 12:23:45'),
            'updated' => new \DateTimeImmutable('2023-01-26 14:00:15'),
            'family_code' => 'webcam',
            'group_codes' => [],
            'raw_values' => [
                'name' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'Webcam Logi-tek X01-DE',
                    ],
                ],
            ],
        ];

        $mapping = [
            'uuid' => [
                'source' => 'uuid',
                'scope' => null,
                'locale' => null,
            ],
            'title' => [
                'source' => 'name',
                'scope' => null,
                'locale' => null,
            ],
        ];

        $mappedProduct = self::getContainer()->get(ProductMapper::class)->getMappedProduct($product, $this->getProductMappingSchema(), $mapping);

        $expected = [
            'uuid' => '8985de43-08bc-484d-aee0-4489a56ba02d',
            'title' => 'Webcam Logi-tek X01-DE',
        ];

        $this->assertEquals($expected, $mappedProduct);
    }

    public function testItIgnoresTargetWithNullSourceValue(): void
    {
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
            'scopable' => true,
            'localizable' => true,
        ]);

        $product = [
            'uuid' => Uuid::fromString('8985de43-08bc-484d-aee0-4489a56ba02d'),
            'identifier' => 'webcam_logi_tek_x01_de',
            'is_enabled' => true,
            'product_model_code' => null,
            'created' => new \DateTimeImmutable('2023-01-26 12:23:45'),
            'updated' => new \DateTimeImmutable('2023-01-26 14:00:15'),
            'family_code' => 'webcam',
            'group_codes' => [],
            'raw_values' => [
                'name' => [
                    '<all_channels>' => [
                        '<all_locales>' => null,
                    ],
                ],
            ],
        ];

        $mapping = [
            'uuid' => [
                'source' => 'uuid',
                'scope' => null,
                'locale' => null,
            ],
            'title' => [
                'source' => 'name',
                'scope' => null,
                'locale' => null,
            ],
        ];

        $mappedProduct = self::getContainer()->get(ProductMapper::class)->getMappedProduct($product, $this->getProductMappingSchema(), $mapping);

        $expected = [
            'uuid' => '8985de43-08bc-484d-aee0-4489a56ba02d',
        ];

        $this->assertEquals($expected, $mappedProduct);
    }

    public function testItIgnoresTargetWhenNoValueExtractorFound(): void
    {
        $product = [
            'uuid' => Uuid::fromString('8985de43-08bc-484d-aee0-4489a56ba02d'),
            'identifier' => 'webcam_logi_tek_x01_de',
            'is_enabled' => true,
            'product_model_code' => null,
            'created' => new \DateTimeImmutable('2023-01-26 12:23:45'),
            'updated' => new \DateTimeImmutable('2023-01-26 14:00:15'),
            'family_code' => 'webcam',
            'group_codes' => [],
            'raw_values' => [],
        ];

        $mapping = [
            'uuid' => [
                'source' => 'uuid',
                'scope' => null,
                'locale' => null,
            ],
            'title' => [
                'source' => 'not_a_valid_source',
                'scope' => null,
                'locale' => null,
            ],
        ];

        $mappedProduct = self::getContainer()->get(ProductMapper::class)->getMappedProduct($product, $this->getProductMappingSchema(), $mapping);

        $expected = [
            'uuid' => '8985de43-08bc-484d-aee0-4489a56ba02d',
        ];

        $this->assertEquals($expected, $mappedProduct);
    }

    public function testItUseTheDefaultValueForTargetWithNullProductValue(): void
    {
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
            'scopable' => true,
            'localizable' => true,
        ]);

        $product = [
            'uuid' => Uuid::fromString('8985de43-08bc-484d-aee0-4489a56ba02d'),
            'identifier' => 'webcam_logi_tek_x01_de',
            'is_enabled' => true,
            'product_model_code' => null,
            'created' => new \DateTimeImmutable('2023-01-26 12:23:45'),
            'updated' => new \DateTimeImmutable('2023-01-26 14:00:15'),
            'family_code' => 'webcam',
            'group_codes' => [],
            'raw_values' => [
                'name' => [
                    '<all_channels>' => [
                        '<all_locales>' => null,
                    ],
                ],
            ],
        ];

        $mapping = [
            'uuid' => [
                'source' => 'uuid',
                'scope' => null,
                'locale' => null,
            ],
            'title' => [
                'source' => 'name',
                'scope' => null,
                'locale' => null,
                'default' => 'Default title',
            ],
        ];

        $mappedProduct = self::getContainer()->get(ProductMapper::class)->getMappedProduct($product, $this->getProductMappingSchema(), $mapping);

        $expected = [
            'uuid' => '8985de43-08bc-484d-aee0-4489a56ba02d',
            'title' => 'Default title',
        ];

        $this->assertEquals($expected, $mappedProduct);
    }

    public function testItUseTheDefaultValueForTargetWithNullSource(): void
    {
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
            'scopable' => true,
            'localizable' => true,
        ]);

        $product = [
            'uuid' => Uuid::fromString('8985de43-08bc-484d-aee0-4489a56ba02d'),
            'identifier' => 'webcam_logi_tek_x01_de',
            'is_enabled' => true,
            'product_model_code' => null,
            'created' => new \DateTimeImmutable('2023-01-26 12:23:45'),
            'updated' => new \DateTimeImmutable('2023-01-26 14:00:15'),
            'family_code' => 'webcam',
            'group_codes' => [],
            'raw_values' => [],
        ];

        $mapping = [
            'uuid' => [
                'source' => 'uuid',
                'scope' => null,
                'locale' => null,
            ],
            'title' => [
                'source' => null,
                'scope' => null,
                'locale' => null,
                'default' => 'Default title',
            ],
        ];

        $mappedProduct = self::getContainer()->get(ProductMapper::class)->getMappedProduct($product, $this->getProductMappingSchema(), $mapping);

        $expected = [
            'uuid' => '8985de43-08bc-484d-aee0-4489a56ba02d',
            'title' => 'Default title',
        ];

        $this->assertEquals($expected, $mappedProduct);
    }

    private function getProductMappingSchema(): array
    {
        $rawProductMappingSchema = <<<'JSON_WRAP'
        {
          "$id": "https://example.com/product",
          "$schema": "https://api.akeneo.com/mapping/product/0.0.4/schema",
          "$comment": "My first schema !",
          "title": "Product Mapping",
          "description": "JSON Schema describing the structure of products expected by our application",
          "type": "object",
          "properties": {
            "uuid": {
              "type": "string"
            },
            "title": {
              "type": "string"
            }
          }
        }
        JSON_WRAP;

        return \json_decode($rawProductMappingSchema, true, 512, JSON_THROW_ON_ERROR);
    }
}
