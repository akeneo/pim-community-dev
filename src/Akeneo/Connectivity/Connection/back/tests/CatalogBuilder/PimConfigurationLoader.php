<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\CatalogBuilder;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimConfigurationLoader
{
    public function __construct(private Connection $connection)
    {
    }

    public function addPimconfiguration(string $code, array $values): void
    {
        $query = <<<SQL
            INSERT INTO pim_configuration (`code`,`values`)
            VALUES (:configurationCode, :configurationValues)
            ON DUPLICATE KEY UPDATE `values`=:configurationValues
            SQL;

        $this->connection->executeQuery($query, [
            'configurationCode' => $code,
            'configurationValues' => $values,
        ], [
            'configurationCode' => Types::STRING,
            'configurationValues' => Types::JSON,
        ]);
    }
}
