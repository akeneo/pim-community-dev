<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\Persistence;

use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectWebhookSecretQueryInterface;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\FetchMode;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalSelectWebhookSecretQuery implements SelectWebhookSecretQueryInterface
{
    public function __construct(private DbalConnection $dbalConnection)
    {
    }

    public function execute(string $code): ?string
    {
        $query = <<<SQL
        SELECT webhook_secret
        FROM akeneo_connectivity_connection
        WHERE code = :code
SQL;

        $result = $this->dbalConnection->executeQuery($query, ['code' => $code])->fetchOne();

        return false === $result ? null : $result;
    }
}
