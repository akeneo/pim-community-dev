<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Installer;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JobInstaller implements EventSubscriberInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DB_CREATE => ['installJob'],
        ];
    }

    public function installJob() {
        $this->createJobExecutionIndexes();
        $this->createJobInstanceScheduleTable();
    }

    public function createJobExecutionIndexes(): void
    {
        $sql = <<<SQL
        CREATE INDEX user_idx ON akeneo_batch_job_execution (user);
        CREATE INDEX status_idx ON akeneo_batch_job_execution (status);
        CREATE INDEX code_idx ON akeneo_batch_job_instance (code);
        CREATE INDEX is_visible_idx ON akeneo_batch_job_execution (is_visible);
        CREATE INDEX job_instance_id_user_status_is_visible_idx ON akeneo_batch_job_execution (job_instance_id, user, status, is_visible);
SQL;

        $this->connection->executeStatement($sql);
    }

    public function createJobInstanceScheduleTable(): void
    {
        $sql = <<<SQL
CREATE TABLE akeneo_batch_job_instance_schedule (
    job_instance_code VARCHAR(255) NOT NULL,
    cron_expression VARCHAR(255) NOT NULL,
    PRIMARY KEY (job_instance_code)
);
SQL;

        $this->connection->executeStatement($sql);
    }
}
