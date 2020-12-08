<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\InternalApi\Attribute;

use Doctrine\DBAL\Connection;

final class GetBlacklistedAttributeJobExecutionId
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function forAttributeCode(string $attributeCode): ?int
    {
        $sql = <<<SQL
SELECT cleanup_job_execution_id
FROM `pim_catalog_attribute_blacklist`
WHERE attribute_code = :attribute_code;
SQL;

        $result = $this->connection->executeQuery(
            $sql,
            [
                'attribute_code' => $attributeCode
            ],
            [
                'attribute_code' => \PDO::PARAM_STR
            ]
        )->fetchColumn();

        if (false === $result) {
            return null;
        }

        return (int) $result;
    }
}
