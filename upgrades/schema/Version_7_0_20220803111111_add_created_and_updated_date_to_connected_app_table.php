<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220803111111_add_created_and_updated_date_to_connected_app_table extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            ALTER TABLE akeneo_connectivity_connected_app
                ADD updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER has_outdated_scopes,
                ADD created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER has_outdated_scopes                
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
