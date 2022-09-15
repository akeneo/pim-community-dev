<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\Persistence;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Write\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\UpdateConnectionWebhookQueryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateConnectionWebhookQuery implements UpdateConnectionWebhookQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(ConnectionWebhook $connectionWebhook): int
    {
        $query = <<<SQL
        UPDATE akeneo_connectivity_connection
        SET webhook_url = :url, webhook_enabled = :enabled, webhook_is_using_uuid = :is_using_uuid
        WHERE code = :code
        SQL;

        return $this->connection->executeStatement(
            $query,
            [
                'url' => $connectionWebhook->url(),
                'enabled' => $connectionWebhook->enabled(),
                'code' => $connectionWebhook->code(),
                'is_using_uuid' => $connectionWebhook->isUsingUuid(),
            ],
            [
                'enabled' => Types::BOOLEAN,
                'is_using_uuid' => Types::BOOLEAN,
            ]
        );
    }
}
