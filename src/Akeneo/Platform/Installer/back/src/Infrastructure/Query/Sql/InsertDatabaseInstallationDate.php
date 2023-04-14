<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\Query\Sql;

use Doctrine\DBAL\Connection;

final class InsertDatabaseInstallationDate
{
    public function __construct(
        private readonly Connection $connection
    ) {}

    public function withDateTime(\DateTimeImmutable $installDatetime): void
    {
        $installData = [
            'database_installed_at' => $installDatetime->format('c'),
        ];

        $sql = <<<SQL
            INSERT INTO pim_configuration (`code`, `values`) VALUES (?, ?);
        SQL;


        $this->connection->executeStatement(
            $sql,
            ['install_data', \json_encode($installData)]
        );
    }
}
