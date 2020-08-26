<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectConnectionsWebhookQuery;
use Akeneo\UserManagement\Component\Model\User;
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
SELECT connection.code, connection.webhook_url, connection.webhook_secret, user_access_group.group_id, user_access_role.role_id
FROM akeneo_connectivity_connection as connection
LEFT JOIN oro_user_access_group as user_access_group ON user_access_group.user_id = connection.user_id 
LEFT JOIN oro_user_access_role as user_access_role ON user_access_role.user_id = connection.user_id 
LEFT JOIN oro_access_group access_group ON user_access_group.group_id = access_group.id
    AND user_access_group.name <> :default_group
WHERE connection.webhook_url IS NOT NULL AND connection.webhook_enabled = 1 
ORDER BY code
SQL;

        $rawWebhooks = $this->dbalConnection->executeQuery(
            $sql,
            [
                'default_group' => User::GROUP_DEFAULT,
            ]
        )->fetchAll(FetchMode::ASSOCIATIVE);

        dump($rawWebhooks);

        // If there is more than one line, remove the one with the default user group (null).
        if (count($rawWebhooks) > 1) {
            $rawWebhooks = array_filter($rawWebhooks, function (array $row) {
                return null !== $row['group_id'];
            });
        }

        $webhooks = [];
        foreach ($rawWebhooks as $rawWebhook) {
            $webhooks[] = new ConnectionWebhook(
                $rawWebhook['code'],
                $rawWebhook['group_id'],
                $rawWebhook['role_id'],
                $rawWebhook['webhook_secret'],
                $rawWebhook['webhook_url']
            );
        }

        return $webhooks;
    }
}
