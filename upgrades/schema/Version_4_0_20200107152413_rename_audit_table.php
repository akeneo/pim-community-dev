<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * This migration will rename the audit table
 *
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class Version_4_0_20200107152413_rename_audit_table extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->renameAuditTable();
        $this->dropAuditFk();
    }

    private function renameAuditTable(): void
    {
        $alterTableQuery = <<<SQL
ALTER TABLE akeneo_app_audit RENAME TO akeneo_connectivity_connection_audit
SQL;
        $this->addSql($alterTableQuery);
    }

    private function dropAuditFk(): void
    {
        $alterTableQuery = <<<SQL
ALTER TABLE akeneo_connectivity_connection_audit DROP KEY FK_AUDIT_akeneo_app_audit_code
SQL;
        $this->addSql($alterTableQuery);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
