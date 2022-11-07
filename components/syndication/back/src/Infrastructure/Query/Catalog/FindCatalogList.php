<?php

namespace Akeneo\Platform\Syndication\Infrastructure\Query\Catalog;

use Akeneo\Platform\Syndication\Domain\Model\Catalog;
use Akeneo\Platform\Syndication\Domain\Query\Catalog\FindCatalogListQueryInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

class FindCatalogList implements FindCatalogListQueryInterface
{
    private Connection $connection;
    private string $syndicationJobName;

    public function __construct(
        Connection $connection,
        string $syndicationJobName
    ) {
        $this->connection = $connection;
        $this->syndicationJobName = $syndicationJobName;
    }

    /**
     * @return Catalog[]
     */
    public function execute(): array
    {
        $sql = <<<SQL
SELECT
    code,
    label,
    raw_parameters
FROM akeneo_batch_job_instance
WHERE job_name = :job_name
AND status = :status
SQL;

        $statement = $this->connection->executeQuery(
            $sql,
            [
                'job_name' => $this->syndicationJobName,
                'status' => JobInstance::STATUS_READY,
            ],
            [
                'job_name' => Types::STRING,
                'status' => Types::INTEGER,
            ]
        );

        $results = $statement->fetchAllAssociative();
        $hydratedResults = $this->hydrate($results);

        return $hydratedResults;
    }

    private function hydrate(array $rows): array
    {
        return array_merge(...array_values(array_map(function ($row) {
            $parameters = unserialize($row['raw_parameters']);

            return array_filter(array_map(function ($catalog) {
                if (!isset($catalog['uuid'])) {
                    return null;
                }

                return new Catalog(
                    $catalog['uuid'],
                    $catalog['code'],
                    $catalog['label'] ?? $catalog['code']
                );
            }, $parameters['catalogProjections']));
        }, $rows)));
    }
}
