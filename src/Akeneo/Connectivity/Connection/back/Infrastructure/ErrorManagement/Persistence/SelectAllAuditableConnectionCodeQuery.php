<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\Persistence;

use Doctrine\DBAL\Connection as DbalConnection;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SelectAllAuditableConnectionCodeQuery
{
    public function __construct(private DbalConnection $dbalConnection)
    {
    }

    public function execute(): array
    {
        $selectSQL = <<<SQL
SELECT code
FROM akeneo_connectivity_connection
WHERE auditable = 1
SQL;

        return $this->dbalConnection->executeQuery($selectSQL)->fetchFirstColumn();
    }
}
