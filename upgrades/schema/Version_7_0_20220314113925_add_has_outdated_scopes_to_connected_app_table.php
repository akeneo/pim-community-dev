<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220314113925_add_has_outdated_scopes_to_connected_app_table extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $table = $schema->getTable('akeneo_connectivity_connected_app');
        if ($table->hasColumn('has_outdated_scopes')) {
            $this->disableMigrationWarning();
            return;
        }

        $this->addSql("ALTER TABLE akeneo_connectivity_connected_app ADD has_outdated_scopes TINYINT DEFAULT 0 NOT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    /**
     * Function that does a non altering operation on the DB using SQL to hide the doctrine warning stating that no
     * sql query has been made to the db during the migration process.
     */
    private function disableMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }
}
