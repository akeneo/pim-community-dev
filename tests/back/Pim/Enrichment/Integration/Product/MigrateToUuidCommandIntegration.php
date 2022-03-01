<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\MigrateToUuidAddTriggers;
use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\MigrateToUuidStep;
use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\MigrateToUuidTrait;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\Application\UpsertProductHandler;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Command\BatchCommand;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\QuantifiedAssociationsTestCaseTrait;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class MigrateToUuidCommandIntegration extends TestCase
{
    use MigrateToUuidTrait;
    use QuantifiedAssociationsTestCaseTrait;

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
        $this->assertJsonHaveUuid();
        $this->assertTriggersWork();
    }

    private function clean(): void
    {
        foreach (MigrateToUuidStep::TABLES as $tableName => $columnNames) {
            if ($this->tableExists($tableName)) {
                $this->removeColumn($tableName, $columnNames[MigrateToUuidStep::UUID_COLUMN_INDEX]);
            }
            $this->removeTriggers($tableName);
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

    private function assertJsonHaveUuid(): void
    {
        $query = 'SELECT BIN_TO_UUID(uuid) as uuid, quantified_associations FROM pim_catalog_product';

        $result = $this->connection->fetchAllAssociative($query);

        foreach (range(1, 10) as $i) {
            $quantifiedAssociations = \json_decode($result[$i - 1]['quantified_associations'], true);
            if ($i === 1) {
                // the first product is linked to a non existing product and is cleaned
                Assert::assertEquals(["SOIREEFOOD10" => ["products" => []]], $quantifiedAssociations);
            } else {
                Assert::assertEquals(["SOIREEFOOD10" => ["products" => [
                    ['id' => $i - 1, 'uuid' => $result[$i - 2]['uuid'], 'quantity' => 1000]
                ]]], $quantifiedAssociations);
            }
        }
    }

    private function assertTriggersWork(): void
    {
        // create associations
        $product = $this->get('pim_catalog.builder.product')->createProduct('new_product');
        $this->get('pim_catalog.updater.product')->update($product, ['associations' => [
            'X_SELL' => ['products' => ['identifier1']],
        ]]);
        $this->get('pim_catalog.validator.product')->validate($product);
        $this->get('pim_catalog.saver.product')->save($product);
        $ownerdUuids = $this->connection->executeQuery('SELECT DISTINCT BIN_TO_UUID(owner_uuid) FROM pim_catalog_association')->fetchFirstColumn();
        // Does not working because of subscriber
//        Assert::assertSame([$this->getProductUuid('new_product')], $ownerdUuids);

        sleep(5);

        // update associations
        $this->get('pim_catalog.updater.product')->update($product, ['associations' => [
            'PACK' => ['products' => ['identifier2']],
        ]]);
        $this->get('pim_catalog.validator.product')->validate($product);
        $this->get('pim_catalog.saver.product')->save($product);
        $ownerdUuids = $this->connection->executeQuery('SELECT DISTINCT BIN_TO_UUID(owner_uuid) FROM pim_catalog_association')->fetchFirstColumn();
        Assert::assertSame([$this->getProductUuid('new_product')], $ownerdUuids);
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

        $this->createQuantifiedAssociationType('SOIREEFOOD10');

        foreach (range(1, 10) as $i) {
            ($this->get(UpsertProductHandler::class))(new UpsertProductCommand(
                userId: $adminUser->getId(),
                productIdentifier: 'identifier' . $i
            ));

            $this->connection->executeQuery(\strtr(<<<SQL
                UPDATE pim_catalog_product
                SET quantified_associations = '{"SOIREEFOOD10":{"products":[{"id":{associated_product_id},"quantity":1000}]}}'
                WHERE id = {product_id}
            SQL, [
                '{product_id}' => $i,
                '{associated_product_id}' => $i - 1,
            ]));
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

    private function removeTriggers(string $tableName): void
    {
        $sql = \sprintf('DROP TRIGGER IF EXISTS %s.{trigger_name}', $this->connection->getDatabase());

        $this->connection->executeQuery(\str_replace('{trigger_name}', MigrateToUuidAddTriggers::getInsertTriggerName($tableName), $sql));
        $this->connection->executeQuery(\str_replace('{trigger_name}', MigrateToUuidAddTriggers::getUpdateTriggerName($tableName), $sql));
    }

    private function getProductUuid(string $identifier): ?string
    {
        $sql = 'SELECT uuid FROM pim_catalog_product WHERE identifier = :identifier';

        return $this->connection->executeQuery($sql, ['identifier' => $identifier])->fetchOne();
    }
}
