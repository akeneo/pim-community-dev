<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\VersioningBundle\Doctrine\Query;

use Doctrine\DBAL\Connection;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlDeleteVersionsByIdsQuery
{
    /** @var Connection */
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    /**
     * @param int[] $versionIds
     */
    public function execute(array $versionIds): void
    {
        if (empty($versionIds)) {
            return;
        }

        $sql = <<<SQL
DELETE FROM pim_versioning_version
    WHERE id IN (:version_ids);
SQL;
        $this->dbConnection->executeQuery(
            $sql,
            ['version_ids' => $versionIds],
            ['version_ids' => Connection::PARAM_INT_ARRAY]
        );
    }
}
