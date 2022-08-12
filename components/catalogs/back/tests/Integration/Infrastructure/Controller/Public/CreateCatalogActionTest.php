<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Public;

use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Public\CreateCatalogAction
 * @covers \Akeneo\Catalogs\Application\Handler\CreateCatalogHandler
 */
class CreateCatalogActionTest extends IntegrationTestCase
{
    private ?KernelBrowser $client = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItCreatesTheCatalog(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient(['write_catalogs']);

        $this->client->request(
            'POST',
            '/api/rest/v1/catalogs',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            \json_encode([
                'name' => 'Store US',
            ]),
        );

        $response = $this->client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(201, $response->getStatusCode());
        Assert::assertArrayHasKey('id', $payload);
        Assert::assertSame('Store US', $payload['name']);
        Assert::assertSame(false, $payload['enabled']);
    }

    public function testItReturnsUnprocessableEntityWhenInvalid(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient(['write_catalogs']);

        $this->client->request(
            'POST',
            '/api/rest/v1/catalogs',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            \json_encode([
                'name' => '', // empty
            ]),
        );

        $response = $this->client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(422, $response->getStatusCode());
        Assert::assertArrayHasKey('message', $payload);
        Assert::assertArrayHasKey('errors', $payload);
    }

    public function testItReturnsForbiddenWhenMissingPermissions(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient([]);

        $this->client->request(
            'POST',
            '/api/rest/v1/catalogs',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            \json_encode([
                'name' => 'Store US',
            ]),
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(403, $response->getStatusCode());
    }
}
