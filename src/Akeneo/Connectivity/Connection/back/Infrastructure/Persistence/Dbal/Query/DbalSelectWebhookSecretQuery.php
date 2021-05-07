<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectWebhookSecretQuery;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\FetchMode;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalSelectWebhookSecretQuery implements SelectWebhookSecretQuery
{
    /** @var DbalConnection */
    private $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function execute(string $code): ?string
    {
        $query = <<<SQL
        SELECT webhook_secret
        FROM akeneo_connectivity_connection
        WHERE code = :code
SQL;

        $result = $this->dbalConnection->executeQuery($query, ['code' => $code])->fetch(FetchMode::COLUMN);

        return false === $result ? null : $result;
    }
}
