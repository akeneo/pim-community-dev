<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Infrastructure\Persistence\GetCatalogsByOwnerIdQuery;
use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

class GetCatalogsByOwnerIdQueryTest extends IntegrationTestCase
{
    private ?Connection $connection;
    private ?GetCatalogsByOwnerIdQuery $query;

    public function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->connection = self::getContainer()->get(Connection::class);
        $this->query = self::getContainer()->get(GetCatalogsByOwnerIdQuery::class);
    }

    public function testItGetsPaginatedCatalogsByOwnerId(): void
    {
        $ownerId = $this->createUser('owner')->getId();
        $anotherUserId = $this->createUser('another_user')->getId();
        $idUS = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $idFR = 'ed30425c-d9cf-468b-8bc7-fa346f41dd07';
        $idUK = '27c53e59-ee6a-4215-a8f1-2fccbb67ba0d';
        $idJP = '34478398-d77b-44d6-8a71-4d9ba4cb2c3b';
        $this->insertCatalog([
            'id' => $idUS,
            'name' => 'Store US',
            'owner_id' => $ownerId,
        ]);
        $this->insertCatalog([
            'id' => $idFR,
            'name' => 'Store FR',
            'owner_id' => $ownerId,
        ]);
        $this->insertCatalog([
            'id' => $idJP,
            'name' => 'Store JP',
            'owner_id' => $anotherUserId,
        ]);
        $this->insertCatalog([
            'id' => $idUK,
            'name' => 'Store UK',
            'owner_id' => $ownerId,
        ]);

        $resultFirstPage = $this->query->execute($ownerId, 0, 2);
        $expectedFirstPage = [
            new Catalog($idUK, 'Store UK', $ownerId),
            new Catalog($idUS, 'Store US', $ownerId),
        ];
        $this->assertEquals($expectedFirstPage, $resultFirstPage);

        $resultSecondPage = $this->query->execute($ownerId, 2, 2);
        $expectedSecondPage = [
            new Catalog($idFR, 'Store FR', $ownerId),
        ];
        $this->assertEquals($expectedSecondPage, $resultSecondPage);
    }

    private function insertCatalog(array $values): void
    {
        $this->connection->insert(
            'akeneo_catalog',
            \array_merge($values, [
                'id' => Uuid::fromString($values['id'])->getBytes(),
            ])
        );
    }
}
