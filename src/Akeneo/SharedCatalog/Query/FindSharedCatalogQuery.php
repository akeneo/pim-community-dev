<?php

namespace Akeneo\SharedCatalog\Query;

use Akeneo\SharedCatalog\Model\SharedCatalog;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

class FindSharedCatalogQuery implements FindSharedCatalogQueryInterface
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

    public function find(string $code): ?SharedCatalog
    {
        $sql = <<<SQL
SELECT
    code,
    raw_parameters
FROM akeneo_batch_job_instance
WHERE job_name = :job_name
AND status = :status
AND code = :code
SQL;

        $statement = $this->connection->executeQuery(
            $sql,
            [
                'code' => $code,
                'job_name' => $this->sharedCatalogJobName,
                'status' => JobInstance::STATUS_READY,
            ],
            [
                'code' => Types::STRING,
                'job_name' => Types::STRING,
                'status' => Types::INTEGER,
            ]
        );

        $row = $statement->fetch();

        if (!$row) {
            return null;
        }

        $hydrated = $this->hydrate($row);

        return $hydrated;
    }

    private function hydrate(array $row): SharedCatalog
    {
        $parameters = unserialize($row['raw_parameters']);

        return new SharedCatalog(
            $row['code'],
            $parameters['publisher'] ?? null,
            $parameters['recipients'] ?? [],
            $parameters['filters'] ?? null,
            $parameters['branding'] ?? null
        );
    }
}
