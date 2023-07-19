<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Test\Integration\EventSubscriber;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MaintenanceModeSubscriberIntegration extends TestCase
{
    private Connection $connection;
    private EventDispatcherInterface $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
        $this->eventDispatcher = $this->get('event_dispatcher');
    }

    public function test_it_enables_maintenance_mode_before_resseting(): void
    {
        $this->disableMaintenanceMode();
        $this->eventDispatcher->dispatch(new InstallerEvent(), InstallerEvents::PRE_RESET_INSTANCE);
        $this->assertMaintenanceModeIsEnabled();
    }

    public function test_it_disables_maintenance_mode_after_resseting(): void
    {
        $this->enableMaintenanceMode();
        $this->eventDispatcher->dispatch(new InstallerEvent(), InstallerEvents::POST_RESET_INSTANCE);
        $this->assertMaintenanceModeIsDisabled();
    }

    private function assertMaintenanceModeIsEnabled(): void
    {
        $this->assertTrue($this->isMaintenanceModeEnabled());
    }

    private function assertMaintenanceModeIsDisabled(): void
    {
        $this->assertFalse($this->isMaintenanceModeEnabled());
    }

    private function isMaintenanceModeEnabled(): bool
    {
        $result = $this->connection->fetchOne("SELECT `values` FROM pim_configuration WHERE code = 'maintenance_mode'");

        return (false === $result) ? false : json_decode($result, true)['enabled'];
    }

    private function enableMaintenanceMode(): void
    {
        $this->updateMaintenanceMode(true);
    }

    private function disableMaintenanceMode(): void
    {
        $this->updateMaintenanceMode(false);
    }

    private function updateMaintenanceMode(bool $enabled): void
    {
        $query = <<<SQL
            INSERT INTO pim_configuration (`code`,`values`)
            VALUES (:code, :values)
            ON DUPLICATE KEY UPDATE `values`= :values
        SQL;

        $this->connection->executeStatement($query, [
            'code' => 'maintenance_mode',
            'values' => ['enabled' => $enabled],
        ], [
            'code' => Types::STRING,
            'values' => Types::JSON,
        ]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
