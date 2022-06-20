<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\GetCatalogsByOwnerUsernameQueryInterface;
use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @phpstan-import-type ProductSelectionCriterion from Catalog
 */
class GetCatalogsByOwnerUsernameQuery implements GetCatalogsByOwnerUsernameQueryInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * @return array<Catalog>
     */
    public function execute(string $ownerUsername, int $offset = 0, int $limit = 100): array
    {
        $query = <<<SQL
            SELECT
                BIN_TO_UUID(catalog.id) AS id,
                catalog.name,
                catalog.is_enabled,
                catalog.product_selection_criteria,
                oro_user.username AS owner_username
            FROM akeneo_catalog catalog
            JOIN oro_user ON oro_user.id = catalog.owner_id
            WHERE oro_user.username = :owner_username
            ORDER BY catalog.id
            LIMIT :offset, :limit
        SQL;

        /** @var array<array{id: string, name: string, owner_username: string, is_enabled: string, product_selection_criteria: string}> $rows */
        $rows = $this->connection->executeQuery(
            $query,
            [
                'owner_username' => $ownerUsername,
                'limit' => $limit,
                'offset' => $offset,
            ],
            [
                'limit' => Types::INTEGER,
                'offset' => Types::INTEGER,
            ]
        )->fetchAllAssociative();

        return \array_map(static fn ($row) => new Catalog(
            $row['id'],
            $row['name'],
            $row['owner_username'],
            (bool) $row['is_enabled'],
            /** @var array<ProductSelectionCriterion> $criteria */
            $criteria = \json_decode($row['product_selection_criteria'], true),
        ), $rows);
    }
}
