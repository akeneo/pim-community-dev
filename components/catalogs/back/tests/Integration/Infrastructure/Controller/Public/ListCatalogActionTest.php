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
class ListCatalogActionTest extends IntegrationTestCase
{
    private ?KernelBrowser $client;
    private ?UpsertCatalogQuery $upsertCatalogQuery;

    public function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->client = $this->getAuthenticatedClient();
        $this->upsertCatalogQuery = self::getContainer()->get(UpsertCatalogQuery::class);
    }

    public function testItListCatalogs(): void
    {
        $this->insertCatalogs([
            [
                'id' => 'd68b8b7c-74e2-43de-9444-838c5b420f07',
                'name' => 'Store FR',
            ],
            [
                'id' => 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
                'name' => 'Store US',
            ],
        ]);

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs',
        );

        $response = $this->client->getResponse();
        $payload = \json_decode($response->getContent(), true);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertEquals([
            'current_page' => 1,
            '_links' => [
                'self' => [
                    'href' => 'http://localhost/api/rest/v1/catalogs?page=1&limit=20',
                ],
                'first' => [
                    'href' => 'http://localhost/api/rest/v1/catalogs?page=1&limit=20',
                ],
            ],
            '_embedded' => [
                'items' => [
                    [
                        'id' => 'd68b8b7c-74e2-43de-9444-838c5b420f07',
                        'name' => 'Store FR',
                        '_links' => [
                            'self' => [
                                'href' => 'http://localhost/api/rest/v1/catalogs/d68b8b7c-74e2-43de-9444-838c5b420f07',
                            ],
                        ],
                    ],
                    [
                        'id' => 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
                        'name' => 'Store US',
                        '_links' => [
                            'self' => [
                                'href' => 'http://localhost/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c',
                            ],
                        ],
                    ],
                ],
            ],
        ], $payload);
    }

    private function insertCatalogs(array $catalogs): void
    {
        foreach ($catalogs as $catalog) {
            $this->upsertCatalogQuery->execute(Catalog::fromSerialized($catalog));
        }
    }
}
