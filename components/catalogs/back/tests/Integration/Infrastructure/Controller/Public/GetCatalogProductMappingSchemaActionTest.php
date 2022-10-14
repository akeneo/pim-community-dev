<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Public;

use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Command\UpdateCatalogProductMappingSchemaCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Public\GetCatalogProductMappingSchemaAction
 * @covers \Akeneo\Catalogs\Application\Handler\GetCatalogProductMappingSchemaHandler
 */
class GetCatalogProductMappingSchemaActionTest extends IntegrationTestCase
{
    private ?KernelBrowser $client = null;
    private ?CommandBus $commandBus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandBus = self::getContainer()->get(CommandBus::class);

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsTheCatalogProductMappingSchema(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient([
            'read_catalogs',
        ]);
        $this->commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            'shopifi',
        ));
        $this->commandBus->execute(new UpdateCatalogProductMappingSchemaCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            \json_decode($this->getValidSchemaData(), false, 512, JSON_THROW_ON_ERROR),
        ));

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();
        $payload = $response->getContent();

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertJsonStringEqualsJsonString($this->getValidSchemaData(), $payload);
    }

    public function testItReturnsForbiddenWhenMissingPermissions(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient([]);
        $this->commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            'shopifi',
        ));
        $this->commandBus->execute(new UpdateCatalogProductMappingSchemaCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            \json_decode($this->getValidSchemaData(), false, 512, JSON_THROW_ON_ERROR),
        ));

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(403, $response->getStatusCode());
    }

    public function testItReturnsNotFoundWhenCatalogDoesNotExist(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient([
            'read_catalogs',
        ]);

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $this->getValidSchemaData(),
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(404, $response->getStatusCode());
    }

    public function testItReturnsNotFoundWhenCatalogDoesNotBelongToCurrentUser(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient([
            'read_catalogs',
        ]);
        $this->createUser('magendo');
        $this->commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            'magendo',
        ));

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping-schemas/product',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();

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
}
