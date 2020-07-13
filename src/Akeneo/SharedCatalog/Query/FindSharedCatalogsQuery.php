<?php

namespace Akeneo\SharedCatalog\Query;

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
        $normalizedResults = $this->normalize($results);

        return $normalizedResults;
    }

    private function normalize(array $rows)
    {
        return array_map(function ($row) {
            $parameters = unserialize($row['raw_parameters']);

            return [
                'code' => $row['code'],
                'publisher' => $parameters['publisher'] ?? null,
                'recipients' => array_map(function ($recipient) {
                    return $recipient['email'];
                }, $parameters['recipients'] ?? []),
                'channel' => $parameters['filters']['structure']['scope'] ?? null,
                'catalogLocales' => $parameters['filters']['structure']['locales'] ?? [],
                'attributes' => $parameters['filters']['structure']['attributes'] ?? [],
                'branding' => [
                    'logo' => $parameters['branding']['image'] ?? null,
                ],
            ];
        }, $rows);
    }
}
