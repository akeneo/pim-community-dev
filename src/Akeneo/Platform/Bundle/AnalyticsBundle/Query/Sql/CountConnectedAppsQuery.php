<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AnalyticsBundle\Query\Sql;

use Akeneo\Tool\Component\Analytics\CountConnectedAppsQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountConnectedAppsQuery implements CountConnectedAppsQueryInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(): int
    {
        $query = <<<SQL
SELECT count(*)
FROM akeneo_connectivity_connected_app
SQL;

        return (int) $this->connection->fetchOne($query);
    }
}
