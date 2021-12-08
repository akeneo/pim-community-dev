<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_6_0_20210917103700_add_user_group_default_permissions extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $table = $schema->getTable('oro_access_group');
        if ($table->hasColumn('default_permissions')) {
            $this->disableMigrationWarning();

            return;
        }

        $this->addSql('ALTER TABLE oro_access_group ADD default_permissions JSON NULL DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    /**
     * Function that does a non altering operation on the DB using SQL to hide the doctrine warning stating that no
     * sql query has been made to the db during the migration process.
     */
    private function disableMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }
}
