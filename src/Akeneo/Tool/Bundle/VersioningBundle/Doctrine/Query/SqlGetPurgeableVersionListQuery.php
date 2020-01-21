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
SELECT id, logged_at FROM pim_versioning_version 
WHERE resource_name = :resource_name  
  AND (
      (logged_at = :logged_at AND id > :last_id) 
      OR logged_at > :logged_at
  ) 
ORDER BY logged_at ASC, id ASC LIMIT :list_size
SQL;

        return $this->fetchVersionIds($query, $resourceName, $date, $listSize, 0);
    }

    /**
     * Returns an object PurgeableVersionList at each iteration
     */
    public function olderThan(string $resourceName, \DateTime $date, int $listSize): iterable
    {
        $query = <<<SQL
SELECT id, logged_at FROM pim_versioning_version 
WHERE resource_name = :resource_name  
  AND (
      (logged_at = :logged_at AND id < :last_id) 
      OR logged_at < :logged_at
  ) 
ORDER BY logged_at DESC, id DESC LIMIT :list_size
SQL;

        return $this->fetchVersionIds($query, $resourceName, $date, $listSize, PHP_INT_MAX);
    }

    private function fetchVersionIds(
        string $query,
        string $resourceName,
        \DateTime $date,
        int $listSize,
        int $startingId
    ): iterable {
        $loggedAt = $date->format('Y-m-d H:i:s');

        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam('resource_name', $resourceName, \PDO::PARAM_STR);
        $statement->bindParam('list_size', $listSize, \PDO::PARAM_INT);
        $lastId = $startingId;

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
                    array_map(function ($row) {
                        return intval($row['id']);
                    }, $results)
                );
            }
        } while (!empty($results));
    }

    public function countByResource(string $resourceName): int
    {
        $query = 'SELECT COUNT(*) FROM pim_versioning_version WHERE resource_name = :resource_name';

        $count = $this->dbConnection->executeQuery($query, [
            'resource_name' => $resourceName,
        ])->fetchColumn();

        return intval($count);
    }
}
