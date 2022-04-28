<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Query\JobInstanceRemoteStorage;

use Doctrine\DBAL\Connection;

class GetJobInstanceRemoteStorage
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function byJobInstanceCode(string $jobInstanceCode): ?JobInstanceRemoteStorage
    {
        $query = <<<SQL
SELECT * FROM akeneo_batch_job_instance_remote_storage
WHERE job_instance_code = :job_instance_code;
SQL;

        $statement = $this->connection->executeQuery($query, ['job_instance_code' => $jobInstanceCode]);
        $rawJobInstanceRemoteStorage = $statement->fetchAssociative();

        if (false === $rawJobInstanceRemoteStorage) {
            return null;
        }

        return JobInstanceRemoteStorage::create($rawJobInstanceRemoteStorage);
    }
}
