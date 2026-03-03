<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Test\Integration\Query;

use Akeneo\Platform\Installer\Infrastructure\Query\UpdateMaintenanceMode;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateMaintenanceModeTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_maintenance_mode_when_configuration_key_does_not_exist(): void
    {
        $this->dropMaintenanceModeConfiguration();
        $this->getQuery()->execute(true);
        $this->assertMaintenanceModeIsEnabled();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function dropMaintenanceModeConfiguration(): void
    {
        $this->getConnection()->executeQuery(
            'DELETE FROM pim_configuration WHERE code = :code',
            ['code' => 'maintenance_mode'],
        );
    }

    private function assertMaintenanceModeIsEnabled(): void
    {
        $result = $this->getConnection()->executeQuery(
            'SELECT `values` FROM pim_configuration WHERE code = :code',
            ['code' => 'maintenance_mode'],
        );
        $this->assertEquals(true, $result->fetchOne());
    }

    private function getQuery(): UpdateMaintenanceMode
    {
        return $this->get('Akeneo\Platform\Installer\Infrastructure\Query\UpdateMaintenanceMode');
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }
}
