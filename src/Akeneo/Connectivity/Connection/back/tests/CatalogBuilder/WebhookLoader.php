<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\CatalogBuilder;

use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Types\Types;

class WebhookLoader
{
    /** @var DbalConnection */
    private $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function initWebhook(string $code): void
    {
        $this->updateConnection($code, true, 'http://test.com', 'secret');
    }

    public function updateConnection(
        string $code,
        bool $enabled = false,
        ?string $url = null,
        ?string $secret = null
    ): int {
        if ($enabled && (null === $url || '' === $url)) {
            throw new \InvalidArgumentException('An enabled webhook required an url.');
        }

        $query = <<<SQL
UPDATE akeneo_connectivity_connection
SET webhook_url = :url, webhook_enabled = :enabled, webhook_secret = :secret
WHERE code = :code
SQL;

        return $this->dbalConnection->executeUpdate(
            $query,
            [
                'url' => $url,
                'enabled' => $enabled,
                'code' => $code,
                'secret' => $secret,
            ],
            [
                'enabled' => Types::BOOLEAN,
            ]
        );
    }
}
