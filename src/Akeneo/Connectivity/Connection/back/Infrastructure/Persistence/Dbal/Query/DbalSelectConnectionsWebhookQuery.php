<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query;

use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\FetchMode;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectConnectionsWebhookQuery
{
    /** @var DbalConnection */
    private $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    /**
     * @return array<array{code: string, webhook: string}>
     */
    public function execute(): array
    {
        $sql = <<<SQL
SELECT code, webhook_url, webhook_secret
FROM akeneo_connectivity_connection
WHERE webhook_url IS NOT NULL AND webhook_enabled = 1
SQL;

        return $this->dbalConnection->executeQuery($sql)->fetchAll(FetchMode::ASSOCIATIVE);
    }
}
