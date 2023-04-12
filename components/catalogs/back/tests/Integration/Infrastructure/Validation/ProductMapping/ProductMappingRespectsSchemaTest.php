<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductMapping;

use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\ProductMappingRespectsSchemaValidator
 */
class ProductMappingRespectsSchemaTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->createUser('admin', ['IT support'], ['ROLE_ADMINISTRATOR']);

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'admin',
            productMappingSchema: $this->getValidSchemaData(),
        );

        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
            'scopable' => false,
            'localizable' => false,
        ]);
        $this->createAttribute([
            'code' => 'description',
            'type' => 'pim_catalog_text',
            'scopable' => false,
            'localizable' => false,
        ]);
        $this->createAttribute([
            'code' => 'release_date',
            'type' => 'pim_catalog_date',
            'scopable' => true,
            'localizable' => false,
        ]);
        $this->createAttribute([
            'code' => 'is_released',
            'type' => 'pim_catalog_boolean',
            'scopable' => false,
            'localizable' => false,
        ]);
        $this->createAttribute([
            'code' => 'height',
            'type' => 'pim_catalog_number',
            'scopable' => false,
            'localizable' => false,
        ]);
    }

    /**
     * @dataProvider validCasesProvider
     */
    public function testItValidates(array $productMapping): void
    {
        $violations = self::getContainer()->get(ValidatorInterface::class)->validate(
            new Catalog(
                'db1079b6-f397-4a6a-bae4-8658e64ad47c',
                'Store US',
                'willy',
                false,
                [],
                [],
                $productMapping,
            ),
        );

        $this->assertEquals(0, $violations->count());
    }

    public function validCasesProvider(): array
    {
        return [
            'Basic use case' => [
                'productMapping' => [
                    'uuid' => [
                        'source' => 'uuid',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'name' => [
                        'source' => 'name',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'simple_description' => [
                        'source' => 'description',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'released_at' => [
                        'source' => 'release_date',
                        'scope' => 'ecommerce',
                        'locale' => null,
                    ],
                    'released' => [
                        'source' => 'is_released',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'size' => [
                        'source' => 'height',
                        'scope' => null,
                        'locale' => null,
                    ],
                ],
            ],
            'A required target has no source but a default value' => [
                'productMapping' => [
                    'uuid' => [
                        'source' => 'uuid',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'name' => [
                        'source' => 'name',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'simple_description' => [
                        'source' => null,
                        'scope' => null,
                        'locale' => null,
                        'default' => 'Wonderful product made by Akeneo.',
                    ],
                    'released_at' => [
                        'source' => null,
                        'scope' => null,
                        'locale' => null,
                    ],
                    'released' => [
                        'source' => null,
                        'scope' => null,
                        'locale' => null,
                    ],
                    'size' => [
                        'source' => null,
                        'scope' => null,
                        'locale' => null,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidCasesProvider
     */
    public function testItDoesNotValidate(
        array $productMapping,
        string $errorMessage,
        int $errorCount,
    ): void {
        $violations = self::getContainer()->get(ValidatorInterface::class)->validate(
            new Catalog(
                'db1079b6-f397-4a6a-bae4-8658e64ad47c',
                'Store US',
                'willy',
                false,
                [],
                [],
                $productMapping,
            ),
        );

        $this->assertViolationsListContains($violations, $errorMessage);
        $this->assertEquals($errorCount, $violations->count());
    }

    public function invalidCasesProvider(): array
    {
        return [
            'Targets are missing' => [
                'productMapping' => [
                    'uuid' => [
                        'source' => 'uuid',
                        'scope' => null,
                        'locale' => null,
                    ],
                ],
                'errorMessage' => 'The mapping is incomplete, following targets are missing: "name", "simple_description", "released_at", "released", "size".',
                'errorCount' => 1,
            ],
            'Source does not match target type' => [
                'productMapping' => [
                    'uuid' => [
                        'source' => 'uuid',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'name' => [
                        'source' => 'name',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'simple_description' => [
                        'source' => 'description',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'released_at' => [
                        'source' => 'release_date',
                        'scope' => 'ecommerce',
                        'locale' => null,
                    ],
                    'released' => [
                        'source' => 'release_date',
                        'scope' => 'ecommerce',
                        'locale' => null,
                    ],
                    'size' => [
                        'source' => 'height',
                        'scope' => null,
                        'locale' => null,
                    ],
                ],
                'errorMessage' => 'The selected source type does not match the requirements: boolean expected.',
                'errorCount' => 1,
            ],
            'A required target has no source' => [
                'productMapping' => [
                    'uuid' => [
                        'source' => null,
                        'scope' => null,
                        'locale' => null,
                    ],
                    'name' => [
                        'source' => 'name',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'simple_description' => [
                        'source' => null,
                        'scope' => null,
                        'locale' => null,
                    ],
                    'released_at' => [
                        'source' => null,
                        'scope' => null,
                        'locale' => null,
                    ],
                    'size' => [
                        'source' => null,
                        'scope' => null,
                        'locale' => null,
                    ],
                    'released' => [
                        'source' => null,
                        'scope' => null,
                        'locale' => null,
                    ],
                ],
                'errorMessage' => 'The source is required.',
                'errorCount' => 1,
            ],
            'Extra target' => [
                'productMapping' => [
                    'uuid' => [
                        'source' => null,
                        'scope' => null,
                        'locale' => null,
                    ],
                    'name' => [
                        'source' => 'name',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'simple_description' => [
                        'source' => 'description',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'released_at' => [
                        'source' => null,
                        'scope' => null,
                        'locale' => null,
                    ],
                    'size' => [
                        'source' => null,
                        'scope' => null,
                        'locale' => null,
                    ],
                    'released' => [
                        'source' => null,
                        'scope' => null,
                        'locale' => null,
                    ],
                    'additional' => [
                        'source' => 'uuid',
                        'scope' => null,
                        'locale' => null,
                    ],
                ],
                'errorMessage' => 'The mapping is incorrect, following targets don\'t exist: "additional".',
                'errorCount' => 1,
            ],
            'Default value in the wrong type' => [
                'productMapping' => [
                    'uuid' => [
                        'source' => null,
                        'scope' => null,
                        'locale' => null,
                    ],
                    'name' => [
                        'source' => 'name',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'simple_description' => [
                        'source' => 'description',
                        'scope' => null,
                        'locale' => null,
                        'default' => true,
                    ],
                    'released_at' => [
                        'source' => null,
                        'scope' => null,
                        'locale' => null,
                    ],
                    'size' => [
                        'source' => null,
                        'scope' => null,
                        'locale' => null,
                    ],
                    'released' => [
                        'source' => null,
                        'scope' => null,
                        'locale' => null,
                    ],
                ],
                'errorMessage' => 'This value should be of type string.',
                'errorCount' => 1,
            ],
        ];
    }

    private function getValidSchemaData(): string
    {
        return <<<'JSON_WRAP'
        {
          "$id": "https://example.com/product",
          "$schema": "https://api.akeneo.com/mapping/product/0.0.11/schema",
          "$comment": "My first schema !",
          "title": "Product Mapping",
          "description": "JSON Schema describing the structure of products expected by our application",
          "type": "object",
          "properties": {
            "uuid": {
              "type": "string"
            },
            "name": {
              "type": "string"
            },
            "simple_description": {
              "type": "string"
            },
            "released_at": {
              "type": "string",
              "format": "date-time"
            },
            "released": {
              "type": "boolean"
            },
            "size": {
              "type": "number"
            }
          },
          "required": ["name","simple_description"]
        }
        JSON_WRAP;
    }
}
