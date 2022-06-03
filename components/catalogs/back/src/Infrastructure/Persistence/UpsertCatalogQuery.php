<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\UpsertCatalogQueryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpsertCatalogQuery implements UpsertCatalogQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(
        string $id,
        string $name,
        string $ownerUsername,
        bool $enabled
    ): void {
        $query = <<<SQL
        INSERT INTO akeneo_catalog (id, name, owner_id, is_enabled)
        VALUES (UUID_TO_BIN(:id), :name, :owner_id, :is_enabled)
        ON DUPLICATE KEY UPDATE name = :name, updated = NOW()
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'id' => $id,
                'name' => $name,
                'owner_id' => $ownerUsername,
                'is_enabled' => $enabled,
            ],
            [
                'is_enabled' => Types::BOOLEAN,
            ]
        );
    }
}
