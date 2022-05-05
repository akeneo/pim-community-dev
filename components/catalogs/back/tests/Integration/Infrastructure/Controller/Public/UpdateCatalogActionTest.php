<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Public;

use Akeneo\Catalogs\Domain\Model\Catalog;
use Akeneo\Catalogs\Infrastructure\Persistence\UpsertCatalogQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateCatalogActionTest extends IntegrationTestCase
{
    private ?KernelBrowser $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->client = $this->getAuthenticatedClient();
        $this->upsertCatalogQuery = self::getContainer()->get(UpsertCatalogQuery::class);
    }

    public function testItUpdatesTheCatalog(): void
    {
        $this->upsertCatalogQuery->execute(Catalog::fromSerialized([
            'id' => 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'name' => 'Store US',
        ]));

        $this->client->request(
            'PATCH',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            \json_encode([
                'name' => 'Store US [NEW]',
            ]),
        );

        $response = $this->client->getResponse();
        $payload = \json_decode($response->getContent(), true);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertArrayHasKey('id', $payload);
        Assert::assertSame('Store US [NEW]', $payload['name']);
    }
}
