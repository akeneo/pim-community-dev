<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Command\UpdateProductMappingSchemaCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Internal\GetTargetsAction
 */
class GetTargetsActionTest extends IntegrationTestCase
{
    private ?CommandBus $commandBus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandBus = self::getContainer()->get(CommandBus::class);

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsTargets(): void
    {
        $client = $this->getAuthenticatedInternalApiClient();

        $this->commandBus->execute(new CreateCatalogCommand(
            'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            'Store FR',
            'admin',
        ));

        $this->commandBus->execute(new UpdateProductMappingSchemaCommand(
            'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            \json_decode($this->getValidSchemaData(), false, 512, JSON_THROW_ON_ERROR),
        ));

        $client->request(
            'GET',
            '/rest/catalogs/targets/ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        Assert::assertEquals(200, $response->getStatusCode());
        $targets = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $expectedTargets = [
            ['code' => 'name', 'label' => 'name'],
            ['code' => 'body_html', 'label' => 'Description'],
        ];
        Assert::assertEquals($expectedTargets, $targets);
    }

    public function testItGetsAnEmptyListOfTargets(): void
    {
        $client = $this->getAuthenticatedInternalApiClient();

        $this->commandBus->execute(new CreateCatalogCommand(
            'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            'Store FR',
            'admin',
        ));

        $this->commandBus->execute(new UpdateProductMappingSchemaCommand(
            'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            \json_decode($this->getValidSchemaDataWithEmptyProperties(), false, 512, JSON_THROW_ON_ERROR),
        ));

        $client->request(
            'GET',
            '/rest/catalogs/targets/ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        Assert::assertEquals(200, $response->getStatusCode());
        $targets = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $expectedTargets = [];
        Assert::assertEquals($expectedTargets, $targets);
    }

    public function testItReturnsANoContentResponseWhenThereIsNoProductMappingSchemaInAGivenCatalog(): void
    {
        $client = $this->getAuthenticatedInternalApiClient();

        $this->commandBus->execute(new CreateCatalogCommand(
            'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            'Store FR',
            'admin',
        ));

        $client->request(
            'GET',
            '/rest/catalogs/targets/ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        Assert::assertEquals(204, $response->getStatusCode());
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
        JSON_WRAP;
    }

    private function getValidSchemaDataWithEmptyProperties(): string
    {
        return <<<'JSON_WRAP'
        {
          "$id": "https://example.com/product",
          "$schema": "https://api.akeneo.com/mapping/product/0.0.1/schema",
          "$comment": "My first schema !",
          "title": "Product Mapping",
          "description": "JSON Schema describing the structure of products expected by our application",
          "type": "object",
          "properties": {}
        }
        JSON_WRAP;
    }
}
