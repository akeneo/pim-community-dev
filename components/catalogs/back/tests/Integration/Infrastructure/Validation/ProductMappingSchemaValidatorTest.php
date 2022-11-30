<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation;

use Akeneo\Catalogs\Infrastructure\Validation\ProductMappingSchema;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\ProductMappingSchema
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\ProductMappingSchemaValidator
 */
class ProductMappingSchemaValidatorTest extends IntegrationTestCase
{
    private ?ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = self::getContainer()->get(ValidatorInterface::class);
    }

    /**
     * @dataProvider validSchemaDataProvider
     */
    public function testItAcceptsTheSchema(string $schema): void
    {
        $violations = $this->validator->validate(
            \json_decode($schema, false, 512, JSON_THROW_ON_ERROR),
            new ProductMappingSchema()
        );

        $this->assertEmpty($violations);
    }

    /**
     * @dataProvider invalidSchemaDataProvider
     */
    public function testItRejectsTheSchema(string $schema): void
    {
        $violations = $this->validator->validate(
            \json_decode($schema, false, 512, JSON_THROW_ON_ERROR),
            new ProductMappingSchema()
        );

        $this->assertCount(1, $violations);
        $this->assertEquals('You must provide a valid schema.', $violations->get(0)->getMessage());
    }

    public function validSchemaDataProvider(): array
    {
        return [
            '0.0.1 with valid schema' => [
                'schema' => <<<'JSON_WRAP'
{
  "$id": "https://example.com/product",
  "$schema": "https://api.akeneo.com/mapping/product/0.0.1/schema",
  "$comment": "My first schema !",
  "title": "Product Mapping",
  "description": "JSON Schema describing the structure of products expected by our application",
  "type": "object",
  "properties": {
    "name": {
      "type": "string"
    },
    "body_html": {
      "title": "Description",
      "description": "Product description in raw HTML",
      "type": "string"
    }
  }
}
JSON_WRAP,
            ],
            '0.0.2 with valid schema with uuid' => [
                'schema' => <<<'JSON_WRAP'
{
  "$id": "https://example.com/product",
  "$schema": "https://api.akeneo.com/mapping/product/0.0.2/schema",
  "$comment": "My first schema !",
  "title": "Product Mapping",
  "description": "JSON Schema describing the structure of products expected by our application",
  "type": "object",
  "properties": {
    "uuid": {
      "title": "Product uuid",
      "type": "string"
    },
    "name": {
      "type": "string"
    },
    "body_html": {
      "title": "Description",
      "description": "Product description in raw HTML",
      "type": "string"
    }
  }
}
JSON_WRAP,
            ],
        ];
    }

    public function invalidSchemaDataProvider(): array
    {
        return [
            '0.0.1 with invalid type number' => [
                'schema' => <<<'JSON_WRAP'
{
  "$schema": "https://api.akeneo.com/mapping/product/0.0.1/schema",
  "properties": {
    "price": {
      "type": "number"
    }
  }
}
JSON_WRAP,
            ],
            '0.0.1 with missing target type' => [
                'schema' => <<<'JSON_WRAP'
{
  "$schema": "https://api.akeneo.com/mapping/product/0.0.1/schema",
  "properties": {
    "price": {}
  }
}
JSON_WRAP,
            ],
            '0.0.2 with missing uuid' => [
                'schema' => <<<'JSON_WRAP'
{
  "$schema": "https://api.akeneo.com/mapping/product/0.0.2/schema",
  "properties": {
    "name": {
      "type": "string"
    }
  }
}
JSON_WRAP,
            ],
            '0.0.2 with invalid uuid type' => [
                'schema' => <<<'JSON_WRAP'
{
  "$schema": "https://api.akeneo.com/mapping/product/0.0.2/schema",
  "properties": {
    "uuid": {
      "type": "boolean"
    }
  }
}
JSON_WRAP,
            ],
            '0.0.2 with invalid uuid extra fields' => [
                'schema' => <<<'JSON_WRAP'
{
  "$schema": "https://api.akeneo.com/mapping/product/0.0.2/schema",
  "properties": {
    "uuid": {
      "type": "boolean",
      "title": "Description"
    }
  }
}
JSON_WRAP,
            ],
        ];
    }
}
