<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Persistence\ORM\AttributeGroup;

use Doctrine\DBAL\Connection;

class GetAllAttributeGroupCodes
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
FROM pim_catalog_attribute_group
SQL;

        $results = $this->connection->fetchAllAssociative($query) ?: [];

        return \array_map(fn ($row) => $row['code'], $results);
    }
}
