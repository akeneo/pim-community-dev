<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Infrastructure\Persistence\Sql;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InstallData
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function withDatetime(DateTimeImmutable $installDatetime): void
    {
        $installData = [
            'database_installed_at' => $installDatetime->format('c'),
        ];

        $sql = 'INSERT INTO pim_configuration (`code`, `values`) VALUES (?, ?)';

        $this->connection->executeStatement(
            $sql,
            ['install_data', \json_encode($installData)]
        );
    }
}
