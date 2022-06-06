<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Test\Integration;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * This class is used for running integration tests.
 * It should be used for testing the SQL implementation of query functions and repositories.
 */
abstract class SqlIntegrationTestCase extends KernelTestCase
{
    protected Connection $connection;

    protected function setUp(): void
    {
        static::bootKernel(['debug' => false]);
        $this->connection = $this->get('doctrine.dbal.default_connection');

        $this->connection->executeStatement(<<<SQL
            DELETE FROM `akeneo_onboarder_serenity_supplier`;
            DELETE FROM `akeneo_onboarder_serenity_supplier_contributor`;
            DELETE FROM `akeneo_batch_job_execution`;
            DELETE FROM `akeneo_onboarder_serenity_contributor_account`;
        SQL);

        $this->addOnboarderSerenityXlsxSupplierImportJob();
    }

    protected function get(string $service): ?object
    {
        return static::getContainer()->get($service);
    }

    protected function tearDown(): void
    {
        $connectionCloser = $this->get('akeneo_integration_tests.doctrine.connection.connection_closer');
        $connectionCloser->closeConnections();

        $this->ensureKernelShutdown();
    }

    private function addOnboarderSerenityXlsxSupplierImportJob(): void
    {
        if ($this->onboarderSerenityXlsxSupplierImportJobExists()) {
            return;
        }

        $sql = <<<SQL
            INSERT INTO akeneo_batch_job_instance (code, label, job_name, status, connector, raw_parameters, type)
            VALUES (:code, :label, :code, 0, :connector, :rawParameters, :type);
        SQL;

        $this->connection->executeStatement(
            $sql,
            [
                'code' => 'onboarder_serenity_xlsx_supplier_import',
                'label' => 'Onboarder Serenity XLSX Supplier Import',
                'connector' => 'Onboarder Serenity',
                'rawParameters' => 'a:0:{}',
                'type' => 'import',
            ],
        );
    }

    private function onboarderSerenityXlsxSupplierImportJobExists(): bool
    {
        $sql = <<<SQL
            SELECT COUNT(*)
            FROM `akeneo_batch_job_instance`
            WHERE code = :code
        SQL;

        return 1 === (int) $this
                ->connection
                ->executeQuery($sql, ['code' => 'onboarder_serenity_xlsx_supplier_import'])
                ->fetchOne()
            ;
    }
}
