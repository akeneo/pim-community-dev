<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\InstallerBundle\Persistence\Query;

use Doctrine\DBAL\Connection;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InstallTimeQuery
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function setInstallTime(): void
    {
        $installData = [
            'database_installed_at' => (new \DateTimeImmutable())->format('c'),
        ];

        $sql = 'INSERT INTO pim_configuration (`code`, `values`) VALUES (?, ?)';

        $this->connection->executeStatement(
            $sql,
            ['install_data', \json_encode($installData)]
        );
    }
}
