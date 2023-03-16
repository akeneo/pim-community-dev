<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Internal\GetFamiliesAction
 */
class GetFamiliesActionTest extends IntegrationTestCase
{
    private ?Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = self::getContainer()->get(Connection::class);

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsFamilies(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('admin');
        $this->insertFamilies(['tshirt', 'pants', 'guitare']);

        $client->request(
            'GET',
            '/rest/catalogs/families',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        Assert::assertEquals(200, $response->getStatusCode());

        $families = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        Assert::assertCount(3, $families);
        Assert::assertArrayHasKey('code', $families[0]);
        Assert::assertArrayHasKey('label', $families[0]);
    }

    public function testItGetsFamiliesByCodes(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('admin');
        $this->insertFamilies(['tshirt', 'pants', 'guitare']);

        $client->request(
            'GET',
            '/rest/catalogs/families',
            ['codes' => 'tshirt,guitare'],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        Assert::assertEquals(200, $response->getStatusCode());

        $families = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        Assert::assertCount(2, $families);
        Assert::assertArrayHasKey('code', $families[0]);
        Assert::assertArrayHasKey('label', $families[0]);
    }

    public function testItPaginatesSearchResults(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('admin');
        $this->insertFamilies(['GuitarFolk', 'ClassicGuitar', 'ElectricGuitarSomething', 'Piano']);

        $client->request(
            'GET',
            '/rest/catalogs/families',
            ['search' => 'Guitar', 'page' => 2, 'limit' => 2],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $secondPageResponse = $client->getResponse();
        Assert::assertEquals(200, $secondPageResponse->getStatusCode());

        $secondPageFamilies = \json_decode($secondPageResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
        Assert::assertCount(1, $secondPageFamilies);
    }

    private function insertFamilies(array $codes): void
    {
        foreach ($codes as $code) {
            $this->connection->insert(
                'pim_catalog_family',
                ['code' => $code, 'created' => '2022-06-27 16:38:45', 'updated' => '2022-06-27 16:38:45'],
            );
        }
    }
}
