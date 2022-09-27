<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation;

use Akeneo\Catalogs\Infrastructure\Validation\ProductSchema;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\ProductSchema
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\ProductSchemaValidator
 */
class ProductSchemaValidatorTest extends IntegrationTestCase
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
            new ProductSchema()
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
            new ProductSchema()
        );

        $this->assertNotEmpty($violations);
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
        ];
    }
}
