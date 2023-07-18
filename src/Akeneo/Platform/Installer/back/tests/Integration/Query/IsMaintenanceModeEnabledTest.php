<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Test\Integration\Query;

use Akeneo\Platform\Installer\Infrastructure\Query\IsMaintenanceModeEnabled;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class IsMaintenanceModeEnabledTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_false_when_maintenance_mode_configuration_key_does_not_exist(): void
    {
        $isMaintenanceModeEnabled = $this->getQuery()->execute();

        $this->assertFalse($isMaintenanceModeEnabled);
    }

    /**
     * @test
     */
    public function it_returns_true_when_maintenance_mode_is_enabled(): void
    {
        $this->enableMaintenanceMode();
        $isMaintenanceModeEnabled = $this->getQuery()->execute();

        $this->assertTrue($isMaintenanceModeEnabled);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getQuery(): IsMaintenanceModeEnabled
    {
        return $this->get('Akeneo\Platform\Installer\Infrastructure\Query\IsMaintenanceModeEnabled');
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function enableMaintenanceMode(): void
    {
        $query = <<<SQL
            INSERT INTO pim_configuration (`code`,`values`)
            VALUES (:code, :values)
            ON DUPLICATE KEY UPDATE `values`= :values
        SQL;

        $this->getConnection()->executeQuery($query, [
            'code' => 'maintenance_mode',
            'values' => ['enabled' => true],
        ], [
            'code' => Types::STRING,
            'values' => Types::JSON,
        ]);
    }
}
