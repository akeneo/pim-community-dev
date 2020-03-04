<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_4_0_20200302143200_update_audit_event_date extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->truncateAudit();
        $this->dropEventDateColumn();
        $this->addEventDatetimeColumn();
    }

    private function truncateAudit()
    {
        $this->addSql('TRUNCATE TABLE akeneo_connectivity_connection_audit');
    }

    private function dropEventDateColumn()
    {
        $this->addSql('ALTER TABLE akeneo_connectivity_connection_audit DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE akeneo_connectivity_connection_audit DROP COLUMN event_date');
    }

    private function addEventDatetimeColumn()
    {
        $this->addSql('ALTER TABLE akeneo_connectivity_connection_audit ADD event_datetime DATETIME NOT NULL');
        $this->addSql('ALTER TABLE akeneo_connectivity_connection_audit ADD PRIMARY KEY (event_datetime, connection_code, event_type)');
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
