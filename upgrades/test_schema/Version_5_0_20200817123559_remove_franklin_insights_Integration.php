<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pim\Upgrade\test_schema;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_5_0_20200817123559_remove_franklin_insights_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->createJobQueueTableIfNeeded();
    }

    protected function tearDown(): void
    {
        $this->get('database_connection')->executeQuery('DROP TABLE akeneo_batch_job_execution_queue');
        parent::tearDown();
    }

    public function testItRemovesFranklinInsightsTables()
    {
        $schemaManager = $this->get('database_connection')->getSchemaManager();

        $this->createFranklinInsightsTables();
        $this->assertTrue($schemaManager->tablesExist('pimee_franklin_insights_attribute_added_to_family'));
        $this->assertTrue($schemaManager->tablesExist('pimee_franklin_insights_attribute_created'));
        $this->assertTrue($schemaManager->tablesExist('pimee_franklin_insights_identifier_mapping'));
        $this->assertTrue($schemaManager->tablesExist('pimee_franklin_insights_quality_highlights_pending_items'));
        $this->assertTrue($schemaManager->tablesExist('pimee_franklin_insights_subscription'));

        $this->runMigration();
        $this->assertFalse($schemaManager->tablesExist('pimee_franklin_insights_attribute_added_to_family'));
        $this->assertFalse($schemaManager->tablesExist('pimee_franklin_insights_attribute_created'));
        $this->assertFalse($schemaManager->tablesExist('pimee_franklin_insights_identifier_mapping'));
        $this->assertFalse($schemaManager->tablesExist('pimee_franklin_insights_quality_highlights_pending_items'));
        $this->assertFalse($schemaManager->tablesExist('pimee_franklin_insights_subscription'));
    }

    public function testItRemovesJobs()
    {
        $this->createJobs();
        $this->runMigration();

        $dbConnection = $this->get('database_connection');

        $query = <<<SQL
SELECT count(*) FROM akeneo_batch_job_instance WHERE code LIKE 'franklin_insights_%'
SQL;
        $this->assertSame('0', $dbConnection->executeQuery($query)->fetchColumn());

        $query = <<<SQL
SELECT count(*) FROM akeneo_batch_job_execution
SQL;
        $this->assertSame('0', $dbConnection->executeQuery($query)->fetchColumn());

        $query = <<<SQL
SELECT count(*) FROM akeneo_batch_step_execution
SQL;
        $this->assertSame('0', $dbConnection->executeQuery($query)->fetchColumn());

        $query = <<<SQL
SELECT count(*) FROM akeneo_batch_job_execution_queue
SQL;
        $this->assertSame('0', $dbConnection->executeQuery($query)->fetchColumn());
    }

    private function runMigration(): void
    {
        $migrationCommand = sprintf('doctrine:migrations:execute %s --up -n', $this->getMigrationLabel());
        $this->get('pim_catalog.command_launcher')->executeForeground($migrationCommand);
    }

    private function getMigrationLabel(): string
    {
        $migration = (new \ReflectionClass($this))->getShortName();
        $migration = str_replace('_Integration', '', $migration);
        $migration = str_replace('Version', '', $migration);

        return $migration;
    }

    private function createFranklinInsightsTables(): void
    {
        $dbConnection = $this->get('database_connection');

        $dbConnection->executeQuery(<<<SQL
CREATE TABLE IF NOT EXISTS pimee_franklin_insights_attribute_created(
    attribute_code VARCHAR(100) NOT NULL,
    attribute_type VARCHAR(255) NOT NULL,
    created DATETIME NOT NULL COMMENT '(DC2Type:datetime)' DEFAULT CURRENT_TIMESTAMP, 
    INDEX IDX_FI_AATF_attribute_code (attribute_code)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC
SQL
        );

        $dbConnection->executeQuery(<<<SQL
CREATE TABLE IF NOT EXISTS pimee_franklin_insights_attribute_added_to_family(
    attribute_code VARCHAR(100) NOT NULL,
    family_code VARCHAR(100) NOT NULL,
    created DATETIME NOT NULL COMMENT '(DC2Type:datetime)' DEFAULT CURRENT_TIMESTAMP, 
    INDEX IDX_FI_aatf_attribute_code (attribute_code),
    INDEX IDX_FI_aatf_family_code (family_code)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC
SQL
        );

        $dbConnection->executeQuery(<<<SQL
CREATE TABLE `pimee_franklin_insights_identifier_mapping` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute_id` int(11) DEFAULT NULL,
  `franklin_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_7898784B6E62EFA` (`attribute_id`),
  KEY `franklin_code_idx` (`franklin_code`),
  CONSTRAINT `FK_7898784B6E62EFA` FOREIGN KEY (`attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
SQL
        );

        $dbConnection->executeQuery(<<<SQL
CREATE TABLE IF NOT EXISTS pimee_franklin_insights_quality_highlights_pending_items
(
    entity_type varchar(20) not null,
    entity_id varchar(100) not null,
    action varchar(20) null,
    lock_id varchar(60) default '' not null,
    UNIQUE KEY(entity_type, entity_id, lock_id)

) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC;
SQL
        );

     $dbConnection->executeQuery(<<<SQL
CREATE TABLE `pimee_franklin_insights_subscription` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subscription_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` int(11) NOT NULL,
  `raw_suggested_data` json DEFAULT NULL COMMENT '(DC2Type:native_json)',
  `misses_mapping` tinyint(1) NOT NULL,
  `requested_asin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_upc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_brand` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_mpn` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `franklin_insights_subscription_idx` (`subscription_id`),
  KEY `franklin_insights_product_idx` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
SQL
     );
    }

    private function createJobs(): void
    {
        $dbConnection = $this->get('database_connection');

        $dbConnection->executeQuery(<<<SQL
INSERT INTO akeneo_batch_job_instance 
    (id, code, label, job_name, status, connector, raw_parameters, type)
VALUES
   (1234, 'franklin_insights_subscribe_products', 'Mass subscribe products', 'franklin_insights_subscribe_products', 0, 'Franklin Insights Connector', 'a:0:{}', ''),
   (1235, 'franklin_insights_unsubscribe_products', 'Mass unsubscribe products', 'franklin_insights_unsubscribe_products', 0, 'Franklin Insights Connector', 'a:0:{}', '')
SQL
        );

        $dbConnection->executeQuery(<<<SQL
INSERT INTO akeneo_batch_job_execution 
    (id, job_instance_id, pid, user, status, start_time, end_time, create_time, updated_time, health_check_time, exit_code, exit_description, failure_exceptions, log_file, raw_parameters)
VALUES
    (1000, 1234, null, 'admin', 2, null, null, '2020-10-07 07:50:24', null, null, 'UNKNOWN', '', 'a:0:{}', null, '{}'),
    (1001, 1234, null, 'admin', 2, null, null, '2020-10-07 07:51:36', null, null, 'UNKNOWN', '', 'a:0:{}', null, '{}')
SQL
        );

        $dbConnection->executeQuery(<<<SQL
INSERT INTO akeneo_batch_step_execution 
    (id, job_execution_id, status, read_count, write_count, filter_count, errors, summary)
VALUES
    (1111, 1000, 1, 42, 42, 0, '', '')
SQL
        );


        $dbConnection->executeQuery(<<<SQL
INSERT INTO akeneo_batch_job_execution_queue 
    (id, job_execution_id, options, consumer, create_time, updated_time)
VALUES
    (2001, 1001, '{"env": "prod"}', null, '2020-10-07 07:51:36', null)
SQL
        );
    }

    private function createJobQueueTableIfNeeded(): void
    {
        $showTables = $this->get('database_connection')->executeQuery(
            "SHOW TABLES LIKE 'akeneo_batch_job_execution_queue';"
        );
        if (1 <= $showTables->rowCount()) {
            return;
        }

        $this->get('database_connection')->executeQuery(<<<SQL
        create table akeneo_batch_job_execution_queue
        (
            id               int auto_increment primary key,
            job_execution_id int          null,
            options          json         null,
            consumer         varchar(255) null,
            create_time      datetime     null,
            updated_time     datetime     null
        ) collate = utf8mb4_unicode_ci;
        SQL);
    }
}
