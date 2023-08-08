<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_8_0_20230801085017_add_columns_to_workflow_table extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new columns to the workflow table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            ALTER TABLE akeneo_workflow
            ADD translation json NOT NULL,
            ADD enabled TINYINT NOT NULL,
            ADD created DATETIME NOT NULL,
            ADD updated DATETIME NOT NULL,
            ADD deleted DATETIME;
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
