<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Internal\UpdateCatalogAction
 */
class UpdateCatalogActionTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItUpdatesTheCatalog(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('shopifi');

        $this->createCatalog(
            id: 'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            name: 'Store US',
            ownerUsername: 'shopifi',
            productMappingSchema: $this->getValidSchemaData(),
        );

        $client->request(
            'PATCH',
            '/rest/catalogs/ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            \json_encode([
                'enabled' => true,
                'product_selection_criteria' => [
                    [
                        'field' => 'enabled',
                        'operator' => '!=',
                        'value' => true,
                    ],
                ],
                'product_value_filters' => [
                    'channels' => ['ecommerce'],
                ],
                'product_mapping' => [
                    'uuid' => [
                        'source' => 'uuid',
                        'scope' => null,
                        'locale' => null,
                    ],
                ],
            ]),
        );

        $response = $client->getResponse();

        Assert::assertEquals(204, $response->getStatusCode());

        $this->assertCatalogIsEnabled('ed30425c-d9cf-468b-8bc7-fa346f41dd07');
        $this->assertCatalogHasProductSelectionCriteria('ed30425c-d9cf-468b-8bc7-fa346f41dd07', [
            [
                'field' => 'enabled',
                'operator' => '!=',
                'value' => true,
            ],
        ]);
        $this->assertCatalogHasProductValueFilters('ed30425c-d9cf-468b-8bc7-fa346f41dd07', [
            'channels' => ['ecommerce'],
        ]);
        $this->assertCatalogHasProductMapping('ed30425c-d9cf-468b-8bc7-fa346f41dd07', [
            'uuid' => [
                'source' => 'uuid',
                'scope' => null,
                'locale' => null,
            ],
        ]);
    }

    public function testItGetsNotFoundResponseWithWrongId(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('shopifi');

        $client->request(
            'PATCH',
            '/rest/catalogs/ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            \json_encode([
                'enabled' => true,
                'product_selection_criteria' => [
                    [
                        'field' => 'enabled',
                        'operator' => '!=',
                        'value' => true,
                    ],
                ],
                'product_value_filters' => [
                    'channels' => ['ecommerce'],
                ],
                'product_mapping' => [
                    'uuid' => [
                        'source' => 'uuid',
                        'scope' => null,
                        'locale' => null,
                    ],
                ],
            ]),
        );
        $response = $client->getResponse();

        Assert::assertEquals(404, $response->getStatusCode());
    }

    public function testItGetsUnprocessableEntityWithWrongCriteria(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('shopifi');

        $this->createCatalog(
            id: 'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            name: 'Store US',
            ownerUsername: 'shopifi',
            productMappingSchema: $this->getValidSchemaData(),
        );

        $client->request(
            'PATCH',
            '/rest/catalogs/ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            \json_encode([
                'enabled' => true,
                'product_selection_criteria' => [
                    [
                        'field' => 'wrong-criteria',
                    ],
                ],
                'product_value_filters' => [
                    'channels' => ['ecommerce'],
                ],
                'product_mapping' => [
                    'uuid' => [
                        'source' => 'uuid',
                        'scope' => null,
                        'locale' => null,
                    ],
                ],
            ]),
        );

        $response = $client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(422, $response->getStatusCode());
        Assert::assertArrayHasKey('message', $payload);
        Assert::assertArrayHasKey('errors', $payload);
        Assert::assertCount(1, $payload['errors']);
    }

    public function testItGetsUnprocessableEntityWithWrongTargetSourceAssociation(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('shopifi');

        $this->createCatalog(
            id: 'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            name: 'Store US',
            ownerUsername: 'shopifi',
            productMappingSchema: $this->getValidSchemaData(),
        );

        $client->request(
            'PATCH',
            '/rest/catalogs/ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            \json_encode([
                'enabled' => true,
                'product_selection_criteria' => [
                    [
                        'field' => 'enabled',
                        'operator' => '!=',
                        'value' => true,
                    ],
                ],
                'product_value_filters' => [
                    'channels' => ['ecommerce'],
                ],
                'product_mapping' => [
                    'uuid' => [
                        'source' => 'uuid',
                        'scope' => 'ecommerce',
                        'locale' => null,
                    ],
                ],
            ]),
        );

        $response = $client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(422, $response->getStatusCode());
        Assert::assertArrayHasKey('message', $payload);
        Assert::assertArrayHasKey('errors', $payload);
        Assert::assertCount(1, $payload['errors']);
    }

    private function assertCatalogIsEnabled(string $id): void
    {
        $query = <<<SQL
        SELECT catalog.is_enabled
        FROM akeneo_catalog catalog
        WHERE id = :id
        SQL;

        $row = (bool) self::getContainer()->get(Connection::class)->executeQuery($query, [
            'id' => Uuid::fromString($id)->getBytes(),
        ])->fetchOne();

        $this->assertEquals(true, $row);
    }

    private function assertCatalogHasProductSelectionCriteria(string $id, array $expected): void
    {
        $query = <<<SQL
        SELECT catalog.product_selection_criteria
        FROM akeneo_catalog catalog
        WHERE id = :id
        SQL;

        $row = self::getContainer()->get(Connection::class)->executeQuery($query, [
            'id' => Uuid::fromString($id)->getBytes(),
        ])->fetchOne();

        $this->assertEquals($expected, \json_decode($row, true, 512, JSON_THROW_ON_ERROR));
    }

    private function assertCatalogHasProductValueFilters(string $id, array $expected): void
    {
        $query = <<<SQL
        SELECT catalog.product_value_filters
        FROM akeneo_catalog catalog
        WHERE id = :id
        SQL;

        $row = self::getContainer()->get(Connection::class)->executeQuery($query, [
            'id' => Uuid::fromString($id)->getBytes(),
        ])->fetchOne();

        $this->assertEquals($expected, \json_decode($row, true, 512, JSON_THROW_ON_ERROR));
    }

    private function assertCatalogHasProductMapping(string $id, array $expected): void
    {
        $query = <<<SQL
        SELECT catalog.product_mapping
        FROM akeneo_catalog catalog
        WHERE id = :id
        SQL;

        $row = self::getContainer()->get(Connection::class)->executeQuery($query, [
            'id' => Uuid::fromString($id)->getBytes(),
        ])->fetchOne();

        $this->assertEquals($expected, \json_decode($row, true, 512, JSON_THROW_ON_ERROR));
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
