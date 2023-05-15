<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230511113912_fix_oro_access_tables_columns_length_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_8_0_20230511113912_fix_oro_access_tables_columns_length';

    private Connection $connection;

    /** @test */
    public function it_does_not_change_column_length_if_already_correct(): void
    {
        Assert::assertEquals(255, $this->countColumnLength('oro_access_role', 'label'));
        Assert::assertEquals(255, $this->countColumnLength('oro_access_role', 'role'));
        Assert::assertEquals(255, $this->countColumnLength('oro_access_group', 'name'));

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertEquals(255, $this->countColumnLength('oro_access_role', 'label'));
        Assert::assertEquals(255, $this->countColumnLength('oro_access_role', 'role'));
        Assert::assertEquals(255, $this->countColumnLength('oro_access_group', 'name'));
    }

    /** @test */
    public function it_updates_column_length(): void
    {
        Assert::assertEquals(255, $this->countColumnLength('oro_access_role', 'label'));
        Assert::assertEquals(255, $this->countColumnLength('oro_access_role', 'role'));
        Assert::assertEquals(255, $this->countColumnLength('oro_access_group', 'name'));

        $this->connection->executeStatement(<<<SQL
            ALTER TABLE oro_access_role 
                MODIFY label VARCHAR(30) NOT NULL,
                MODIFY role VARCHAR(30) NOT NULL
            ;
        SQL);
        $this->connection->executeStatement(<<<SQL
            ALTER TABLE akeneo_connectivity_connected_app 
                DROP CONSTRAINT FK_CONNECTIVITY_CONNECTED_APP_user_group_name
            ;
            SQL
        );
        $this->connection->executeStatement(<<<SQL
            ALTER TABLE oro_access_group MODIFY name VARCHAR(30) NOT NULL;
        SQL);
        $this->connection->executeStatement(<<<SQL
            ALTER TABLE akeneo_connectivity_connected_app 
                ADD CONSTRAINT FK_CONNECTIVITY_CONNECTED_APP_user_group_name FOREIGN KEY (user_group_name) REFERENCES oro_access_group (name);
            SQL
        );
        Assert::assertEquals(30, $this->countColumnLength('oro_access_role', 'label'));
        Assert::assertEquals(30, $this->countColumnLength('oro_access_role', 'role'));
        Assert::assertEquals(30, $this->countColumnLength('oro_access_group', 'name'));

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertEquals(255, $this->countColumnLength('oro_access_role', 'label'));
        Assert::assertEquals(255, $this->countColumnLength('oro_access_role', 'role'));
        Assert::assertEquals(255, $this->countColumnLength('oro_access_group', 'name'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function countColumnLength(string $tableName, string $columnName): int
    {
        $maxLength = $this->connection->executeQuery(
            <<<SQL
                SELECT CHARACTER_MAXIMUM_LENGTH 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA ='akeneo_pim_test' 
                AND TABLE_NAME = :tableName
                AND COLUMN_NAME = :columnName
                LIMIT 1;
            SQL,
            [
                'tableName' => $tableName,
                'columnName' => $columnName,
            ]
            )->fetchOne();

        return (int) $maxLength;
    }
}
