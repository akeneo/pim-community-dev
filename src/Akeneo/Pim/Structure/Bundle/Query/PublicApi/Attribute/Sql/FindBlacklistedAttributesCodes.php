<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\FindBlacklistedAttributesCodesInterface;
use Doctrine\DBAL\Connection;

final class FindBlacklistedAttributesCodes implements FindBlacklistedAttributesCodesInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function all(): array
    {
        $sql = <<<SQL
        SELECT attribute_code FROM pim_catalog_attribute_blacklist;
SQL;

        $statement = $this->connection->executeQuery($sql);

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }
}
