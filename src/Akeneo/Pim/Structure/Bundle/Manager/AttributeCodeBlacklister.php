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

    public function blacklist(array $attributeCodes): void
    {
        if (empty($attributeCodes)) {
            return;
        }

        $placeholder = [];
        $params = [];
        foreach ($attributeCodes as $key => $attributeCode) {
            $placeholder[] = '(:attribute_code_' . $key . ')';
            $params['attribute_code_' . $key] = $attributeCode;
        }

        $placeholder = implode(',', $placeholder);

        $blacklistAttributeCodeSql = <<<SQL
INSERT INTO `pim_catalog_attribute_blacklist` (`attribute_code`)
VALUES $placeholder;
SQL;

        $this->connection->executeStatement(
            $blacklistAttributeCodeSql,
            $params
        );
    }

    public function registerJob(array $attributeCodes, int $jobExecutionId): void
    {
        if (empty($attributeCodes)) {
            return;
        }

        $registerJobSql = <<<SQL
UPDATE `pim_catalog_attribute_blacklist`
SET `cleanup_job_execution_id` = :job_execution_id
WHERE `attribute_code` IN (:attribute_codes);
SQL;

        $this->connection->executeStatement(
            $registerJobSql,
            [
                'attribute_codes' => $attributeCodes,
                'job_execution_id' => $jobExecutionId
            ],
            [
                'attribute_codes' => Connection::PARAM_STR_ARRAY,
                'job_execution_id' => \PDO::PARAM_INT
            ]
        );
    }

    public function removeFromBlacklist(array $attributeCodes): void
    {
        if (empty($attributeCodes)) {
            return;
        }

        $whiteListSql = <<<SQL
DELETE FROM `pim_catalog_attribute_blacklist`
WHERE `attribute_code` IN (:attribute_codes);
SQL;

        $this->connection->executeStatement(
            $whiteListSql,
            [
                'attribute_codes' => $attributeCodes,
            ],
            [
                'attribute_codes' => Connection::PARAM_STR_ARRAY,
            ]
        );
    }
}
