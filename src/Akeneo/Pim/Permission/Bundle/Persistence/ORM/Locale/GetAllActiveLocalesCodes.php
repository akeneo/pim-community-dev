<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Persistence\ORM\Locale;

use Doctrine\DBAL\Connection;

class GetAllActiveLocalesCodes
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
        $query = 'SELECT code FROM pim_catalog_locale WHERE is_activated = 1';

        $results = $this->connection->fetchAllAssociative($query) ?: [];

        return \array_map(fn (array $row): string => $row['code'], $results);
    }
}
