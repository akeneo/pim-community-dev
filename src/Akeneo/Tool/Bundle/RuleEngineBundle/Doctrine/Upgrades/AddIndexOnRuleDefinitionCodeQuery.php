<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\RuleEngineBundle\Doctrine\Upgrades;

use Doctrine\DBAL\Connection;

final class AddIndexOnRuleDefinitionCodeQuery
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(): void
    {
        $isIndexHereSQL = <<<SQL
SELECT COUNT(1) IsIndexHere FROM INFORMATION_SCHEMA.STATISTICS
WHERE table_schema=DATABASE() AND
      table_name='akeneo_rule_engine_rule_definition' AND
      index_name='akeneo_rule_engine_rule_definition_code__index';
SQL;
        $isIndexHere = $this->connection->executeQuery($isIndexHereSQL)->fetchColumn();
        if (1 === (int) $isIndexHere) {
            return;
        }

        $sql = <<<SQL
        create index akeneo_rule_engine_rule_definition_code__index on akeneo_rule_engine_rule_definition (code);
SQL;
        $this->connection->executeUpdate($sql);
    }
}
