<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Public;

use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Public\GetProductsAction
 * @covers \Akeneo\Catalogs\Application\Handler\GetProductsHandler
 */
class GetProductsActionTest extends IntegrationTestCase
{
    private ?KernelBrowser $client = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->disableExperimentalTestDatabase();
        $this->purgeDataAndLoadMinimalCatalog();
        $this->createUser('admin', ['IT support'], ['ROLE_ADMINISTRATOR']);
    }

    public function testItGetsPaginatedProductsByCatalogId(): void
    {
        $this->logAs('admin'); // Creating products requires an authenticated user with higher permissions
        $this->createProduct(Uuid::fromString('8985de43-08bc-484d-aee0-4489a56ba02d'), [new SetEnabled(true)]);
        $this->createProduct(Uuid::fromString('00380587-3893-46e6-a8c2-8fee6404cc9e'), [new SetEnabled(true)]);

        $this->client = $this->getAuthenticatedPublicApiClient(['read_catalogs', 'read_products']);
        $this->createCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c', 'Store US', 'shopifi');
        $this->enableCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/products',
            [
                'limit' => 2,
            ],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertCount(2, $payload['_embedded']['items']);

        $uuids = \array_map(static fn (array $item): string => $item['uuid'], $payload['_embedded']['items']);
        Assert::assertEquals([
            '00380587-3893-46e6-a8c2-8fee6404cc9e',
            '8985de43-08bc-484d-aee0-4489a56ba02d'
        ], $uuids);
    }

    public function testItReturnsAnErrorMessagePayloadWhenTheCatalogIsDisabled(): void
    {
        $this->logAs('admin'); // Creating products requires an authenticated user with higher permissions
        $this->createProduct('blue');

        $this->client = $this->getAuthenticatedPublicApiClient(['read_catalogs', 'read_products']);
        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'shopifi',
            isEnabled: false,
        );

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/products',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $expectedMessage = 'No products to synchronize. The catalog db1079b6-f397-4a6a-bae4-8658e64ad47c has been ' .
            'disabled on the PIM side. Note that you can get catalogs status with the GET /api/rest/v1/catalogs endpoint.';

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertEquals($expectedMessage, $payload['error']);
    }

    public function testItReturnsBadRequestWhenPaginationIsInvalidGetProductsQueryInterfaced(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient(['read_catalogs', 'read_products']);
        $this->createCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c', 'Store US', 'shopifi');
        $this->enableCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/products',
            [
                'limit' => -1,
            ],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(422, $response->getStatusCode());
    }

    public function testItReturnsForbiddenWhenMissingPermissions(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient([]);

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/products',
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
        $this->client = $this->getAuthenticatedPublicApiClient(['read_catalogs', 'read_products']);

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/products',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(404, $response->getStatusCode());
    }

    public function testItReturnsAnErrorMessagePayloadWhenTheCatalogIsEnabledAndInvalid(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient(['read_catalogs', 'read_products']);
        $this->createAttribute([
            'code' => 'color',
            'type' => 'pim_catalog_multiselect',
            'options' => ['red', 'blue'],
        ]);
        $catalogIdUS = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $this->createCatalog($catalogIdUS, 'Store US', 'shopifi');
        $this->enableCatalog($catalogIdUS);
        $this->setCatalogProductSelection($catalogIdUS, [
            [
                'field' => 'color',
                'operator' => Operator::IN_LIST,
                'value' => ['red'],
                'scope' => null,
                'locale' => null,
            ],
        ]);
        $this->removeAttributeOption('color.red');

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/products',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $expectedMessage = 'No products to synchronize. The catalog db1079b6-f397-4a6a-bae4-8658e64ad47c has been ' .
            'disabled on the PIM side. Note that you can get catalogs status with the GET /api/rest/v1/catalogs endpoint.';

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertEquals($expectedMessage, $payload['error']);
        Assert::assertEquals(false, $this->getCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c')->isEnabled());
    }
}
