<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Command\UpdateProductMappingSchemaCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Internal\GetProductMappingSchemaAction
 */
class GetCatalogActionTest extends IntegrationTestCase
{
    private ?CommandBus $commandBus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandBus = self::getContainer()->get(CommandBus::class);

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsCatalog(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('admin');

        $this->commandBus->execute(new CreateCatalogCommand(
            'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            'Store FR',
            'admin',
        ));
        $this->setCatalogProductValueFilters('ed30425c-d9cf-468b-8bc7-fa346f41dd07', [
            'channel' => ['print', 'ecommerce'],
        ]);

        $client->request(
            'GET',
            '/rest/catalogs/ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(200, $response->getStatusCode());

        Assert::assertSame('ed30425c-d9cf-468b-8bc7-fa346f41dd07', $payload['id']);
        Assert::assertSame('Store FR', $payload['name']);
        Assert::assertSame(false, $payload['enabled']);
        Assert::assertSame('admin', $payload['owner_username']);
        Assert::assertSame([
            [
                'field' => 'enabled',
                'value' => true,
                'operator' => '=',
            ],
        ], $payload['product_selection_criteria']);
        Assert::assertSame([
            'channel' => ['print', 'ecommerce'],
        ], $payload['product_value_filters']);
        Assert::assertSame([], $payload['product_mapping']);
        Assert::assertFalse($payload['has_product_mapping_schema']);
    }

    public function testItGetsCatalogWithProductMapping(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('admin');

        $this->commandBus->execute(new CreateCatalogCommand(
            'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            'Store FR',
            'admin',
        ));
        $this->commandBus->execute(new UpdateProductMappingSchemaCommand(
            'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            \json_decode($this->getValidSchemaData(), false, 512, JSON_THROW_ON_ERROR),
        ));

        $productMapping = [
            'uuid' => [
                'source' => 'uuid',
                'scope' => null,
                'locale' => null,
            ],
            'name' => [
                'source' => 'title',
                'scope' => 'ecommerce',
                'locale' => 'en_US',
            ],
        ];
        $this->setCatalogProductMapping('ed30425c-d9cf-468b-8bc7-fa346f41dd07', $productMapping);

        $client->request(
            'GET',
            '/rest/catalogs/ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(200, $response->getStatusCode());

        Assert::assertJsonStringEqualsJsonString(
            \json_encode($productMapping, JSON_THROW_ON_ERROR),
            \json_encode($payload['product_mapping'], JSON_THROW_ON_ERROR)
        );
        Assert::assertTrue($payload['has_product_mapping_schema']);
    }

    public function testItGetsNotFoundResponseWithWrongId(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('admin');

        $client->request(
            'GET',
            '/rest/catalogs/ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );
        $response = $client->getResponse();

        Assert::assertEquals(404, $response->getStatusCode());
    }

    private function getValidSchemaData(): string
    {
        return <<<'JSON_WRAP'
        {
          "$id": "https://example.com/product",
          "$schema": "https://api.akeneo.com/mapping/product/0.0.1/schema",
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
