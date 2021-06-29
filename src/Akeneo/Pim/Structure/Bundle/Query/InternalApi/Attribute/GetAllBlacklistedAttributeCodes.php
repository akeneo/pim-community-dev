<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\InternalApi\Attribute;

use Akeneo\Pim\Structure\Component\Query\InternalApi\GetAllBlacklistedAttributeCodesInterface;
use Doctrine\DBAL\Connection;

final class GetAllBlacklistedAttributeCodes implements GetAllBlacklistedAttributeCodesInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(): array
    {
        $sql = <<<SQL
SELECT attribute_code
FROM `pim_catalog_attribute_blacklist`
SQL;

        return $this->connection
            ->executeQuery($sql)
            ->fetchAll(\PDO::FETCH_COLUMN);
    }
}
