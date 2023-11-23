<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Platform\VersionProviderInterface;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

final class Version_6_0_20220518134914_set_not_null_fields_for_job_and_step_execution_tables extends AbstractMigration implements ContainerAwareInterface
{
    private ?ContainerInterface $container;

    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    public function up(Schema $schema): void
    {
        if ($this->isSassVersion()) {
            $this->write('This migration is only for non Sass version');

            return;
        }

        if ($this->isMigrationDone()) {
            $this->write('Migration already done');

            return;
        }

        $this->addSql("UPDATE akeneo_batch_job_execution SET is_stoppable = 0 WHERE is_stoppable IS NULL");
        $this->addSql("UPDATE akeneo_batch_job_execution SET step_count = 1 WHERE step_count IS NULL");
        $this->addSql("UPDATE akeneo_batch_job_execution SET is_visible = 1 WHERE is_visible IS NULL");
        $this->addSql("UPDATE akeneo_batch_step_execution SET is_trackable = 0 WHERE is_trackable IS NULL");

        $this->addSql("ALTER TABLE akeneo_batch_job_execution MODIFY COLUMN is_stoppable TINYINT(1) DEFAULT 0 NOT NULL, ALGORITHM=INPLACE, LOCK=NONE");
        $this->addSql("ALTER TABLE akeneo_batch_job_execution MODIFY COLUMN step_count INT DEFAULT 1 NOT NULL, ALGORITHM=INPLACE, LOCK=NONE");
        $this->addSql("ALTER TABLE akeneo_batch_job_execution MODIFY COLUMN is_visible TINYINT(1) DEFAULT 1 NOT NULL, ALGORITHM=INPLACE, LOCK=NONE");
        $this->addSql("ALTER TABLE akeneo_batch_step_execution MODIFY COLUMN is_trackable TINYINT(1) DEFAULT 0 NOT NULL, ALGORITHM=INPLACE, LOCK=NONE");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function isSassVersion(): bool
    {
        /** @var VersionProviderInterface $versionProvider */
        $versionProvider = $this->container->get('pim_catalog.version_provider');

        return $versionProvider->isSaaSVersion();
    }

    private function isMigrationDone()
    {
        $jobExecutionFieldsToMigrate = $this->connection->executeQuery("
            SHOW COLUMNS
            FROM `akeneo_batch_job_execution`
            WHERE `Null` = 'YES'
            AND `Field` IN ('is_stoppable', 'step_count', 'is_visible')
        ")->fetchAllAssociative();

        $stepExecutionFieldsToMigrate = $this->connection->executeQuery("
          SHOW COLUMNS
          FROM `akeneo_batch_step_execution`
          WHERE `Null` = 'YES'
          AND `Field` = 'is_trackable'
        ")->fetchAllAssociative();

        return empty($jobExecutionFieldsToMigrate) && empty($stepExecutionFieldsToMigrate);
    }
}
