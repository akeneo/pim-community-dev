<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Query;

use Doctrine\DBAL\Connection;

class SaveJobInstanceServerCredentials
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function save(JobInstanceServerCredentials $jobInstanceServerCredentials): void
    {
        $sql = <<<SQL
INSERT INTO akeneo_batch_job_instance_server_credentials (
    job_instance_code,
    host,
    user,
    password,
    port,
    is_secure,
    working_directory
)
VALUES (:job_instance_code, :host, :user, :password, :port, :is_secure, :working_directory)
ON DUPLICATE KEY UPDATE 
    job_instance_code = :job_instance_code,
    host = :host,
    user = :user,
    password = :password,
    port = :port,
    is_secure = :is_secure,
    working_directory = :working_directory
SQL;

        $this->connection->executeQuery($sql, [
            'job_instance_code' => $jobInstanceServerCredentials->getJobInstanceCode(),
            'host' => $jobInstanceServerCredentials->getHost(),
            'user' => $jobInstanceServerCredentials->getUser(),
            'password' => $jobInstanceServerCredentials->getPassword(),
            'port' => $jobInstanceServerCredentials->getPort(),
            'is_secure' => $jobInstanceServerCredentials->isSecure(),
            'working_directory' => $jobInstanceServerCredentials->getWorkingDirectory(),
        ]);
    }
}
