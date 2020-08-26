<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectConnectionsWebhookQuery;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\FetchMode;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectConnectionsWebhookQuery implements SelectConnectionsWebhookQuery
{
    /** @var DbalConnection */
    private $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    /**
     * @return ConnectionWebhook[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function execute(): array
    {
        $sql = <<<SQL
SELECT connection.code, connection.webhook_url, connection.webhook_secret, access_group.group_id, access_role.role_id
FROM akeneo_connectivity_connection as connection
LEFT JOIN oro_user_access_group as access_group ON access_group.user_id = connection.user_id 
LEFT JOIN oro_user_access_role as access_role ON access_role.user_id = connection.user_id 
WHERE connection.webhook_url IS NOT NULL AND connection.webhook_enabled = 1 
ORDER BY code
SQL;

        $rawWebhooks = $this->dbalConnection->executeQuery($sql)->fetchAll(FetchMode::ASSOCIATIVE);
        $webhooks = [];
        foreach ($rawWebhooks as $rawWebhook) {
            $webhooks[] = new ConnectionWebhook(
                $rawWebhook['code'],
                $rawWebhook['userGroup'],
                $rawWebhook['userRole'],
                $rawWebhook['secret'],
                $rawWebhook['url']
            );
        }

        return $webhooks;
    }
}
