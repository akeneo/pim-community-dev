<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Installer;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JobInstaller implements EventSubscriberInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DB_CREATE => ['createJobExecutionIndexes'],
        ];
    }

    public function createJobExecutionIndexes(): void
    {
        $sql = <<<SQL
        CREATE INDEX user_idx ON akeneo_batch_job_execution (user);
        CREATE INDEX status_idx ON akeneo_batch_job_execution (status);
SQL;

        $this->connection->executeStatement($sql);
    }
}
