<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Persistence\ORM\Locale;

use Doctrine\DBAL\Connection;

class GetAllLocalesCodes
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
        $query = 'SELECT code FROM pim_catalog_locale';

        $results = $this->connection->fetchAll($query) ?: [];

        return array_map(fn ($row) => $row['code'], $results);
    }
}
