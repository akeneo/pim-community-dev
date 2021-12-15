<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20211207154203_add_is_trackable_in_step_execution extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->skipIf(
            $schema->getTable('akeneo_batch_step_execution')->hasColumn('is_trackable'),
            'is_trackable column already exists in akeneo_batch_step_execution'
        );

        $this->addSql('ALTER TABLE akeneo_batch_step_execution ADD is_trackable TINYINT(1) DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
