<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_7_0_20220524134005_add_is_enabled_column_to_catalog extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        if ($schema->getTable('akeneo_catalog')->hasColumn('is_enabled')) {
            $this->write('is_enabled column already exists in akeneo_catalog');

            return;
        }

        $this->addSql(<<<SQL
        ALTER TABLE akeneo_catalog
        ADD is_enabled TINYINT NOT NULL DEFAULT 0 AFTER owner_id,
        ALGORITHM=INPLACE, LOCK=NONE;
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
