<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Repository;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Write\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\ConnectionWebhookRepository;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalConnectionWebhookRepository implements ConnectionWebhookRepository
{
    /** @var DbalConnection */
    private $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function update(ConnectionWebhook $connectionWebhook): int
    {
        $query = <<<SQL
UPDATE akeneo_connectivity_connection
SET webhook_url = :url, webhook_enabled = :enabled
WHERE code = :code
SQL;

        return $this->dbalConnection->executeUpdate(
            $query,
            [
                'url' => $connectionWebhook->url(),
                'enabled' => $connectionWebhook->enabled(),
                'code' => $connectionWebhook->code(),
            ],
            [
                'enabled' => Types::BOOLEAN,
            ]
        );
    }
}
