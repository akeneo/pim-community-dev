<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_5_0_20200221154552_connection_is_auditable extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $alterTable = <<<SQL
ALTER TABLE akeneo_connectivity_connection
ADD auditable tinyint(1) default 0 not null;
SQL;
        $updateRows = <<<SQL
UPDATE akeneo_connectivity_connection
SET auditable = 1
WHERE flow_type != 'other'
SQL;

        $this->addSql($alterTable);
        $this->addSql($updateRows);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
