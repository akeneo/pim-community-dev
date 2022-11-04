<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Public;

use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Public\GetProductAction
 */
class GetProductActionTest extends IntegrationTestCase
{
    private ?KernelBrowser $client = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsProductByCatalogIdAndUuid(): void
    {
        $catalogId = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $this->client = $this->getAuthenticatedPublicApiClient(['read_catalogs', 'read_products']);
        $this->createCatalog($catalogId, 'Store US', 'shopifi');
        $this->enableCatalog($catalogId);

        $product = $this->createProduct('blue', [new SetEnabled(true)]);
        $this->createProduct('red', [new SetEnabled(true)]);
        $this->createProduct('green', [new SetEnabled(false)]);
        $productUuid = (string) $product->getUuid();

        $this->client->request(
            'GET',
            "/api/rest/v1/catalogs/$catalogId/products/$productUuid",
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();
        $result = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertEquals($productUuid, $result['uuid'], 'Not a valid UUID');
        Assert::assertTrue($result['enabled']);
    }

    public function testItReturnsForbiddenWhenMissingReadProductsPermissions(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient(['read_catalogs']);

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/products/c335c87e-ec23-4c5b-abfa-0638f141933a',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(403, $response->getStatusCode());
    }

    public function testItReturnsForbiddenWhenMissingReadCatalogsPermissions(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient(['read_products']);

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/products/c335c87e-ec23-4c5b-abfa-0638f141933a',
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
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/products/c335c87e-ec23-4c5b-abfa-0638f141933a',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(404, $response->getStatusCode());
    }

    public function testItReturnsNotFoundWhenProductDoesNotExistForTheCatalog(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient(['read_catalogs', 'read_products']);
        $catalogId = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $this->createCatalog($catalogId, 'Store US', 'shopifi');
        $this->enableCatalog($catalogId);

        $this->client->request(
            'GET',
            "/api/rest/v1/catalogs/$catalogId/products/c335c87e-ec23-4c5b-abfa-0638f141933a",
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(404, $response->getStatusCode());
    }

    public function testItReturnsAnErrorWhenCatalogIsDisabled(): void
    {
        $catalogId = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $this->client = $this->getAuthenticatedPublicApiClient(['read_catalogs', 'read_products']);
        $this->createCatalog($catalogId, 'Store US', 'shopifi');

        $product = $this->createProduct('blue', [new SetEnabled(true)]);
        $productUuid = (string) $product->getUuid();

        $this->client->request(
            'GET',
            "/api/rest/v1/catalogs/$catalogId/products/$productUuid",
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();
        $result = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertArrayHasKey('error', $result);
        Assert::assertIsString($result['error']);
    }

    public function testItReturnsAnErrorWhenCatalogIsInvalid(): void
    {
        $catalogId = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $this->client = $this->getAuthenticatedPublicApiClient(['read_catalogs', 'read_products']);
        $this->createCatalog($catalogId, 'Store US', 'shopifi');
        $this->enableCatalog($catalogId);

        $this->createAttribute([
            'code' => 'color',
            'type' => 'pim_catalog_multiselect',
            'options' => ['red', 'blue'],
        ]);
        $this->setCatalogProductSelection($catalogId, [
            [
                'field' => 'color',
                'operator' => Operator::IN_LIST,
                'value' => ['red'],
                'scope' => null,
                'locale' => null,
            ],
        ]);

        $product = $this->createProduct('blue', [
            new SetEnabled(true),
        ]);
        $productUuid = (string) $product->getUuid();

        $this->removeAttributeOption('color.red');

        $this->client->request(
            'GET',
            "/api/rest/v1/catalogs/$catalogId/products/$productUuid",
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();
        $result = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertArrayHasKey('error', $result);
        Assert::assertIsString($result['error']);
        Assert::assertFalse($this->getCatalog($catalogId)->isEnabled());
    }
}
