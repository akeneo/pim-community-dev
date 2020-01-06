<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * This migration will alter the connection table
 *
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class Version_4_0_20200106112022_alter_table_connection extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $alterTableQuery = <<<SQL
ALTER TABLE akeneo_app RENAME TO akeneo_connectivity_connection
SQL;

        $this->addSql($alterTableQuery);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
