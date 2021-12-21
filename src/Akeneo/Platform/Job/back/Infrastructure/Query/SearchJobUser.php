<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Query;

use Akeneo\Platform\Job\Application\SearchJobUser\SearchJobUserInterface;
use Akeneo\Platform\Job\Application\SearchJobUser\SearchJobUserQuery;
use Doctrine\DBAL\Connection;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SearchJobUser implements SearchJobUserInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function search(SearchJobUserQuery $query): array
    {
        $sql = $this->createSqlQuery($query);

        return $this->fetchUsers($sql, $query);
    }

    private function createSqlQuery(SearchJobUserQuery $query): string
    {
        $username = $query->search;

        $sql = <<<SQL
            SELECT DISTINCT job_execution.user
            FROM akeneo_batch_job_execution job_execution
            WHERE job_execution.is_visible = 1
            AND job_execution.user IS NOT NULL
            %s
            ORDER BY job_execution.user
        SQL;

        $wherePart = '';

        if (!empty($username)) {
            $wherePart = 'AND job_execution.user LIKE :username';
        }

        $sql = sprintf($sql, $wherePart);

        return $sql;
    }

    private function fetchUsers(string $sql, SearchJobUserQuery $query): array
    {
        $jobUsers = $this->connection->executeQuery(
            $sql,
            ['username' => sprintf('%%%s%%', $query->search)],
        )->fetchFirstColumn();

        return $jobUsers;
    }
}
