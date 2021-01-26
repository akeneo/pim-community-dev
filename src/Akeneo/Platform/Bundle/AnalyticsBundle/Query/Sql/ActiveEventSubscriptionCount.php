<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AnalyticsBundle\Query\Sql;

use Akeneo\Tool\Component\Analytics\ActiveEventSubscriptionCountQuery;
use Doctrine\DBAL\Connection;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ActiveEventSubscriptionCount implements ActiveEventSubscriptionCountQuery
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fetch(): int
    {
        $query = <<<SQL
SELECT count(*)
FROM akeneo_connectivity_connection
WHERE webhook_enabled=1
SQL;

        return (int)$this->connection->query($query)->fetchColumn(0);
    }
}
