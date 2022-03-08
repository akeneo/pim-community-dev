<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product\UuidMigration;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\MigrateToUuidAddTriggers;
use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\MigrateToUuidStep;
use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\MigrateToUuidTrait;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Command\BatchCommand;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\QuantifiedAssociationsTestCaseTrait;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

abstract class AbstractMigrateToUuidTestCase extends TestCase
{
    use MigrateToUuidTrait;
    use QuantifiedAssociationsTestCaseTrait;

    protected Connection $connection;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->clean();
    }

    protected function clean(): void
    {
        foreach (MigrateToUuidStep::TABLES as $tableName => $columnNames) {
            if ($this->tableExists($tableName)) {
                $this->removeColumn($tableName, $columnNames[MigrateToUuidStep::UUID_COLUMN_INDEX]);
            }
            $this->removeTriggers($tableName);
        }
    }

    protected function launchMigrationCommand(): void
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

    protected function removeColumn(string $tableName, string $columnName): void
    {
        if ($this->tableExists($tableName) && $this->columnExists($tableName, $columnName)) {
            $this->connection->executeQuery(\sprintf('ALTER TABLE %s DROP COLUMN %s', $tableName, $columnName));
        }
    }

    protected function tableExists(string $tableName): bool
    {
        $rows = $this->connection->fetchAllAssociative(
            'SHOW TABLES LIKE :tableName',
            ['tableName' => $tableName]
        );

        return \count($rows) >= 1;
    }

    protected function removeTriggers(string $tableName): void
    {
        $sql = \sprintf('DROP TRIGGER IF EXISTS %s.{trigger_name}', $this->connection->getDatabase());

        $this->connection->executeQuery(\str_replace('{trigger_name}', MigrateToUuidAddTriggers::getInsertTriggerName($tableName), $sql));
        $this->connection->executeQuery(\str_replace('{trigger_name}', MigrateToUuidAddTriggers::getUpdateTriggerName($tableName), $sql));
    }

    protected function getProductUuid(string $identifier): ?string
    {
        $sql = 'SELECT BIN_TO_UUID(uuid) FROM pim_catalog_product WHERE identifier = :identifier';

        return $this->connection->executeQuery($sql, ['identifier' => $identifier])->fetchOne();
    }

    protected function createProductGroup(array $data): void
    {
        $group = $this->get('pim_catalog.factory.group')->create();
        $this->get('pim_catalog.updater.group')->update($group, $data);
        $violations = $this->get('validator')->validate($group);
        Assert::assertCount(0, $violations, (string) $violations);
        $this->get('pim_catalog.saver.group')->save($group);
    }

    protected function createFamilyVariant(array $data): FamilyVariantInterface
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, $data);

        $violations = $this->get('validator')->validate($familyVariant);
        Assert::assertCount(0, $violations, (string) $violations);
        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);

        return $familyVariant;
    }

    protected function createFamily(array $data): FamilyInterface
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $data);
        $violations = $this->get('validator')->validate($family);
        Assert::assertCount(0, $violations, (string) $violations);
        $this->get('pim_catalog.saver.family')->save($family);

        return $family;
    }

    protected function createAttribute(array $data = []): AttributeInterface
    {
        $data['group'] = $data['group'] ?? 'other';
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $violations, (string) $violations);
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }
}
