<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectActiveWebhooksQuery;
use Akeneo\UserManagement\Component\Model\User;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\FetchMode;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectActiveWebhooksQuery implements SelectActiveWebhooksQuery
{
    private DbalConnection $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    /**
     * @return ActiveWebhook[]
     */
    public function execute(): array
    {
        $sql = <<<SQL
SELECT connection.code,
connection.webhook_url,
connection.webhook_secret,
connection.user_id,
access_group.name as group_name
FROM akeneo_connectivity_connection as connection
LEFT JOIN oro_user_access_group as user_access_group ON user_access_group.user_id = connection.user_id
LEFT JOIN oro_user_access_role as user_access_role ON user_access_role.user_id = connection.user_id
LEFT JOIN oro_access_group access_group ON user_access_group.group_id = access_group.id
WHERE connection.webhook_url IS NOT NULL AND connection.webhook_enabled = 1
ORDER BY code
SQL;
        $result = $this->dbalConnection->executeQuery($sql)->fetchAll(FetchMode::ASSOCIATIVE);

        /*
         * Filter rows to keep only one row per webhook, while priorizing the non-default user groups.
         */
        $resultFilteredByGroup = [];
        foreach ($result as $row) {
            $code = $row['code'];

            // Add webhook if it doesn't already exists.
            if (!isset($resultFilteredByGroup[$code])) {
                $resultFilteredByGroup[$code] = $row;
            }

            // Overwrite webhook to priorize the non-default user group.
            if (User::GROUP_DEFAULT === $resultFilteredByGroup[$code]['group_name']) {
                $resultFilteredByGroup[$code] = $row;
            }
        }

        $webhooks = [];
        foreach ($resultFilteredByGroup as $row) {
            $webhooks[] = new ActiveWebhook(
                $row['code'],
                (int) $row['user_id'],
                $row['webhook_secret'],
                $row['webhook_url']
            );
        }

        return $webhooks;
    }
}
