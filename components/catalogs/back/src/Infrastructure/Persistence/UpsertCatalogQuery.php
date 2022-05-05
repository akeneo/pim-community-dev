<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\UpsertCatalogQueryInterface;
use Akeneo\Catalogs\Domain\Model\Catalog;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpsertCatalogQuery implements UpsertCatalogQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(Catalog $catalog): void
    {
        $query = <<<SQL
        INSERT INTO akeneo_catalog (id, name)
        VALUES (UUID_TO_BIN(:id), :name)
        ON DUPLICATE KEY UPDATE name = :name, updated = NOW()
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'id' => $catalog->getId(),
                'name' => $catalog->getName(),
            ]
        );
    }
}
