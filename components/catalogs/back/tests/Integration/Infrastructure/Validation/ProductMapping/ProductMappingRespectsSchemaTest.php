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
    private ?ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = self::getContainer()->get(ValidatorInterface::class);

        $this->purgeDataAndLoadMinimalCatalog();

        $this->createUser('admin', ['IT support'], ['ROLE_ADMINISTRATOR']);
        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'admin',
            productMappingSchema: $this->getValidSchemaData(),
        );
    }

    public function testItValidates(): void
    {
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

        $violations = $this->validator->validate(
            new Catalog(
                'db1079b6-f397-4a6a-bae4-8658e64ad47c',
                'Store US',
                'willy',
                false,
                [],
                [],
                [
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
            ),
        );

        $this->assertEquals(0, $violations->count());
    }

    public function testItReturnsViolationsWhenTargetsAreMissing(): void
    {
        $violations = $this->validator->validate(
            new Catalog(
                'db1079b6-f397-4a6a-bae4-8658e64ad47c',
                'Store US',
                'willy',
                false,
                [],
                [],
                [
                    'uuid' => [
                        'source' => 'uuid',
                        'scope' => null,
                        'locale' => null,
                    ],
                ],
            ),
        );

        $this->assertViolationsListContains($violations, 'The mapping is incomplete, following targets are missing: "name", "simple_description", "released_at", "released", "size".');
    }

    public function testItReturnsViolationsWhenThereIsAdditionalTarget(): void
    {
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

        $violations = $this->validator->validate(
            new Catalog(
                'db1079b6-f397-4a6a-bae4-8658e64ad47c',
                'Store US',
                'willy',
                false,
                [],
                [],
                [
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
                        'source' => null,
                        'scope' => true,
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
                    'additional' => [
                        'source' => 'uuid',
                        'scope' => null,
                        'locale' => null,
                    ],
                ],
            ),
        );

        $this->assertViolationsListContains($violations, 'The mapping is incorrect, following targets don\'t exist: "additional".');
    }

    public function testItReturnsViolationsWhenThereIsRequiredTargetWithNoSourceDefined(): void
    {
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
            'scopable' => false,
            'localizable' => false,
        ]);

        $this->createCatalog(
            id: '3e073da1-29f8-4bf3-9adf-99c51b0c5348',
            name: 'Store FR',
            ownerUsername: 'admin',
            productMappingSchema: $this->getValidSchemaDataWithRequiredField(),
        );

        $violations = $this->validator->validate(
            new Catalog(
                '3e073da1-29f8-4bf3-9adf-99c51b0c5348',
                'Store FR',
                'admin',
                false,
                [],
                [],
                [
                    'uuid' => [
                        'source' => 'uuid',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'name' => [
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
            ),
        );

        $this->assertViolationsListContains($violations, 'The source is required.');
    }
    public function testItValidatesWhenThereIsRequiredTargetWithNoSourceDefinedAndDefaultValue(): void
    {
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
            'scopable' => false,
            'localizable' => false,
        ]);

        $this->createCatalog(
            id: '3e073da1-29f8-4bf3-9adf-99c51b0c5348',
            name: 'Store FR',
            ownerUsername: 'admin',
            productMappingSchema: $this->getValidSchemaDataWithRequiredField(),
        );

        $violations = $this->validator->validate(
            new Catalog(
                '3e073da1-29f8-4bf3-9adf-99c51b0c5348',
                'Store FR',
                'admin',
                false,
                [],
                [],
                [
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
                    'size' => [
                        'source' => null,
                        'scope' => null,
                        'locale' => null,
                        'default' => 175,
                    ],
                ],
            ),
        );

        $this->assertEquals(0, $violations->count());
    }

    public function testItReturnsViolationsWhenThereIsErrorInNullSources(): void
    {
        $this->createCatalog(
            id: '3e073da1-29f8-4bf3-9adf-99c51b0c5348',
            name: 'Store FR',
            ownerUsername: 'admin',
            productMappingSchema: $this->getValidSchemaDataForNullSources(),
        );

        $violations = $this->validator->validate(
            new Catalog(
                '3e073da1-29f8-4bf3-9adf-99c51b0c5348',
                'Store FR',
                'admin',
                false,
                [],
                [],
                [
                    'uuid' => [
                        'source' => 'uuid',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'name' => [
                        'source' => null,
                        'scope' => null,
                        'locale' => null,
                        'default' => true,
                    ],
                    'released' => [
                        'source' => null,
                        'scope' => null,
                        'locale' => null,
                        'default' => 10,
                    ],
                ],
            ),
        );

        $this->assertEquals(2, $violations->count());
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
          }
        }
        JSON_WRAP;
    }

    private function getValidSchemaDataWithRequiredField(): string
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
            "size": {
              "type": "number"
            }
          },
          "required": ["name","size"]
        }
        JSON_WRAP;
    }

    private function getValidSchemaDataForNullSources(): string
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
            "released": {
              "type": "boolean"
            }
          }
        }
        JSON_WRAP;
    }
}
