<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\InstallerBundle\Persistence\Sql;

use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetResetData
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function __invoke(): ?array
    {
        $sql = <<< SQL
            SELECT `values` FROM pim_configuration WHERE code = 'reset_data';
        SQL;

        $values = $this->connection->executeQuery($sql)->fetchOne();

        if (false === $values) {
            return null;
        }

        return \json_decode($values, true);
    }
}
