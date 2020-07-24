<?php

namespace Akeneo\SharedCatalog\Query;

use Akeneo\SharedCatalog\Model\SharedCatalog;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

class FindSharedCatalogsQuery implements FindSharedCatalogsQueryInterface
{
    /** @var Connection */
    private $connection;

    /** @var string */
    private $sharedCatalogJobName;

    public function __construct(
        Connection $connection,
        string $sharedCatalogJobName
    ) {
        $this->connection = $connection;
        $this->sharedCatalogJobName = $sharedCatalogJobName;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(): array
    {
        $sql = <<<SQL
SELECT
    code,
    raw_parameters
FROM akeneo_batch_job_instance
WHERE job_name = :job_name
AND status = :status
SQL;

        $statement = $this->connection->executeQuery(
            $sql,
            [
                'job_name' => $this->sharedCatalogJobName,
                'status' => JobInstance::STATUS_READY,
            ],
            [
                'job_name' => Types::STRING,
                'status' => Types::INTEGER,
            ]
        );

        $results = $statement->fetchAll();
        $hydratedResults = $this->hydrate($results);

        return $hydratedResults;
    }

    private function hydrate(array $rows): array
    {
        return array_map(function ($row) {
            $parameters = unserialize($row['raw_parameters']);

            return new SharedCatalog(
                $row['code'],
                $parameters['publisher'] ?? null,
                $parameters['recipients'] ?? [],
                $parameters['filters'] ?? null,
                $parameters['branding'] ?? null
            );
        }, $rows);
    }
}
