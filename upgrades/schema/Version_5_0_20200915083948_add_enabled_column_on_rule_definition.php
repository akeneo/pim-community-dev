<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20200915083948_add_enabled_column_on_rule_definition extends AbstractMigration
{
    private const TABLE = 'akeneo_rule_engine_rule_definition';
    private const COLUMN = 'enabled';

    public function up(Schema $schema) : void
    {
        if ($schema->getTable(static::TABLE)->hasColumn(static::COLUMN)) {
            return;
        }

        $addColumnSql = <<<SQL
ALTER TABLE akeneo_rule_engine_rule_definition
    ADD COLUMN enabled boolean NOT NULL DEFAULT true;
SQL;
        $addColumnSql = sprintf(
            'ALTER TABLE %s ADD COLUMN %s boolean NOT NULL DEFAULT true;',
            static::TABLE,
            static::COLUMN,
        );

        $this->addSql($addColumnSql);

    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
