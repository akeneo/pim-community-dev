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
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Public\GetProductAction
 */
class GetProductActionTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->disableExperimentalTestDatabase();
        $this->purgeDataAndLoadMinimalCatalog();
    }

    /**
     * @group leak
     */
    public function testItGetsProductByCatalogIdAndUuid(): void
    {
        $productCountFromEvent = 0;
        $this->addSubscriberForReadProductEvent(function ($productCount) use (&$productCountFromEvent): void {
            $productCountFromEvent = $productCount;
        });

        $client = $this->getAuthenticatedPublicApiClient(['read_catalogs', 'read_products']);
        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'shopifi',
        );

        $product = $this->createProduct('blue', [new SetEnabled(true)]);
        $this->createProduct('red', [new SetEnabled(true)]);
        $this->createProduct('green', [new SetEnabled(false)]);
        $productUuid = (string) $product->getUuid();

        $client->request(
            'GET',
            "/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/products/$productUuid",
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $client->getResponse();
        $result = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertEquals($productUuid, $result['uuid'], 'Not a valid UUID');
        Assert::assertTrue($result['enabled']);
        Assert::assertEquals(1, $productCountFromEvent, 'Wrong dispatched product count');
    }

    public function testItReturnsForbiddenWhenMissingReadProductsPermissions(): void
    {
        $client = $this->getAuthenticatedPublicApiClient(['read_catalogs']);

        $client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/products/c335c87e-ec23-4c5b-abfa-0638f141933a',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $client->getResponse();

        Assert::assertEquals(403, $response->getStatusCode());
    }

    public function testItReturnsForbiddenWhenMissingReadCatalogsPermissions(): void
    {
        $client = $this->getAuthenticatedPublicApiClient(['read_products']);

        $client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/products/c335c87e-ec23-4c5b-abfa-0638f141933a',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $client->getResponse();

        Assert::assertEquals(403, $response->getStatusCode());
    }

    public function testItReturnsNotFoundWhenCatalogDoesNotExist(): void
    {
        $client = $this->getAuthenticatedPublicApiClient(['read_catalogs', 'read_products']);

        $client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/products/c335c87e-ec23-4c5b-abfa-0638f141933a',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $expectedMessage = 'Either catalog "db1079b6-f397-4a6a-bae4-8658e64ad47c" does not exist or you can\'t access'.
            ' it, or product "c335c87e-ec23-4c5b-abfa-0638f141933a" does not exist or you do not have permission to access it.';

        Assert::assertEquals(404, $response->getStatusCode());
        Assert::assertEquals($expectedMessage, $payload['message']);
    }

    public function testItReturnsNotFoundWhenProductDoesNotExistForTheCatalog(): void
    {
        $client = $this->getAuthenticatedPublicApiClient(['read_catalogs', 'read_products']);
        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'shopifi',
        );

        $client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/products/c335c87e-ec23-4c5b-abfa-0638f141933a',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $expectedMessage = 'Either catalog "db1079b6-f397-4a6a-bae4-8658e64ad47c" does not exist or you can\'t access'.
            ' it, or product "c335c87e-ec23-4c5b-abfa-0638f141933a" does not exist or you do not have permission to access it.';

        Assert::assertEquals(404, $response->getStatusCode());
        Assert::assertEquals($expectedMessage, $payload['message']);
    }

    public function testItReturnsNotFoundWhenTheProductIsFilteredOut(): void
    {
        $client = $this->getAuthenticatedPublicApiClient([
            'read_catalogs',
            'read_products',
        ]);

        $this->createProduct(Uuid::fromString('c335c87e-ec23-4c5b-abfa-0638f141933a'), [
            new SetEnabled(false),
        ]);

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'shopifi',
            catalogProductSelection: [
                [
                    'field' => 'enabled',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ],
        );

        $client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/products/c335c87e-ec23-4c5b-abfa-0638f141933a',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $expectedMessage = 'Either catalog "db1079b6-f397-4a6a-bae4-8658e64ad47c" does not exist or you can\'t access'.
            ' it, or product "c335c87e-ec23-4c5b-abfa-0638f141933a" does not exist or you do not have permission to access it.';

        Assert::assertEquals(404, $response->getStatusCode());
        Assert::assertEquals($expectedMessage, $payload['message']);
    }


    public function testItReturnsAnErrorWhenCatalogIsDisabled(): void
    {
        $client = $this->getAuthenticatedPublicApiClient(['read_catalogs', 'read_products']);
        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'shopifi',
            isEnabled: false,
        );

        $product = $this->createProduct('blue', [new SetEnabled(true)]);
        $productUuid = (string) $product->getUuid();

        $client->request(
            'GET',
            "/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/products/$productUuid",
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $client->getResponse();
        $result = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertArrayHasKey('error', $result);
        Assert::assertIsString($result['error']);
    }

    public function testItReturnsAnErrorWhenCatalogIsInvalid(): void
    {
        $this->createAttribute([
            'code' => 'color',
            'type' => 'pim_catalog_multiselect',
            'options' => ['red', 'blue'],
        ]);

        $client = $this->getAuthenticatedPublicApiClient(['read_catalogs', 'read_products']);

        $product = $this->createProduct('blue', [new SetEnabled(true)]);
        $productUuid = (string) $product->getUuid();

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'shopifi',
            catalogProductSelection: [
                [
                    'field' => 'color',
                    'operator' => Operator::IN_LIST,
                    'value' => ['red'],
                    'scope' => null,
                    'locale' => null,
                ],
            ],
        );

        $this->removeAttributeOption('color.red');

        $client->request(
            'GET',
            "/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/products/$productUuid",
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $client->getResponse();
        $result = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertArrayHasKey('error', $result);
        Assert::assertIsString($result['error']);
        Assert::assertFalse($this->getCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c')->isEnabled());
    }
}
