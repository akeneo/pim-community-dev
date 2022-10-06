<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Application\Persistence\Catalog\FindOneCatalogByIdQueryInterface;
use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FindOneCatalogByIdQuery implements FindOneCatalogByIdQueryInterface
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
            catalog.is_enabled,
            oro_user.username AS owner_username
        FROM akeneo_catalog catalog
        JOIN oro_user ON oro_user.id = catalog.owner_id
        WHERE catalog.id = :id
        SQL;

        /** @var array{id: string, name: string, owner_username: string, is_enabled: string}|false $row */
        $row = $this->connection->executeQuery($query, [
            'id' => Uuid::fromString($id)->getBytes(),
        ])->fetchAssociative();

        if (!$row) {
            return null;
        }

        return new Catalog(
            $row['id'],
            $row['name'],
            $row['owner_username'],
            (bool) $row['is_enabled'],
        );
    }
}
