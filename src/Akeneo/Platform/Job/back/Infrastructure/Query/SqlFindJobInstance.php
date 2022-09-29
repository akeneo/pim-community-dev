<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Query;

use Akeneo\Platform\Job\ServiceApi\JobInstance\FindJobInstanceInterface;
use Akeneo\Platform\Job\ServiceApi\JobInstance\JobInstanceQuery;
use Akeneo\Platform\Job\ServiceApi\JobInstance\JobInstanceQueryPagination;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SqlFindJobInstance implements FindJobInstanceInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function fromQuery(JobInstanceQuery $query): array
    {
        $sql = $this->buildSqlQuery($query);

        return $this->fetchJobInstances($sql, $query);
    }

    private function buildSqlQuery(JobInstanceQuery $query): string
    {
        $types = $query->types;
        $search = $query->search;
        $pagination = $query->pagination;

        $sql = <<<SQL
        SELECT
            job_instance.code,
            job_instance.label
        FROM akeneo_batch_job_instance job_instance
        %s
        %s
SQL;

        $sqlWherePart = $this->buildWherePart($types, $search);
        $sqlPaginationPart = $this->buildPaginationPart($pagination);

        return sprintf($sql, $sqlWherePart, $sqlPaginationPart);
    }

    private function buildWherePart(?array $types, ?string $search): string
    {
        $sqlWhereParts = [];

        if (null !== $types) {
            $sqlWhereParts[] = 'job_instance.type IN (:types)';
        }

        if (null !== $search) {
            $sqlWhereParts[] = 'job_instance.code = :search';
        }

        return empty($sqlWhereParts) ? '' : 'WHERE '.implode(' AND ', $sqlWhereParts);
    }

    private function buildPaginationPart(JobInstanceQueryPagination $queryPagination): string
    {
        $page = $queryPagination->page;
        $limit = $queryPagination->limit;
        $sqlPaginationParts = [];

        if (null !== $page) {
            $sqlPaginationParts[] = ':offset,';
        }

        if (null !== $limit) {
            $sqlPaginationParts[] = ':limit';
        }

        return empty($sqlPaginationParts) ? '' : 'LIMIT '.implode(' ', $sqlPaginationParts);
    }

    private function fetchJobInstances(string $sql, JobInstanceQuery $query): array
    {
        $types = $query->types;
        $search = $query->search;
        $page = $query->pagination->page;
        $limit = $query->pagination->limit;

        return $this->connection->executeQuery(
            $sql,
            [
                'types' => $types,
                'search' => $search,
                'offset' => ($page - 1) * $limit,
                'limit' => $limit,
            ],
            [
                'types' => Connection::PARAM_STR_ARRAY,
                'search' => \PDO::PARAM_STR,
                'offset' => \PDO::PARAM_INT,
                'limit' => \PDO::PARAM_INT,
            ],
        )->fetchAllAssociative();
    }
}
