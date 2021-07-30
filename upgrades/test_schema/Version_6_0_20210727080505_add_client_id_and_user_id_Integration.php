<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_6_0_20210727080505_add_client_id_and_user_id_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20210727080505_add_client_id_and_user_id';

    private Connection $connection;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_adds_client_id_and_user_id_columns_to_pim_api_auth_code_table(): void
    {
        $this->prepareTable();

        Assert::assertFalse($this->columnExist('client_id'));
        Assert::assertFalse($this->columnExist('user_id'));
        Assert::assertFalse($this->foreignKeyConstraintExists('client_id','pim_api_client','id'));
        Assert::assertFalse($this->foreignKeyConstraintExists('user_id','oro_user','id'));
        Assert::assertFalse($this->indexExist('client_id'));
        Assert::assertFalse($this->indexExist('user_id'));

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        Assert::assertTrue($this->columnExist('client_id'));
        Assert::assertTrue($this->columnExist('user_id'));
        Assert::assertTrue($this->indexExist('client_id'));
        Assert::assertTrue($this->indexExist('user_id'));
        Assert::assertTrue($this->foreignKeyConstraintExists('client_id','pim_api_client','id'));
        Assert::assertTrue($this->foreignKeyConstraintExists('user_id','oro_user','id'));
    }

    private function prepareTable(): void
    {
        if ($this->columnExist('client_id')) {
            $this->connection->executeQuery("ALTER TABLE pim_api_auth_code DROP FOREIGN KEY FK_AD5DC7C619EB6921;");
            $this->connection->executeQuery('ALTER TABLE pim_api_auth_code DROP COLUMN client_id;');
        }

        if ($this->columnExist('user_id')) {
            $this->connection->executeQuery("ALTER TABLE pim_api_auth_code DROP FOREIGN KEY FK_AD5DC7C6A76ED395;");
            $this->connection->executeQuery('ALTER TABLE pim_api_auth_code DROP COLUMN user_id;');
        }
    }

    private function columnExist($columnName): bool
    {
        $columns = $this->connection->getSchemaManager()->listTableColumns('pim_api_auth_code');
        return isset($columns[$columnName]);
    }

    private function foreignKeyConstraintExists($columnName, $referenceTable, $referenceColumn): bool
    {
        $foreignKeyConstraints = $this->connection->getSchemaManager()->listTableForeignKeys('pim_api_auth_code');
        foreach ($foreignKeyConstraints as $constraint) {
            if (
                in_array($columnName, $constraint->getLocalColumns(), true)
                && in_array($referenceColumn, $constraint->getForeignColumns(), true)
                && $referenceTable === $constraint->getForeignTableName()
            ) {
                return true;
            }
        }

        return false;
    }

    private function indexExist($columnName): bool
    {
        $indexes = $this->connection->getSchemaManager()->listTableIndexes('pim_api_auth_code');
        foreach ($indexes as $index) {
            if (in_array($columnName, $index->getColumns(), true)) {
                return  true;
            }
        }

        return  false;
    }
}
