<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AnalyticsBundle\Query\Sql;

use Akeneo\Tool\Component\Analytics\IsDemoCatalogQuery;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsDemoCatalog implements IsDemoCatalogQuery
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * By requiring only one of the user email of the demo catalog, it's robust to some changes such as email modification
     * or deletion of one of the user.
     */
    public function fetch(): bool
    {
        $query = "SELECT DISTINCT true FROM oro_user WHERE email IN('Julia@example.com', 'Peter@example.com', 'Sandra@example.com')";

        $isDemo = $this->connection->executeQuery($query)->fetch(\PDO::FETCH_COLUMN, 0);

        return $isDemo === '1';
    }
}
