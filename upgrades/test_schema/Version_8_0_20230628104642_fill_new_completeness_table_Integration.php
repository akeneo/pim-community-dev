<?php

declare(strict_types=1);


namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\Exception\SkipMigration;
use PHPUnit\Framework\Assert;
use Pim\Upgrade\Schema\Version_8_0_20230628104642_fill_new_completeness_table;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230628104642_fill_new_completeness_table_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_8_0_20230628104642_fill_new_completeness_table';
    private const PRODUCT_UUID = '3618bf0c-cee5-4d24-8802-6a97faa0356c';

    private Connection $connection;

    /** @test */
    public function it_skips_the_migration_if_the_new_table_does_not_exist(): void
    {
        $this->connection->executeStatement('DROP TABLE pim_catalog_product_completeness');
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertFalse(
            $this->connection->fetchOne(
                'SELECT * FROM migration_versions WHERE version = :migration',
                ['migration' => Version_8_0_20230628104642_fill_new_completeness_table::class]
            )
        );
    }

    /** @test */
    public function it_runs_the_migration_if_there_is_no_row_in_the_new_completeness_table(): void
    {
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertSame(
            '{}',
            $this->connection->fetchOne('SELECT completeness FROM pim_catalog_product_completeness')
        );
    }

    /** @test */
    public function it_runs_the_migration_if_new_table_is_empty(): void
    {
        $this->connection->executeStatement('DELETE FROM pim_catalog_product_completeness');
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertNotFalse(
            $this->connection->fetchOne(
                'SELECT * FROM migration_versions WHERE version = :migration',
                ['migration' => Version_8_0_20230628104642_fill_new_completeness_table::class]
            )
        );
        Assert::assertJsonStringEqualsJsonString(
            json_encode(['ecommerce' => ['en_US' => ['required' => 6, 'missing' => 5]]]),
            $this->connection->fetchOne(
                'SELECT completeness FROM pim_catalog_product_completeness WHERE product_uuid = :uuid',
                ['uuid' => Uuid::fromString(self::PRODUCT_UUID)->getBytes()],
                ['uuid' => Types::BINARY]
            )
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
        $this->loadFixtures();
    }

    protected function tearDown(): void
    {
        $this->createNewTableIfNotExists();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function loadFixtures(): void
    {
        $this->createLegacyTableIfNotExists();
        $this->createNewTableIfNotExists();
        $this->insertProductData();
    }

    private function createLegacyTableIfNotExists(): void
    {
        $this->connection->executeStatement(
            <<<SQL
            CREATE TABLE IF NOT EXISTS `pim_catalog_completeness` (
              `id` bigint NOT NULL AUTO_INCREMENT,
              `locale_id` int NOT NULL,
              `channel_id` int NOT NULL,
              `product_uuid` binary(16) NOT NULL,
              `missing_count` int NOT NULL,
              `required_count` int NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `channel_locale_product_unique_idx` (`channel_id`,`locale_id`,`product_uuid`),
              KEY `IDX_113BA854E559DFD1` (`locale_id`),
              KEY `IDX_113BA85472F5A1AA` (`channel_id`),
              KEY `product_uuid` (`product_uuid`),
              CONSTRAINT `FK_113BA85472F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `pim_catalog_channel` (`id`) ON DELETE CASCADE,
              CONSTRAINT `FK_113BA854E559DFD1` FOREIGN KEY (`locale_id`) REFERENCES `pim_catalog_locale` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL
        );
    }

    private function createNewTableIfNotExists(): void
    {
        $this->connection->executeStatement(
            <<<SQL
            CREATE TABLE IF NOT EXISTS pim_catalog_product_completeness(
                `product_uuid` binary(16) NOT NULL,
                `completeness` JSON NOT NULL DEFAULT (JSON_OBJECT()),
                PRIMARY KEY (`product_uuid`),
                CONSTRAINT `FK_PRODUCTUUID_COMPLETENESS` FOREIGN KEY (`product_uuid`) REFERENCES `pim_catalog_product` (`uuid`) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
            SQL
        );
    }

    private function insertProductData(): void
    {
        $this->connection->executeStatement(
            <<<SQL
            INSERT INTO pim_catalog_product(uuid, raw_values, is_enabled, created, updated)
            VALUES (:uuid, JSON_OBJECT(), 1, NOW(), NOW())
            SQL,
            ['uuid' => Uuid::fromString(self::PRODUCT_UUID)->getBytes()],
            ['uuid' => Types::BINARY]
        );
        $this->connection->executeStatement(
            <<<SQL
            INSERT INTO pim_catalog_completeness(product_uuid, channel_id, locale_id, missing_count, required_count)
            SELECT :uuid, channel_id, locale_id, 5, 6 FROM pim_catalog_channel_locale LIMIT 1
            SQL,
            ['uuid' => Uuid::fromString(self::PRODUCT_UUID)->getBytes()],
            ['uuid' => Types::BINARY]
        );
        $this->connection->executeStatement(
            <<<SQL
            INSERT INTO pim_catalog_product_completeness(product_uuid, completeness)
            VALUES (:uuid, JSON_OBJECT())
            SQL,
            ['uuid' => Uuid::fromString(self::PRODUCT_UUID)->getBytes()],
            ['uuid' => Types::BINARY]
        );
    }
}
