<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20210914161025_add_index_on_rule_definition_code extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = <<<SQL
            SELECT COUNT(1) indexCount
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE table_schema=DATABASE()
            AND table_name='akeneo_rule_engine_rule_definition'
            AND index_name='akeneo_rule_engine_rule_definition_code__index';
        SQL;

        $indexCount = $this->connection->executeQuery($sql)->fetchOne();
        $this->skipIf(1 <= (int)$indexCount, "The table akeneo_rule_engine_rule_definition already have an index on code");

        $this->addSql(
            "CREATE INDEX akeneo_rule_engine_rule_definition_code__index ON akeneo_rule_engine_rule_definition(code)"
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
