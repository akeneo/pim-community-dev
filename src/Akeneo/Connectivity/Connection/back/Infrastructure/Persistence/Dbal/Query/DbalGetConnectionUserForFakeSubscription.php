<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\GetConnectionUserForFakeSubscription;
use Doctrine\DBAL\Connection as DbalConnection;

class DbalGetConnectionUserForFakeSubscription implements GetConnectionUserForFakeSubscription
{
    private DbalConnection $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function execute(): ?int
    {
        $query = <<<SQL
    SELECT user_id
    FROM akeneo_connectivity_connection
    ORDER BY code
    LIMIT 1
SQL;
        $result = $this->dbalConnection->executeQuery($query)->fetchColumn();

        if (null === $result) {
            return null;
        }

        return (int)$result;
    }
}
