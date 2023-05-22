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
        if ($this->migrationWasAlreadyApplied($schema)) {
            $this->disableMigrationWarning();

            return;
        }

        $this->addSql('ALTER TABLE akeneo_batch_step_execution ADD COLUMN current_state JSON NULL;');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function migrationWasAlreadyApplied(Schema $schema): bool
    {
        return $schema->getTable('akeneo_batch_step_execution')->hasColumn('current_step');
    }

    private function disableMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }
}
