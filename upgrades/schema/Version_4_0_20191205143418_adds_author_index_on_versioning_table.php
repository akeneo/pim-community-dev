<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * This migration adds an index on author on pim_versioning_version table
 *
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class Version_4_0_20191205143418_adds_author_index_on_versioning_table extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $alterTableQuery = <<<SQL
ALTER TABLE pim_versioning_version ADD INDEX author_idx (author)
SQL;

        $this->addSql($alterTableQuery);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
