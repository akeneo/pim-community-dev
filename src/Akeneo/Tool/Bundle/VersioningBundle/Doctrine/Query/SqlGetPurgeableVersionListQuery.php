<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\VersioningBundle\Doctrine\Query;

use Akeneo\Tool\Bundle\VersioningBundle\Purger\PurgeableVersionList;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetPurgeableVersionListQuery
{
    /** @var Connection */
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    /**
     * Returns an object PurgeableVersionList at each iteration
     */
    public function youngerThan(string $resourceName, \DateTime $date, int $listSize): iterable
    {
        $query = <<<SQL
SELECT id FROM pim_versioning_version 
WHERE logged_at > :logged_at  
  AND resource_name = :resource_name
  AND id > :last_id 
ORDER BY logged_at, id LIMIT :list_size
SQL;

        return $this->fetchVersionIds($query, $resourceName, $date, $listSize);
    }

    /**
     * Returns an object PurgeableVersionList at each iteration
     */
    public function olderThan(string $resourceName, \DateTime $date, int $listSize): iterable
    {
        $query = <<<SQL
SELECT id, logged_at FROM pim_versioning_version 
WHERE resource_name = :resource_name  
  AND logged_at < :logged_at
  AND id < :last_id 
ORDER BY logged_at DESC, id DESC LIMIT :list_size
SQL;

        return $this->fetchVersionIds($query, $resourceName, $date, $listSize);
    }

    private function fetchVersionIds(string $query, string $resourceName, \DateTime $date, int $listSize): iterable
    {
        $loggedAt = $date->format('Y-m-d');

        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam('resource_name', $resourceName, \PDO::PARAM_STR);
        $statement->bindParam('list_size', $listSize, \PDO::PARAM_INT);
        $lastId = 999999999999;

        do {
            $statement->bindParam('logged_at', $loggedAt, \PDO::PARAM_STR);
            $statement->bindParam('last_id', $lastId, \PDO::PARAM_INT);
            $statement->execute();
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

            if (!empty($results)) {
                $lastResult = end($results);
                $lastId = $lastResult['id'];
                $loggedAt = $lastResult['logged_at'];
                yield new PurgeableVersionList(
                    $resourceName,
                    array_map(function($row) {
                        return intval($row['id']);
                    }, $results)
                );
            }
        } while (!empty($results));
    }

    public function countYoungerThan(string $resourceName, \DateTime $date): int
    {
        $query = <<<SQL
SELECT COUNT(*) FROM pim_versioning_version USE INDEX (resource_name_logged_at_idx)
WHERE logged_at > :logged_at AND resource_name = :resource_name 
SQL;

        $count = $this->dbConnection->executeQuery($query, [
            'resource_name' => $resourceName,
            'logged_at' => $date->format('Y-m-d'),
        ])->fetchColumn();

        return intval($count);
    }

    public function countOlderThan(string $resourceName, \DateTime $date): int
    {
        $query = <<<SQL
SELECT COUNT(*) FROM pim_versioning_version USE INDEX (resource_name_logged_at_idx)
WHERE logged_at < :logged_at AND resource_name = :resource_name 
SQL;

        $count = $this->dbConnection->executeQuery($query, [
            'resource_name' => $resourceName,
            'logged_at' => $date->format('Y-m-d'),
        ])->fetchColumn();

        return intval($count);
    }
}
