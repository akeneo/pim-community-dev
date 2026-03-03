<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\CatalogBuilder;

use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Types\Types;

class WebhookLoader
{
    public function __construct(private DbalConnection $dbalConnection)
    {
    }

    public function initWebhook(string $code, bool $isUsingUuid = false): void
    {
        $this->updateConnection($code, true, 'http://test.com', 'secret', $isUsingUuid);
    }

    public function updateConnection(
        string $code,
        bool $enabled = false,
        ?string $url = null,
        ?string $secret = null,
        bool $isUsingUuid = false,
    ): int {
        if ($enabled && (null === $url || '' === $url)) {
            throw new \InvalidArgumentException('An enabled webhook required an url.');
        }

        $query = <<<SQL
        UPDATE akeneo_connectivity_connection
        SET webhook_url = :url, webhook_enabled = :enabled, webhook_secret = :secret, webhook_is_using_uuid = :isUsingUuid
        WHERE code = :code
        SQL;

        return $this->dbalConnection->executeStatement(
            $query,
            [
                'url' => $url,
                'enabled' => $enabled,
                'code' => $code,
                'secret' => $secret,
                'isUsingUuid' => $isUsingUuid,
            ],
            [
                'enabled' => Types::BOOLEAN,
                'isUsingUuid' => Types::BOOLEAN,
            ]
        );
    }
}
