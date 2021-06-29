<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\CountActiveEventSubscriptionsQuery;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalCountActiveEventSubscriptionsQuery implements CountActiveEventSubscriptionsQuery
{
    private Connection $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function execute(): int
    {
        $query = <<<SQL
        SELECT count(*)
        FROM akeneo_connectivity_connection
        WHERE webhook_enabled=1
        SQL;

        return (int) $this->dbalConnection->query($query)->fetchColumn();
    }
}
