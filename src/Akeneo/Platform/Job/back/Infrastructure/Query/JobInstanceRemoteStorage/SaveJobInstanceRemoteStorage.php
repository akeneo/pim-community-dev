<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Query\JobInstanceRemoteStorage;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

class SaveJobInstanceRemoteStorage
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function save(JobInstanceRemoteStorage $jobInstanceRemoteStorage): void
    {
        $sql = <<<SQL
INSERT INTO akeneo_batch_job_instance_remote_storage (
    job_instance_code,
    host,
    port,
    root,
    username,
    login,
    fingerprint
)
VALUES (:job_instance_code, :host, :port, :root, :username, :login, :fingerprint)
ON DUPLICATE KEY UPDATE 
    job_instance_code = :job_instance_code,
    host = :host,
    port = :port,
    root = :root,
    username = :username,
    login = :login,
    fingerprint = :fingerprint
SQL;

        $this->connection->executeQuery(
            $sql,
            $jobInstanceRemoteStorage->normalize(),
            [
                'login' => Types::JSON
            ]
        );
    }
}
