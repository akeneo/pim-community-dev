<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\VersioningBundle\Doctrine\Query;

use Doctrine\DBAL\Connection;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetAllButLastVersionIdsByIdsQuery
{
    /** @var Connection */
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function execute(array $versionIds): array
    {
        if (empty($versionIds)) {
            return [];
        }

        $query = <<<SQL
            SELECT current.id
            FROM pim_versioning_version AS current
            WHERE 
                current.id IN (:version_ids)
                AND resource_uuid is not null
            AND EXISTS(
                SELECT 1 FROM pim_versioning_version AS latest 
                WHERE latest.resource_name = current.resource_name
                    AND latest.resource_uuid = current.resource_uuid
                    AND latest.version > current.version
            )
            UNION
            SELECT current.id
            FROM pim_versioning_version AS current
            WHERE
                current.id IN (:version_ids)
                AND resource_uuid is null
            AND EXISTS(
                SELECT 1 FROM pim_versioning_version AS latest 
                WHERE latest.resource_name = current.resource_name
                    AND latest.resource_id = current.resource_id
                    AND latest.version > current.version
            );
        SQL;

        $results = $this->dbConnection->executeQuery(
            $query,
            ['version_ids' => $versionIds],
            ['version_ids' => Connection::PARAM_INT_ARRAY]
        )->fetchFirstColumn();

        return array_map('intval', $results);
    }
}
