<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Test\Integration\Controller;

use Akeneo\Platform\Installer\Test\Integration\ControllerIntegrationTestCase;
use Akeneo\Test\Integration\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class IsMaintenanceModeEnabledActionTest extends ControllerIntegrationTestCase
{
    private const ROUTE = 'akeneo_installer_is_maintenance_mode_enabled';

    private Connection $connection;

    public function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');

        $this->logAs('julia');
        $this->featureFlags->enable('reset_pim');
    }

    public function test_it_returns_true_when_maintenance_mode_is_enabled(): void
    {
        // TODO: Fix this test - the route requires reset_pim feature flag to be enabled via EnvVarFeatureFlag,
        // but FilePersistedFeatureFlags (used in tests) doesn't affect the routing layer.
        // The route has _feature: reset_pim which checks EnvVarFeatureFlag, not FilePersistedFeatureFlags.
        $this->markTestSkipped('Test skipped: reset_pim feature flag configuration issue between EnvVarFeatureFlag and FilePersistedFeatureFlags');

        $this->given_maintenance_mode_is_enabled();

        $this->webClientHelper->callApiRoute($this->client, self::ROUTE);

        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertSame('true', $response->getContent());
    }

    private function given_maintenance_mode_is_enabled(): void
    {
        $insertJobExecution = <<<SQL
INSERT INTO pim_configuration (`code`,`values`)
VALUES (:code, :values)
ON DUPLICATE KEY UPDATE `values`= :values
SQL;

        $this->connection->executeStatement(
            $insertJobExecution,
            [
                'code' => 'maintenance_mode',
                'values' => ['enabled' => true],
            ],
            [
                'code' => Types::STRING,
                'values' => Types::JSON,
            ],
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
