<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration;

use Akeneo\Platform\Job\Infrastructure\Loader\FixturesLoader;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class IntegrationTestCase extends WebTestCase
{
    protected FixturesLoader $fixturesLoader;
    protected array $fixtures;
    private Connection $dbalConnection;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel(['debug' => false]);

        $this->dbalConnection = $this->get('database_connection');
        $this->fixturesLoader = $this->get('Akeneo\Platform\Job\Infrastructure\Loader\FixturesLoader');

        $this->resetDB();
        $this->fixtures = $this->loadFixtures();

        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    protected function get(string $service)
    {
        return self::$container->get($service);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $connectionCloser = $this->get('akeneo_integration_tests.doctrine.connection.connection_closer');
        $connectionCloser->closeConnections();

        $this->ensureKernelShutdown();
    }

    private function resetDB(): void
    {
        $resetQuery = <<<SQL
            SET foreign_key_checks = 0;

            DELETE FROM akeneo_batch_job_instance;
            DELETE FROM akeneo_batch_job_execution;
            DELETE FROM oro_user;
            DELETE FROM oro_access_group;
            DELETE FROM oro_user_access_group;

            SET foreign_key_checks = 1;
SQL;
        $this->dbalConnection->executeQuery($resetQuery);
    }

    private function loadFixtures(): array
    {
        $jobInstances = [
            'a_product_import' => $this->fixturesLoader->createJobInstance([
                'code' => 'a_product_import',
                'job_name' => 'a_product_import',
            ]),
            'another_product_import' => $this->fixturesLoader->createJobInstance([
                'code' => 'another_product_import',
                'job_name' => 'another_product_import',
            ]),
        ];
        $jobExecutions = [
            'a_job_execution' => $this->fixturesLoader->createJobExecution([
                'job_instance_id' => $jobInstances['a_product_import']
            ])
        ];

        return [
            'job_instances' => $jobInstances,
            'job_executions' => $jobExecutions,
        ];
    }
}
