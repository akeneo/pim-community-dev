<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\VersioningBundle\Doctrine\Query;

use Doctrine\DBAL\Connection;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetFirstVersionIdsByIdsQuery
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
SELECT id 
FROM pim_versioning_version
WHERE id IN (:version_ids) AND version = 1; 
SQL;

        $results = $this->dbConnection->executeQuery(
            $query,
            ['version_ids' => $versionIds],
            ['version_ids' => Connection::PARAM_INT_ARRAY]
        )->fetchFirstColumn();

        return array_map('intval', $results);
    }
}
