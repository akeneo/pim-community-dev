<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version_8_0_20230818093227_add_is_visible_on_batch_job_instance_table extends AbstractMigration
{
    private const TABLE_NAME = 'akeneo_batch_job_instance';
    private const IS_VISIBLE_COLUMN = 'is_visible';

    public function up(Schema $schema): void
    {
        if ($schema->getTable(self::TABLE_NAME)->hasColumn(self::IS_VISIBLE_COLUMN)) {
            $this->addSql('SELECT 1');

            return;
        }

        $this->addSql(
            sprintf('ALTER TABLE %s ADD %s TINYINT(1) DEFAULT 1', self::TABLE_NAME, self::IS_VISIBLE_COLUMN)
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
