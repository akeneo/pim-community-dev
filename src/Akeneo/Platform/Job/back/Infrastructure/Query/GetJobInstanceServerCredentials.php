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
