<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\GetAConnectionWebhookQuery;
use Doctrine\DBAL\Connection as DbalConnection;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalGetAConnectionWebhookQuery implements GetAConnectionWebhookQuery
{
    /** @var DbalConnection */
    private $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function execute(string $code): ?ConnectionWebhook
    {
        $query = <<<SQL
    SELECT code, webhook_secret, webhook_url, webhook_enabled
    FROM akeneo_connectivity_connection
    WHERE code = :code
SQL;
        $connectionWebhook = $this->dbalConnection->executeQuery($query, ['code' => $code])->fetch();

        if (false === $connectionWebhook) {
            return null;
        }

        return new ConnectionWebhook(
            $connectionWebhook['code'],
            (bool) $connectionWebhook['webhook_enabled'],
            $connectionWebhook['webhook_secret'],
            $connectionWebhook['webhook_url']
        );
    }
}
