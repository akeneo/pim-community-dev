<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetUniqueAttributeCodes;
use Doctrine\DBAL\Connection;

final class SqlGetUniqueAttributeCodes implements GetUniqueAttributeCodes
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return string[]
     */
    public function all(): array
    {
        $query = <<<SQL
SELECT attribute.code
FROM pim_catalog_attribute attribute
WHERE attribute.is_unique = 1
SQL;

        $uniqueAttributeCodes = $this->connection->executeQuery($query)->fetchAll(\PDO::FETCH_COLUMN);

        return $uniqueAttributeCodes;
    }
}
