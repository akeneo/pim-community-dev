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
    public function execute(string $resourceName, \DateTime $date, string $dateOperator, int $listSize): iterable
    {
        $dateOperator = '<' !== $dateOperator ? '>' : '<';
        $loggedAt = $date->format('Y-m-d');

        $query = <<<SQL
SELECT id FROM pim_versioning_version 
WHERE resource_name = :resource_name AND logged_at $dateOperator :logged_at AND id > :last_id
ORDER BY resource_name, logged_at, id
LIMIT :list_size
SQL;

        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam('resource_name', $resourceName, \PDO::PARAM_STR);
        $statement->bindParam('logged_at', $loggedAt, \PDO::PARAM_STR);
        $statement->bindParam('list_size', $listSize, \PDO::PARAM_INT);
        $lastId = 0;

        do {
            $statement->bindParam('last_id', $lastId, \PDO::PARAM_INT);
            $statement->execute();
            $results = $statement->fetchAll(\PDO::FETCH_COLUMN);

            if (!empty($results)) {
                $lastId = end($results);
                yield new PurgeableVersionList($resourceName, array_map('intval', $results));
            }
        } while (!empty($results));
    }

    public function count(string $resourceName, \DateTime $date, string $dateOperator): int
    {
        $dateOperator = '<' !== $dateOperator ? '>' : '<';

        $query = <<<SQL
SELECT COUNT(id) FROM pim_versioning_version 
WHERE resource_name = :resource_name AND logged_at $dateOperator :logged_at
SQL;

        $count = $this->dbConnection->executeQuery($query, [
            'resource_name' => $resourceName,
            'logged_at' => $date->format('Y-m-d'),
        ])->fetchColumn();

        return intval($count);
    }
}
