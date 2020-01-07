<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * This migration will alter the audit table to remove useless constraint and add another one
 *
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class Version_4_0_20200107152554_alter_audit_table_constraints extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->dropAuditIdx();
        $this->dropIdColumn();
        $this->addPrimaryKey();
    }

    private function dropAuditIdx(): void
    {
        $alterTableQuery = <<<SQL
ALTER TABLE akeneo_connectivity_connection_audit DROP KEY IDX_AUDIT_id
SQL;
        $this->addSql($alterTableQuery);
    }

    private function dropIdColumn(): void
    {
        $alterTableQuery = <<<SQL
ALTER TABLE akeneo_connectivity_connection_audit DROP id
SQL;
        $this->addSql($alterTableQuery);
    }

    private function addPrimaryKey(): void
    {
        $alterTableQuery = <<<SQL
ALTER TABLE akeneo_connectivity_connection_audit
ADD CONSTRAINT PK_AUDIT_connection_code_event_date_event_type PRIMARY KEY (connection_code, event_date, event_type)
SQL;
        $this->addSql($alterTableQuery);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
