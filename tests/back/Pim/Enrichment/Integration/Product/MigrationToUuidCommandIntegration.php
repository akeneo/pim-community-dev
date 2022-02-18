<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuidStep;
use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuidTrait;
use Akeneo\Pim\Enrichment\Product\Api\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\Application\UpsertProductHandler;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Command\BatchCommand;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class MigrationToUuidCommandIntegration extends TestCase
{
    use MigrateToUuidTrait;

    private Connection $connection;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /** @test */
    public function it_migrates_the_database_to_use_uuid(): void
    {
        $this->connection = $this->get('database_connection');
        $this->clean();
        $this->loadFixtures();

        $this->assertTheColumnsDoNotExist();
        $this->launchMigrationCommand();
        $this->assertTheColumnsExist();
        $this->assertAllProductsHaveUuid();
    }

    private function clean(): void
    {
        foreach (MigrateToUuidStep::TABLES as $tableName => $columnNames) {
            if ($this->tableExists($tableName)) {
                $this->removeColumn($tableName, $columnNames[MigrateToUuidStep::UUID_COLUMN_INDEX]);
            }
        }
    }

    private function launchMigrationCommand(): void
    {
        $application = new Application($this->get('kernel'));
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'pim:product:migrate-to-uuid',
            '-v' => true,
        ]);
        $output = new BufferedOutput();
        $exitCode = $application->run($input, $output);

        if (BatchCommand::EXIT_SUCCESS_CODE !== $exitCode) {
            throw new \Exception(sprintf('Command failed: %s.', $output->fetch()));
        }
    }

    private function assertTheColumnsDoNotExist(): void
    {
        foreach (MigrateToUuidStep::TABLES as $tableName => $columnNames) {
            if ($this->tableExists($tableName)) {
                Assert::assertFalse(
                    $this->columnExists($tableName, $columnNames[MigrateToUuidStep::UUID_COLUMN_INDEX]),
                    \sprintf('The "%s" column exists in the "%s" table', $columnNames[MigrateToUuidStep::UUID_COLUMN_INDEX], $tableName)
                );
            }
        }
    }

    private function assertTheColumnsExist(): void
    {
        foreach (MigrateToUuidStep::TABLES as $tableName => $columnNames) {
            if ($this->tableExists($tableName)) {
                Assert::assertTrue(
                    $this->columnExists($tableName, $columnNames[MigrateToUuidStep::UUID_COLUMN_INDEX]),
                    \sprintf('The "%s" column does not exist in the "%s" table', $columnNames[MigrateToUuidStep::UUID_COLUMN_INDEX], $tableName)
                );
            }
        }
    }

    private function assertAllProductsHaveUuid(): void
    {
        $query = 'SELECT count(*) FROM pim_catalog_product WHERE uuid IS NULL';

        $result = (int) $this->connection->executeQuery($query)->fetchOne();

        Assert::assertSame(0, $result, \sprintf('%s product(s) does not have an uuid after migration.', $result));
    }

    private function removeColumn(string $tableName, string $columnName): void
    {
        if ($this->tableExists($tableName) && $this->columnExists($tableName, $columnName)) {
            $this->connection->executeQuery(\sprintf('ALTER TABLE %s DROP COLUMN %s', $tableName, $columnName));
        }
    }

    private function loadFixtures(): void
    {
        $adminUser = $this->createAdminUser();
        foreach (range(1, 10) as $i) {
            ($this->get(UpsertProductHandler::class))(new UpsertProductCommand(
                userId: $adminUser->getId(),
                productIdentifier: 'identifier' . $i
            ));
        }
    }

    private function tableExists(string $tableName): bool
    {
        $rows = $this->connection->fetchAllAssociative(
            'SHOW TABLES LIKE :tableName',
            ['tableName' => $tableName]
        );

        return count($rows) >= 1;
    }
}
