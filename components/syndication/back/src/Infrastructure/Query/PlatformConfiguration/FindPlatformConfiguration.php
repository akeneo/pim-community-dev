<?php

namespace Akeneo\Platform\Syndication\Infrastructure\Query\PlatformConfiguration;

use Akeneo\Platform\Syndication\Domain\Model\PlatformConfiguration;
use Akeneo\Platform\Syndication\Domain\Query\PlatformConfiguration\FindPlatformConfigurationQueryInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

class FindPlatformConfiguration implements FindPlatformConfigurationQueryInterface
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
     * {@inheritDoc}
     */
    public function execute(string $platformConfigurationCode): PlatformConfiguration
    {
        $sql = <<<SQL
SELECT
    code,
    label,
    raw_parameters
FROM akeneo_batch_job_instance
WHERE job_name = :job_name
AND status = :status
AND code = :code
SQL;

        $statement = $this->connection->executeQuery(
            $sql,
            [
                'code' => $platformConfigurationCode,
                'job_name' => $this->syndicationJobName,
                'status' => JobInstance::STATUS_READY,
            ],
            [
                'code' => Types::STRING,
                'job_name' => Types::STRING,
                'status' => Types::INTEGER,
            ]
        );
        $result = $statement->fetchAssociative();

        if (!$result) {
            throw new \InvalidArgumentException(sprintf('The platform configuration "%s" does not exist.', $platformConfigurationCode));
        }

        $hydratedResults = $this->hydrate($result);

        return $hydratedResults;
    }

    private function hydrate(array $row): PlatformConfiguration
    {
        $parameters = unserialize($row['raw_parameters']);

        return new PlatformConfiguration(
            $row['code'],
            $row['label'],
            $parameters['catalogProjections'],
            $parameters['connection']['connectedChannelCode'],
        );
    }
}
