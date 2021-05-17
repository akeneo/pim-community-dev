<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SaveWebhookSecretQuery;
use Doctrine\DBAL\Connection as DbalConnection;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalSaveWebhookSecretQuery implements SaveWebhookSecretQuery
{
    private DbalConnection $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function execute(string $code, string $secret): bool
    {
        $query = <<<SQL
UPDATE akeneo_connectivity_connection
SET webhook_secret = :secret
WHERE code = :code
SQL;

        return (bool) $this->dbalConnection->executeUpdate(
            $query,
            [
                'code' => $code,
                'secret' => $secret,
            ]
        );
    }
}
