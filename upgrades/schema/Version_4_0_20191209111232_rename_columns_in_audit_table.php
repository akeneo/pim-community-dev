<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * This migration renames "count" column in "event_count" as count is a SQL keyword
 *
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class Version_4_0_20191209111232_rename_columns_in_audit_table extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $alterTableColumnCountQuery = <<<SQL
ALTER TABLE akeneo_app_audit RENAME COLUMN count TO event_count
SQL;

        $this->addSql($alterTableColumnCountQuery);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
