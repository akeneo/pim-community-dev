<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation;

use Akeneo\Catalogs\Infrastructure\Validation\CatalogUpdatePayload;
use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Command\UpdateProductMappingSchemaCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\CatalogUpdatePayloadValidator
 */
class CatalogUpdatePayloadTest extends IntegrationTestCase
{
    private ?ValidatorInterface $validator;
    private ?CommandBus $commandBus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = self::getContainer()->get(ValidatorInterface::class);
        $this->commandBus = self::getContainer()->get(CommandBus::class);

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItValidatesWithoutMapping(): void
    {
        $violations = $this->validator->validate([
            'enabled' => false,
            'product_selection_criteria' => [
                [
                    'field' => 'enabled',
                    'operator' => '=',
                    'value' => true,
                ],
            ],
            'product_value_filters' => [
                'channels' => ['ecommerce'],
                'locales' => ['en_US'],
                'currencies' => ['EUR', 'USD'],
            ],
            'product_mapping' => [],
        ], new CatalogUpdatePayload());

        $this->assertEmpty($violations);
    }

    public function testItValidatesWithMapping(): void
    {
        $this->createUser('admin', ['IT support'], ['ROLE_ADMINISTRATOR']);
        $this->commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            'admin',
        ));
        $this->commandBus->execute(new UpdateProductMappingSchemaCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            \json_decode($this->getValidSchemaData(), false, 512, JSON_THROW_ON_ERROR),
        ));

        $violations = $this->validator->validate([
            'enabled' => false,
            'product_selection_criteria' => [
                [
                    'field' => 'enabled',
                    'operator' => '=',
                    'value' => true,
                ],
            ],
            'product_value_filters' => [],
            'product_mapping' => [
                'uuid' => [
                    'source' => 'uuid',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
        ], new CatalogUpdatePayload('db1079b6-f397-4a6a-bae4-8658e64ad47c_product.json'));

        $this->assertEmpty($violations);
    }

    public function testItReturnsViolationsWithMissingValues(): void
    {
        $violations = $this->validator->validate([], new CatalogUpdatePayload('db1079b6-f397-4a6a-bae4-8658e64ad47c_product.json'));

        $this->assertViolationsListContains($violations, 'This field is missing.');
    }

    private function getValidSchemaData(): string
    {
        return <<<'JSON_WRAP'
        {
          "$id": "https://example.com/product",
          "$schema": "https://api.akeneo.com/mapping/product/0.0.2/schema",
          "$comment": "My first schema !",
          "title": "Product Mapping",
          "description": "JSON Schema describing the structure of products expected by our application",
          "type": "object",
          "properties": {
            "uuid": {
              "type": "string"
            }
          }
        }
        JSON_WRAP;
    }
}
