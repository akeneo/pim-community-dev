<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\FindOneCatalogByIdQueryInterface;
use Akeneo\Catalogs\Domain\Model\Catalog;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindOneCatalogByIdQuery implements FindOneCatalogByIdQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(string $id): ?Catalog
    {
        $query = <<<SQL
        SELECT
            BIN_TO_UUID(catalog.id) AS id,
            catalog.name,
            catalog.owner_id
        FROM akeneo_catalog catalog
        WHERE id = :id
        SQL;

        /** @var array{id: string, name: string, owner_id: string}|false $row */
        $row = $this->connection->executeQuery($query, [
            'id' => Uuid::fromString($id)->getBytes(),
        ])->fetchAssociative();

        if (!$row) {
            return null;
        }

        return new Catalog(
            $row['id'],
            $row['name'],
            (int) $row['owner_id'],
        );
    }
}
