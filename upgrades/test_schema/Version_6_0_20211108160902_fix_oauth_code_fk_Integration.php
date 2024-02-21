<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_6_0_20211108160902_fix_oauth_code_fk_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private Connection $connection;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_updates_foreign_keys_of_oauth_code_table(): void
    {
        $this->dropForeignKeyIfExists('pim_api_auth_code', 'FK_AD5DC7C619EB6921');
        $this->dropForeignKeyIfExists('pim_api_auth_code', 'FK_AD5DC7C6A76ED395');

        $this->reExecuteMigration($this->getMigrationLabel());

        Assert::assertTrue($this->foreignKeyHasDeleteCascade('pim_api_auth_code', 'FK_AD5DC7C619EB6921'));
        Assert::assertTrue($this->foreignKeyHasDeleteCascade('pim_api_auth_code', 'FK_AD5DC7C6A76ED395'));
    }

    private function dropForeignKeyIfExists(string $table, string $foreignKey): void
    {
        try {
            $this->connection->executeQuery(sprintf('ALTER TABLE %s DROP FOREIGN KEY %s', $table, $foreignKey));
        } catch (Exception $e) {
            // ignore when the foreign did not exist
        }
    }

    private function foreignKeyHasDeleteCascade(string $table, string $foreignKey): bool
    {
        $database = $this->connection->getDatabase();

        $query = <<<SQL
SELECT DELETE_RULE
FROM information_schema.REFERENTIAL_CONSTRAINTS
WHERE CONSTRAINT_NAME=:foreign_key
AND TABLE_NAME=:table
AND CONSTRAINT_SCHEMA=:database
SQL;

        $deleteRule = $this->connection->fetchOne($query, [
            'foreign_key' => $foreignKey,
            'table' => $table,
            'database' => $database,
        ]);

        return 'CASCADE' === $deleteRule;
    }

    private function getMigrationLabel(): string
    {
        $migration = (new \ReflectionClass($this))->getShortName();
        $migration = str_replace('_Integration', '', $migration);
        $migration = str_replace('Version', '', $migration);

        return $migration;
    }
}
