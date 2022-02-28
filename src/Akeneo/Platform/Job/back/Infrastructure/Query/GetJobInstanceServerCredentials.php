<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Query;

use Doctrine\DBAL\Connection;

class GetJobInstanceServerCredentials
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function byJobInstanceCode(string $jobInstanceCode): ?JobInstanceServerCredentials
    {
        $query = <<<SQL
SELECT * FROM akeneo_batch_job_instance_server_credentials
WHERE job_instance_code = :job_instance_code;
SQL;

        $statement = $this->connection->executeQuery($query, ['job_instance_code' => $jobInstanceCode]);
        $rawJobInstanceServerCredentials = $statement->fetchAssociative();

        if (false === $rawJobInstanceServerCredentials) {
            return null;
        }

        return JobInstanceServerCredentials::create($rawJobInstanceServerCredentials);
    }
}
