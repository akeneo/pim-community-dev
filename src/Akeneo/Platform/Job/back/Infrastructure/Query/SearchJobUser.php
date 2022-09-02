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
    private const USER_TYPE = 'user';

    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * {@inheritdoc}
     */
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
            INNER JOIN oro_user ON job_execution.user = oro_user.username
            WHERE job_execution.is_visible = 1
            AND job_execution.user IS NOT NULL
            AND oro_user.user_type = :user_type
            %s
            ORDER BY job_execution.user
        SQL;

        $wherePart = '';

        if (!empty($username)) {
            $wherePart = 'AND job_execution.user LIKE :username';
        }

        return sprintf($sql, $wherePart);
    }

    private function fetchUsers(string $sql, SearchJobUserQuery $query): array
    {
        return $this->connection->executeQuery(
            $sql,
            [
                'username' => sprintf('%%%s%%', $query->search),
                'user_type' => self::USER_TYPE,
            ],
        )->fetchFirstColumn();
    }
}
