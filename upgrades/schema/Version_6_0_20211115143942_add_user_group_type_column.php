<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_6_0_20211115143942_add_user_group_type_column extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $table = $schema->getTable('oro_access_group');
        if (!$table->hasColumn('type')) {
            $this->addSql('ALTER TABLE oro_access_group ADD type VARCHAR(30) NOT NULL DEFAULT "default"');
        }
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
