<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\Query;

use Akeneo\Platform\Installer\Domain\Query\IsMaintenanceModeEnabledInterface;
use Doctrine\DBAL\Connection;

class IsMaintenanceModeEnabled implements IsMaintenanceModeEnabledInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function execute(): bool
    {
        $query = <<<SQL
SELECT `values`
FROM pim_configuration
WHERE code = 'maintenance_mode'
SQL;
        $result = $this->connection->fetchOne($query);

        if (false === $result) {
            return false;
        }

        return json_decode((string) $result, true, 512, JSON_THROW_ON_ERROR)['enabled'];
    }
}
