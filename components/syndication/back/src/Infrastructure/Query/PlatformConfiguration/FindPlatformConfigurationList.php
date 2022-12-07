<?php

namespace Akeneo\Platform\Syndication\Infrastructure\Query\PlatformConfiguration;

use Akeneo\Platform\Syndication\Domain\Model\PlatformConfiguration;
use Akeneo\Platform\Syndication\Domain\Query\PlatformConfiguration\FindPlatformConfigurationListQueryInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

class FindPlatformConfigurationList implements FindPlatformConfigurationListQueryInterface
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
     * @return PlatformConfiguration[]
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
        return array_map(function ($row) {
            $parameters = unserialize($row['raw_parameters']);

            return new PlatformConfiguration(
                $row['code'],
                $row['label'],
                $parameters['catalogProjections'],
                $parameters['connection']['connectedChannelCode'],
            );
        }, $rows);
    }
}
