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
class DeleteCatalogActionTest extends IntegrationTestCase
{
    private ?KernelBrowser $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->client = $this->getAuthenticatedClient();
        $this->upsertCatalogQuery = self::getContainer()->get(UpsertCatalogQuery::class);
    }

    public function testItDeletesTheCatalog(): void
    {
        $this->upsertCatalogQuery->execute(Catalog::fromSerialized([
            'id' => 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'name' => 'Store US',
        ]));

        $this->client->request(
            'DELETE',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c',
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(204, $response->getStatusCode());
        Assert::assertEmpty($response->getContent());
    }
}
