<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Query;

use Akeneo\Platform\Job\ServiceApi\JobInstance\FindJobInstanceInterface;
use Akeneo\Platform\Job\ServiceApi\JobInstance\JobInstance;
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
        $jobNames = $query->jobNames;
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

        $sqlWherePart = $this->buildWherePart($jobNames, $search);
        $sqlPaginationPart = $pagination instanceof JobInstanceQueryPagination ? $this->buildPaginationPart($pagination) : '';

        return sprintf($sql, $sqlWherePart, $sqlPaginationPart);
    }

    private function buildWherePart(?array $jobNames, ?string $search): string
    {
        $sqlWhereParts = [];

        if (null !== $jobNames) {
            $sqlWhereParts[] = 'job_instance.job_name IN (:job_names)';
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
        $queryParametersAndTypes = $this->buildQueryParametersAndTypes($query);
        $queryParameters = $queryParametersAndTypes['query_parameters'];
        $queryTypes = $queryParametersAndTypes['query_types'];

        $results = $this->connection->executeQuery(
            $sql,
            $queryParameters,
            $queryTypes,
        )->fetchAllAssociative();

        return array_map(
            static fn (array $jobInstance) => new JobInstance($jobInstance['code'], $jobInstance['label']),
            $results,
        );
    }

    private function buildQueryParametersAndTypes(JobInstanceQuery $query): array
    {
        $queryParameters = [
            'job_names' => $query->jobNames,
            'search' => $query->search,
        ];

        $queryTypes = [
            'job_names' => Connection::PARAM_STR_ARRAY,
            'search' => \PDO::PARAM_STR,
        ];

        if ($query->pagination instanceof JobInstanceQueryPagination) {
            $queryParameters = array_merge($queryParameters, [
                'offset' => ($query->pagination->page - 1) * $query->pagination->limit,
                'limit' => $query->pagination->limit,
            ]);

            $queryTypes = array_merge($queryTypes, [
                'offset' => \PDO::PARAM_INT,
                'limit' => \PDO::PARAM_INT,
            ]);
        }

        return [
            'query_parameters' => $queryParameters,
            'query_types' => $queryTypes,
        ];
    }
}
