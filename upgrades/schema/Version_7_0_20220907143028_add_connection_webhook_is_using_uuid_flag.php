<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220907143028_add_connection_webhook_is_using_uuid_flag extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        if ($schema->getTable('akeneo_connectivity_connection')->hasColumn('webhook_is_using_uuid')) {
            $this->disableMigrationWarning();
            return;
        }

        $this->addSql(
            <<<SQL
            ALTER TABLE akeneo_connectivity_connection
            ADD webhook_is_using_uuid TINYINT(1) DEFAULT 0 NOT NULL AFTER webhook_enabled
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function disableMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }
}
