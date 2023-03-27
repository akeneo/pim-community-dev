<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\InstallerBundle\Persistence\Sql;

use Akeneo\Platform\Bundle\InstallerBundle\Exception\UnavailableCreationTimeException;
use Doctrine\DBAL\Connection;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetInstallDatetime
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function __invoke(): \DateTime
    {
        $sql = <<< SQL
            SELECT `values` FROM pim_configuration WHERE code = 'install_data';
        SQL;

        $values = $this->connection->executeQuery($sql)->fetchOne();

        if (false === $values) {
            throw new UnavailableCreationTimeException('Database install time cannot be found!');
        }

        $decoded = \json_decode($values, true);

        return new \DateTime($decoded['database_installed_at']);
    }
}
