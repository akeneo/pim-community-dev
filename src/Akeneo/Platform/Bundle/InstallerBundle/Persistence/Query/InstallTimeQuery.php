<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\InstallerBundle\Persistence\Query;

use Doctrine\DBAL\Connection;

class InstallTimeQuery
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
