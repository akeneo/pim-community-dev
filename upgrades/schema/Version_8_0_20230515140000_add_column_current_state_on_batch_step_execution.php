<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230515140000_add_column_current_state_on_batch_step_execution extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->skipIf(
            $schema->getTable('akeneo_batch_step_execution')->hasColumn('current_step'),
            'current_state column already exists in akeneo_batch_step_execution'
        );

        $this->addSql('ALTER TABLE akeneo_batch_step_execution ADD COLUMN current_state JSON NULL;');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
