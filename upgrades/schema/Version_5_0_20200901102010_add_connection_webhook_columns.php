<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20200901102010_add_connection_webhook_columns extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
ALTER TABLE akeneo_connectivity_connection
ADD COLUMN webhook_url VARCHAR(255) NULL,
ADD COLUMN webhook_secret VARCHAR(255) NULL,
ADD COLUMN webhook_enabled TINYINT(1) DEFAULT 0 NOT NULL
SQL);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
