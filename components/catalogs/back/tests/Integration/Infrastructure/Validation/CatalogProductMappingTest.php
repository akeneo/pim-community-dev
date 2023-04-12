<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation;

use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\CatalogProductMappingValidator
 */
class CatalogProductMappingTest extends IntegrationTestCase
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
    }

    public function testItValidates(): void
    {
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
            'scopable' => false,
            'localizable' => false,
        ]);

        $violations = self::getContainer()->get(ValidatorInterface::class)->validate(
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
                ],
            ),
        );

        $this->assertEquals(0, $violations->count());
    }

    public function testItReturnsViolationsWhenProductMappingIsNotAssociativeArray(): void
    {
        $violations = self::getContainer()->get(ValidatorInterface::class)->validate(
            new Catalog(
                'db1079b6-f397-4a6a-bae4-8658e64ad47c',
                'Store US',
                'willy',
                false,
                [],
                [],
                // @phpstan-ignore-next-line
                [
                    [
                        'source' => 'uuid',
                        'scope' => null,
                        'locale' => null,
                    ],
                ],
            ),
        );

        $this->assertViolationsListContains($violations, 'Invalid array structure.');
    }

    public function testItReturnsViolationsWhenSourceIsInvalid(): void
    {
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
            'scopable' => false,
            'localizable' => false,
        ]);

        $violations = self::getContainer()->get(ValidatorInterface::class)->validate(
            new Catalog(
                'db1079b6-f397-4a6a-bae4-8658e64ad47c',
                'Store US',
                'willy',
                false,
                [],
                [],
                [
                    'uuid' => [
                        'source' => 'unknown_attribute',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'name' => [
                        'source' => 'name',
                        'scope' => null,
                        'locale' => null,
                    ],
                ],
            ),
        );

        $this->assertViolationsListContains($violations, 'This attribute has been deleted.');
    }

    public function testItReturnsViolationsWhenSourceTypeIsIncorrect(): void
    {
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_boolean',
            'scopable' => false,
            'localizable' => false,
        ]);

        $violations = self::getContainer()->get(ValidatorInterface::class)->validate(
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
                ],
            ),
        );

        $this->assertViolationsListContains($violations, 'The selected source type does not match the requirements: string expected.');
    }

    private function getValidSchemaData(): string
    {
        return <<<'JSON_WRAP'
        {
          "$id": "https://example.com/product",
          "$schema": "https://api.akeneo.com/mapping/product/0.0.6/schema",
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
            }
          }
        }
        JSON_WRAP;
    }
}
