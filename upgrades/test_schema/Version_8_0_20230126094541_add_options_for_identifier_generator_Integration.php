<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230126094541_add_options_for_identifier_generator_Integration extends TestCase
{
    private const MIGRATION_NAME = '_8_0_20230126094541_add_options_for_identifier_generator';

    use ExecuteMigrationTrait;

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_executes_the_migration(): void
    {
        $this->connection->executeQuery(<<<SQL
ALTER TABLE pim_catalog_identifier_generator ADD COLUMN delimiter VARCHAR(100) AFTER options;
ALTER TABLE pim_catalog_identifier_generator DROP COLUMN options;
SQL);
        $this->connection->executeQuery(<<<SQL
INSERT INTO pim_catalog_identifier_generator (uuid, code, target_id, delimiter, labels, conditions, structure)
VALUES (UUID_TO_BIN('22e35c7a-f1b4-4b81-a890-16b8e68346a1'), 'mygenerator1', (SELECT id FROM pim_catalog_attribute WHERE pim_catalog_attribute.code='sku'), '-', '{}', '[]', '[]'),
       (UUID_TO_BIN('d4d21fcd-37cf-4c8a-937d-a7dee0e61ec1'), 'mygenerator2', (SELECT id FROM pim_catalog_attribute WHERE pim_catalog_attribute.code='sku'), '=', '{}', '[]', '[]'),
       (UUID_TO_BIN('1113c7f8-70cc-45d3-b911-54caac0e12e5'), 'mygenerator3', (SELECT id FROM pim_catalog_attribute WHERE pim_catalog_attribute.code='sku'), null, '{}', '[]', '[]');
SQL);

        Assert::assertFalse($this->columnExists('pim_catalog_identifier_generator', 'options'));
        $this->reExecuteMigration(self::MIGRATION_NAME);
        Assert::assertTrue($this->columnExists('pim_catalog_identifier_generator', 'options'));
        Assert::assertEqualsCanonicalizing([
            ['generator' => '-', 'text_transformation' => 'no'],
            ['generator' => '=', 'text_transformation' => 'no'],
            ['generator' => null, 'text_transformation' => 'no'],
        ], $this->getOptions());
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        $rows = $this->connection->fetchAllAssociative(
            \strtr(
                <<<SQL
                    SHOW COLUMNS FROM {table_name} LIKE :columnName
                SQL,
                ['{table_name}' => $tableName]
            ),
            ['columnName' => $columnName]
        );

        return count($rows) >= 1;
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @return array[]
     */
    private function getOptions(): array
    {
        return array_map(fn (string $options) => \json_decode($options, true),
            $this->connection->fetchFirstColumn(<<<SQL
                SELECT options FROM pim_catalog_identifier_generator;
            SQL)
        );
    }
}
