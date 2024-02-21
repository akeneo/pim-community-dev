<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\Persistence;

use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\CountActiveEventSubscriptionsQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalCountActiveEventSubscriptionsQuery implements CountActiveEventSubscriptionsQueryInterface
{
    public function __construct(private Connection $dbalConnection)
    {
    }

    public function execute(): int
    {
        $query = <<<SQL
        SELECT count(*)
        FROM akeneo_connectivity_connection
        WHERE webhook_enabled=1
        SQL;

        return (int) $this->dbalConnection->executeQuery($query)->fetchOne();
    }
}
