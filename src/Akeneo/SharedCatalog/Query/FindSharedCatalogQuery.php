<?php

namespace Akeneo\SharedCatalog\Query;

use Akeneo\SharedCatalog\Model\SharedCatalog;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

class FindSharedCatalogQuery implements FindSharedCatalogQueryInterface
{
    public function __construct(
        private Connection $connection,
        private string $sharedCatalogJobName
    ) {
    }

    public function find(string $code): ?SharedCatalog
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

        return $this->hydrate($row);
    }

    private function hydrate(array $row): SharedCatalog
    {
        $parameters = unserialize($row['raw_parameters']);

        return new SharedCatalog(
            $row['code'],
            $row['label'],
            $parameters['publisher'] ?? null,
            $parameters['recipients'] ?? [],
            $parameters['filters'] ?? null,
            $parameters['branding'] ?? null
        );
    }
}
