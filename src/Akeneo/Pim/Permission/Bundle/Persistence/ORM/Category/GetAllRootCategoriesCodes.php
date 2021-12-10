<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category;

use Doctrine\DBAL\Connection;

class GetAllRootCategoriesCodes
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return string[]
     */
    public function execute(): array
    {
        $query = <<<SQL
SELECT code
FROM pim_catalog_category
WHERE parent_id IS NULL
SQL;

        $results = $this->connection->fetchAllAssociative($query) ?: [];

        return \array_map(fn ($row) => $row['code'], $results);
    }
}
