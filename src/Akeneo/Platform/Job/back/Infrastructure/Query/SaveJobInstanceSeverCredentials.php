<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Job\Infrastructure\Query;

use Doctrine\DBAL\Connection;

class SaveJobInstanceSeverCredentials
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
VALUES (:job_instance_code, :cron_expression)
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
