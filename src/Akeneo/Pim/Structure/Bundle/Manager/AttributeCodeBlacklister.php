<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Manager;

use Doctrine\DBAL\Connection;

final class AttributeCodeBlacklister
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function blacklist(string $attributeCode): void
    {
        $blacklistAttributeCodeSql = <<<SQL
INSERT INTO `pim_catalog_attribute_blacklist` (`attribute_code`)
VALUES
    (:attribute_code);
SQL;

        $this->connection->executeUpdate(
            $blacklistAttributeCodeSql,
            [
                'attribute_code' => $attributeCode
            ],
            [
                'attribute_code' => \PDO::PARAM_STR,
            ]
        );
    }

    public function registerJob(string $attributeCode, int $jobExecutionId): void
    {
        $registerJobSql = <<<SQL
UPDATE `pim_catalog_attribute_blacklist`
SET `cleanup_job_execution_id` = :job_execution_id
WHERE `attribute_code` = :attribute_code;
SQL;

        $this->connection->executeUpdate(
            $registerJobSql,
            [
                'attribute_code' => $attributeCode,
                'job_execution_id' => $jobExecutionId
            ],
            [
                'attribute_code' => \PDO::PARAM_STR,
                'job_execution_id' => \PDO::PARAM_INT
            ]
        );
    }

    public function removeFromBlacklist(string $attributeCode): void
    {
        $whiteListSql = <<<SQL
DELETE FROM `pim_catalog_attribute_blacklist`
WHERE `attribute_code` = :attribute_code;
SQL;

        $this->connection->executeUpdate(
            $whiteListSql,
            [
                'attribute_code' => $attributeCode,
            ],
            [
                'attribute_code' => \PDO::PARAM_STR,
            ]
        );
    }
}
