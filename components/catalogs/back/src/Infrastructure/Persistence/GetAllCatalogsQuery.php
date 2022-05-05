<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\GetAllCatalogsQueryInterface;
use Akeneo\Catalogs\Domain\Model\Catalog;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAllCatalogsQuery implements GetAllCatalogsQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function execute(): array
    {
        $query = <<<SQL
        SELECT
            BIN_TO_UUID(catalog.id) AS id,
            catalog.name
        FROM akeneo_catalog catalog
        ORDER BY created, id
        SQL;

        /** @var array<array{id: string, name: string}> $rows */
        $rows = $this->connection->executeQuery($query)->fetchAllAssociative();

        return \array_map(static fn (array $row) => Catalog::fromSerialized($row), $rows);
    }
}
