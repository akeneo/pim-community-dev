<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Internal\GetCatalogAction
 */
class GetCatalogActionTest extends IntegrationTestCase
{
    public ?object $tokenStorage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tokenStorage = self::getContainer()->get(TokenStorageInterface::class);

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsCatalog(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('admin');

        $this->createCatalog(
            id: 'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            name: 'Store FR',
            ownerUsername: 'admin',
            isEnabled: false,
            catalogProductValueFilters: [
                'channel' => ['print', 'ecommerce'],
            ],
        );

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
        $client = $this->getAuthenticatedInternalApiClient();
        $catalogProductMapping = [
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

        $this->createCatalog(
            id: 'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            name: 'Store FR',
            ownerUsername: 'admin',
            productMappingSchema: $this->getValidSchemaData(),
            catalogProductMapping: $catalogProductMapping,
        );

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
            \json_encode($catalogProductMapping, JSON_THROW_ON_ERROR),
            \json_encode($payload['product_mapping'], JSON_THROW_ON_ERROR),
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
