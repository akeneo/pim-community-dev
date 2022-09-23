<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Application\Persistence\Catalog\DeleteCatalogQueryInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeleteCatalogQuery implements DeleteCatalogQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(string $id): void
    {
        $query = <<<SQL
        DELETE FROM akeneo_catalog
        WHERE id = :id
        SQL;

        $this->connection->executeQuery($query, [
            'id' => Uuid::fromString($id)->getBytes(),
        ]);
    }
}
