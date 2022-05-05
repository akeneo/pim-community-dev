<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Public;

use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateCatalogActionTest extends IntegrationTestCase
{
    private ?KernelBrowser $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->client = $this->getAuthenticatedClient();
    }

    public function testItFindsTheCatalog(): void
    {
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
        $payload = \json_decode($response->getContent(), true);

        Assert::assertEquals(201, $response->getStatusCode());
        Assert::assertArrayHasKey('id', $payload);
        Assert::assertSame('Store US', $payload['name']);
    }
}
