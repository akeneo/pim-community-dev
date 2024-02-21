<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\MigrateToUuidStep;
use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\MigrateToUuidTrait;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class Version_7_0_20220429131804_execute_uuid_migration_Integration extends TestCase
{
    use ExecuteMigrationTrait;
    use MigrateToUuidTrait;

    private const MIGRATION_ADD_UUID_COLUMNS_LABEL = '_7_0_20220404075823_add_uuid_columns';
    private const MIGRATION_ADD_COMMENT_UUID_COLUMN_LABEL = '_7_0_20220415090329_add_uuid_column_for_comment';
    private const MIGRATION_UUID_LABEL = '_7_0_20220429131804_execute_uuid_migration';

    private UserInterface $adminUser;
    private Connection $connection;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /** @test */
    public function it_migrates_the_database_to_use_uuid(): void
    {
        $this->installOldSchemaWithFixtures();

        $this->assertTheIndexesDoNotExist();

        $this->reExecuteMigration(self::MIGRATION_ADD_UUID_COLUMNS_LABEL);
        $this->reExecuteMigration(self::MIGRATION_ADD_COMMENT_UUID_COLUMN_LABEL);
        $this->reExecuteMigration(self::MIGRATION_UUID_LABEL);

        $this->assertTheIndexesExist();
        $this->assertAllTablesHaveFilledUuid();
        $this->assertJsonHaveUuid();
        $this->assertProductsAreReindexed();
        $this->assertColumnsAreNullable();
        $this->assertCompletenessProductIdColumnNoLongerExists();
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
        // We need to set back the schema for next tests
        $this->clean();
        parent::tearDown();
    }

    private function clean(): void
    {
        $kernel = new \Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
        $consoleApp = new Application($kernel);
        $consoleApp->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'doctrine:schema:drop',
            '--force' => true,
            '--full-database' => true,
        ]);
        $output = new BufferedOutput();
        $consoleApp->run($input, $output);

        $input = new ArrayInput([
            'command' => 'pim:installer:db',
        ]);
        $output = new BufferedOutput();
        $consoleApp->run($input, $output);
    }

    private function assertTheIndexesDoNotExist(): void
    {
        $tables = \array_filter(
            MigrateToUuidStep::TABLES,
            fn (string $tableName): bool => $tableName !== 'pim_catalog_product',
            ARRAY_FILTER_USE_KEY
        );

        foreach ($tables as $tableName => $tableProperties) {
            $indexName = $tableProperties[MigrateToUuidStep::UUID_COLUMN_INDEX_NAME_INDEX];
            if (null !== $indexName && $this->tableExists($tableName)) {
                Assert::assertFalse(
                    $this->indexExists($tableName, $indexName),
                    \sprintf(
                        'The "%s" index exists in the "%s" table',
                        $indexName,
                        $tableName
                    )
                );
            }
        }
    }

    private function assertTheIndexesExist(): void
    {
        foreach (MigrateToUuidStep::TABLES as $tableName => $tableProperties) {
            $indexName = $tableProperties[MigrateToUuidStep::UUID_COLUMN_INDEX_NAME_INDEX];
            if (null !== $indexName && $this->tableExists($tableName)) {
                Assert::assertTrue(
                    $this->indexExists($tableName, $indexName),
                    \sprintf(
                        'The "%s" index does not exist in the "%s" table',
                        $indexName,
                        $tableName
                    )
                );
            }
        }
    }

    private function assertAllTablesHaveFilledUuid(): void
    {
        foreach (MigrateToUuidStep::TABLES as $tableName => $tableInfo) {
            if (!$this->tableExists($tableName)) {
                continue;
            }

            $additionalCondition = \in_array($tableName, ['pim_versioning_version', 'pim_comment_comment'])
                ? ' AND resource_name="Akeneo\\\Pim\\\Enrichment\\\Component\\\Product\\\Model\\\Product"'
                : '';
            $count = $this->getCountWhereUuidColumnIsNull($tableName, $tableInfo[MigrateToUuidStep::UUID_COLUMN_INDEX], $additionalCondition);
            Assert::assertSame(
                0,
                $count,
                \sprintf('%d row(s) in %s do not have filled uuid after migration.', $count, $tableName)
            );
        }
    }

    private function getCountWhereUuidColumnIsNull(string $tableName, string $columnName, string $additionalCondition): int
    {
        $query = \strtr(
            'SELECT COUNT(*) FROM {tableName} WHERE {columnName} IS NULL {additionalCondition}',
            ['{tableName}' => $tableName, '{columnName}' => $columnName, '{additionalCondition}' => $additionalCondition]
        );

        return (int) $this->connection->executeQuery($query)->fetchOne();
    }

    private function assertJsonHaveUuid(): void
    {
        $query = 'SELECT BIN_TO_UUID(uuid) AS uuid, quantified_associations FROM pim_catalog_product WHERE quantified_associations IS NOT NULL';

        $results = $this->connection->fetchAllAssociative($query);

        Assert::assertGreaterThan(0, \count($results), 'No quantified associations found');
        foreach ($results as $result) {
            $quantifiedAssociations = \json_decode($result['quantified_associations'], true);

            foreach ($quantifiedAssociations as $quantifiedAssociation) {
                Assert::assertArrayHasKey('products', $quantifiedAssociation);
                Assert::assertGreaterThan(0, \count($quantifiedAssociation['products']), 'No product quantified associations found');
                foreach ($quantifiedAssociation['products'] as $productInfo) {
                    Assert::assertArrayHasKey('id', $productInfo);
                    Assert::assertArrayHasKey('uuid', $productInfo);
                    Assert::assertSame($this->getProductUuidFromId((int) $productInfo['id']), $productInfo['uuid']);
                }
            }
        }
    }

    private function assertProductsAreReindexed(): void
    {
        $indexedProducts = $this->getIndexedProducts();

        foreach ($indexedProducts as $esId => $identifier) {
            $split = \preg_match('/^product_(?P<uuid>.*)$/', $esId, $matches);
            Assert::assertSame(1, $split);
            Assert::assertTrue(Uuid::isValid($matches['uuid']));
            Assert::assertTrue(
                (bool) $this->connection->executeQuery(
                    'SELECT EXISTS(SELECT * FROM pim_catalog_product WHERE identifier = :identifier AND BIN_TO_UUID(uuid) = :uuid)',
                    ['identifier' => $identifier, 'uuid' => $matches['uuid']]
                )
            );
        }
    }

    private function assertColumnsAreNullable(): void
    {
        $tableWithNullableColumnsList = [
            'pim_versioning_version' => ['resource_id'],
            'pim_comment_comment' => ['resource_id'],
        ];

        foreach ($tableWithNullableColumnsList as $tableName => $columns) {
            foreach ($columns as $columnName) {
                Assert::assertTrue($this->isColumnNullable($tableName, $columnName));
            }
        }
    }

    private function assertCompletenessProductIdColumnNoLongerExists(): void
    {
        Assert::assertSame(
            0,
            (int) $this->connection->executeQuery(<<<SQL
SELECT COUNT(*)
FROM information_schema.columns
WHERE table_name='pim_catalog_completeness' AND column_name='product_id'
SQL
            )->fetchOne()
        );
    }

    private function getIndexedProducts(): array
    {
        /** @var Client $esClient */
        $esClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
        $esClient->refreshIndex();
        $result = $esClient->search([
            'query' => [
                'term' => [
                    'document_type' => ProductInterface::class,
                ],
            ],
            'fields' => ['id', 'identifier'],
            '_source' => false,
            'size' => 100,
        ]);

        $esProducts = [];
        foreach ($result['hits']['hits'] as $document) {
            $identifier = $document['fields']['identifier'][0];
            $esProducts[$document['_id']] = $identifier;
        }

        return $esProducts;
    }

    private function isColumnNullable(string $tableName, string $columnName): bool {
        $schema = $this->connection->getDatabase();
        $sql = <<<SQL
            SELECT IS_NULLABLE
            FROM information_schema.columns
            WHERE table_schema=:schema AND table_name=:tableName AND column_name=:columnName;
        SQL;

        $result = $this->connection->fetchOne($sql, [
            'schema' => $schema,
            'tableName' => $tableName,
            'columnName' => $columnName
        ]);

        return $result !== 'NO';
    }

    private function getProductUuidFromId(int $id): ?string
    {
        $sql = 'SELECT BIN_TO_UUID(uuid) FROM pim_catalog_product WHERE id = :id';

        return $this->connection->executeQuery($sql, ['id' => $id])->fetchOne();
    }

    private function installOldSchemaWithFixtures(): void
    {
        // Schema
        $this->connection->executeQuery('drop database if exists akeneo_pim_test');
        $this->connection->executeQuery('create database akeneo_pim_test');
        $this->connection->executeQuery('use ' . \getenv('APP_DATABASE_NAME')); // Needed after drop/create

        $this->executeLargeQuery(<<<SQL
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `acl_classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `acl_classes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `class_type` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_69DD750638A36066` (`class_type`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `acl_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `acl_entries` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `class_id` int unsigned NOT NULL,
  `object_identity_id` int unsigned DEFAULT NULL,
  `security_identity_id` int unsigned NOT NULL,
  `field_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ace_order` smallint unsigned NOT NULL,
  `mask` int NOT NULL,
  `granting` tinyint(1) NOT NULL,
  `granting_strategy` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `audit_success` tinyint(1) NOT NULL,
  `audit_failure` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_46C8B806EA000B103D9AB4A64DEF17BCE4289BF4` (`class_id`,`object_identity_id`,`field_name`,`ace_order`),
  KEY `IDX_46C8B806EA000B103D9AB4A6DF9183C9` (`class_id`,`object_identity_id`,`security_identity_id`),
  KEY `IDX_46C8B806EA000B10` (`class_id`),
  KEY `IDX_46C8B8063D9AB4A6` (`object_identity_id`),
  KEY `IDX_46C8B806DF9183C9` (`security_identity_id`),
  CONSTRAINT `FK_46C8B8063D9AB4A6` FOREIGN KEY (`object_identity_id`) REFERENCES `acl_object_identities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_46C8B806DF9183C9` FOREIGN KEY (`security_identity_id`) REFERENCES `acl_security_identities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_46C8B806EA000B10` FOREIGN KEY (`class_id`) REFERENCES `acl_classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `acl_object_identities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `acl_object_identities` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `parent_object_identity_id` int unsigned DEFAULT NULL,
  `class_id` int unsigned NOT NULL,
  `object_identifier` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `entries_inheriting` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_9407E5494B12AD6EA000B10` (`object_identifier`,`class_id`),
  KEY `IDX_9407E54977FA751A` (`parent_object_identity_id`),
  CONSTRAINT `FK_9407E54977FA751A` FOREIGN KEY (`parent_object_identity_id`) REFERENCES `acl_object_identities` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `acl_object_identity_ancestors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `acl_object_identity_ancestors` (
  `object_identity_id` int unsigned NOT NULL,
  `ancestor_id` int unsigned NOT NULL,
  PRIMARY KEY (`object_identity_id`,`ancestor_id`),
  KEY `IDX_825DE2993D9AB4A6` (`object_identity_id`),
  KEY `IDX_825DE299C671CEA1` (`ancestor_id`),
  CONSTRAINT `FK_825DE2993D9AB4A6` FOREIGN KEY (`object_identity_id`) REFERENCES `acl_object_identities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_825DE299C671CEA1` FOREIGN KEY (`ancestor_id`) REFERENCES `acl_object_identities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `acl_security_identities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `acl_security_identities` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8835EE78772E836AF85E0677` (`identifier`,`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `acme_reference_data_color`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `acme_reference_data_color` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sortOrder` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `hex` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `red` int NOT NULL,
  `green` int NOT NULL,
  `blue` int NOT NULL,
  `hue` int NOT NULL,
  `hslSaturation` int NOT NULL,
  `light` int NOT NULL,
  `hsvSaturation` int NOT NULL,
  `value` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_D28047C977153098` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `acme_reference_data_fabric`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `acme_reference_data_fabric` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sortOrder` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alternativeName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_5639866477153098` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `akeneo_batch_job_execution`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `akeneo_batch_job_execution` (
  `id` int NOT NULL AUTO_INCREMENT,
  `job_instance_id` int NOT NULL,
  `pid` int DEFAULT NULL,
  `user` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` int NOT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `create_time` datetime DEFAULT NULL,
  `updated_time` datetime DEFAULT NULL,
  `health_check_time` datetime DEFAULT NULL,
  `exit_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exit_description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `failure_exceptions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:array)',
  `log_file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `raw_parameters` json NOT NULL,
  `is_stoppable` tinyint(1) NOT NULL DEFAULT '0',
  `step_count` int NOT NULL DEFAULT '1',
  `is_visible` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `IDX_62738477593D6954` (`job_instance_id`),
  KEY `user_idx` (`user`),
  KEY `status_idx` (`status`),
  KEY `is_visible_idx` (`is_visible`),
  KEY `job_instance_id_user_status_is_visible_idx` (`job_instance_id`,`user`,`status`,`is_visible`),
  CONSTRAINT `FK_62738477593D6954` FOREIGN KEY (`job_instance_id`) REFERENCES `akeneo_batch_job_instance` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `akeneo_batch_job_instance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `akeneo_batch_job_instance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `job_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` int NOT NULL,
  `connector` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `raw_parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `searchunique_idx` (`code`),
  KEY `code_idx` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `akeneo_batch_step_execution`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `akeneo_batch_step_execution` (
  `id` int NOT NULL AUTO_INCREMENT,
  `job_execution_id` int DEFAULT NULL,
  `step_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` int NOT NULL,
  `read_count` int NOT NULL,
  `write_count` int NOT NULL,
  `filter_count` int NOT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `exit_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exit_description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `terminate_only` tinyint(1) DEFAULT NULL,
  `failure_exceptions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:array)',
  `errors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `summary` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `tracking_data` json DEFAULT NULL,
  `warning_count` int NOT NULL DEFAULT '0',
  `is_trackable` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IDX_BDE7D0925871C06B` (`job_execution_id`),
  CONSTRAINT `FK_BDE7D0925871C06B` FOREIGN KEY (`job_execution_id`) REFERENCES `akeneo_batch_job_execution` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `akeneo_batch_warning`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `akeneo_batch_warning` (
  `id` int NOT NULL AUTO_INCREMENT,
  `step_execution_id` int DEFAULT NULL,
  `reason` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `reason_parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `item` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  PRIMARY KEY (`id`),
  KEY `IDX_8EE0AE736C7DA296` (`step_execution_id`),
  CONSTRAINT `FK_8EE0AE736C7DA296` FOREIGN KEY (`step_execution_id`) REFERENCES `akeneo_batch_step_execution` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `akeneo_communication_channel_viewed_announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `akeneo_communication_channel_viewed_announcements` (
  `announcement_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`announcement_id`,`user_id`),
  UNIQUE KEY `IDX_VIEWED_ANNOUNCEMENTS_user_id_announcement_id` (`user_id`,`announcement_id`),
  CONSTRAINT `FK_COMMUNICATION_CHANNEL_VIEWED_ANNOUNCEMENTS_user_id` FOREIGN KEY (`user_id`) REFERENCES `oro_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `akeneo_connectivity_connected_app`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `akeneo_connectivity_connected_app` (
  `id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `author` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `partner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `categories` json NOT NULL,
  `certified` tinyint(1) NOT NULL DEFAULT '0',
  `connection_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `scopes` json NOT NULL,
  `user_group_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_CONNECTIVITY_CONNECTED_APP_connection_code` (`connection_code`),
  KEY `FK_CONNECTIVITY_CONNECTED_APP_user_group_name` (`user_group_name`),
  CONSTRAINT `FK_CONNECTIVITY_CONNECTED_APP_connection_code` FOREIGN KEY (`connection_code`) REFERENCES `akeneo_connectivity_connection` (`code`),
  CONSTRAINT `FK_CONNECTIVITY_CONNECTED_APP_user_group_name` FOREIGN KEY (`user_group_name`) REFERENCES `oro_access_group` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `akeneo_connectivity_connection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `akeneo_connectivity_connection` (
  `client_id` int NOT NULL,
  `user_id` int NOT NULL,
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `flow_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `auditable` tinyint(1) NOT NULL DEFAULT '0',
  `webhook_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `webhook_secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `webhook_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  PRIMARY KEY (`code`),
  UNIQUE KEY `client_id` (`client_id`),
  KEY `FK_CONNECTIVITY_CONNECTION_user_id` (`user_id`),
  CONSTRAINT `FK_CONNECTIVITY_CONNECTION_client_id` FOREIGN KEY (`client_id`) REFERENCES `pim_api_client` (`id`),
  CONSTRAINT `FK_CONNECTIVITY_CONNECTION_user_id` FOREIGN KEY (`user_id`) REFERENCES `oro_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `akeneo_connectivity_connection_audit_error`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `akeneo_connectivity_connection_audit_error` (
  `connection_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `error_datetime` datetime NOT NULL,
  `error_count` int NOT NULL,
  `error_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`error_datetime`,`connection_code`,`error_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `akeneo_connectivity_connection_audit_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `akeneo_connectivity_connection_audit_product` (
  `connection_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_datetime` datetime NOT NULL,
  `event_count` int NOT NULL,
  `event_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`event_datetime`,`connection_code`,`event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `akeneo_connectivity_connection_events_api_request_count`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `akeneo_connectivity_connection_events_api_request_count` (
  `event_minute` int NOT NULL,
  `event_count` int NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`event_minute`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `akeneo_connectivity_connection_wrong_credentials_combination`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `akeneo_connectivity_connection_wrong_credentials_combination` (
  `connection_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `authentication_date` datetime NOT NULL,
  PRIMARY KEY (`connection_code`,`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `akeneo_connectivity_user_consent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `akeneo_connectivity_user_consent` (
  `user_id` int NOT NULL,
  `app_id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `scopes` json NOT NULL,
  `uuid` char(36) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  `consent_date` datetime NOT NULL,
  PRIMARY KEY (`user_id`,`app_id`),
  CONSTRAINT `FK_CONNECTIVITY_CONNECTION_user_consent_user_id` FOREIGN KEY (`user_id`) REFERENCES `oro_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `akeneo_file_storage_file_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `akeneo_file_storage_file_info` (
  `id` int NOT NULL AUTO_INCREMENT,
  `file_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` int DEFAULT NULL,
  `extension` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `hash` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `storage` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_F19B3719A5D32530` (`file_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `akeneo_measurement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `akeneo_measurement` (
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `labels` json NOT NULL,
  `standard_unit` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `units` json NOT NULL,
  PRIMARY KEY (`code`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `akeneo_structure_version_last_update`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `akeneo_structure_version_last_update` (
  `resource_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_update` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  PRIMARY KEY (`resource_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lock_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lock_keys` (
  `key_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `key_token` varchar(44) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `key_expiration` int unsigned NOT NULL,
  PRIMARY KEY (`key_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `messenger_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messenger_messages` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `headers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_75EA56E016BA31DB` (`delivered_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migration_versions` (
  `version` varchar(190) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oro_access_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oro_access_group` (
  `id` smallint NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `default_permissions` json DEFAULT NULL,
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_FEF9EDB75E237E06` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oro_access_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oro_access_role` (
  `id` smallint NOT NULL AUTO_INCREMENT,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_673F65E757698A6A` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oro_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oro_config` (
  `id` int NOT NULL AUTO_INCREMENT,
  `entity` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `record_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `CONFIG_UQ_ENTITY` (`entity`,`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oro_config_value`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oro_config_value` (
  `id` int NOT NULL AUTO_INCREMENT,
  `config_id` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `section` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `CONFIG_VALUE_UQ_ENTITY` (`name`,`section`,`config_id`),
  KEY `IDX_DAF6DF5524DB0683` (`config_id`),
  CONSTRAINT `FK_DAF6DF5524DB0683` FOREIGN KEY (`config_id`) REFERENCES `oro_config` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oro_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oro_user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `file_info_id` int DEFAULT NULL,
  `ui_locale_id` int NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_prefix` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `middle_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_suffix` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `salt` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `confirmation_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_requested` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `login_count` int unsigned NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL,
  `product_grid_filters` json DEFAULT NULL,
  `emailNotifications` tinyint(1) NOT NULL DEFAULT '0',
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timezone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `properties` json NOT NULL,
  `consecutive_authentication_failure_counter` int unsigned NOT NULL DEFAULT '0',
  `authentication_failure_reset_date` datetime DEFAULT NULL,
  `profile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catalogLocale_id` int DEFAULT NULL,
  `catalogScope_id` int DEFAULT NULL,
  `defaultTree_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_F82840BCF85E0677` (`username`),
  UNIQUE KEY `UNIQ_F82840BCE7927C74` (`email`),
  UNIQUE KEY `UNIQ_F82840BC6ED78C3` (`file_info_id`),
  KEY `IDX_F82840BC7BBFC60C` (`catalogLocale_id`),
  KEY `IDX_F82840BCA7EA8E8C` (`ui_locale_id`),
  KEY `IDX_F82840BCEAA72736` (`catalogScope_id`),
  KEY `IDX_F82840BCD654B1EE` (`defaultTree_id`),
  CONSTRAINT `FK_F82840BC6ED78C3` FOREIGN KEY (`file_info_id`) REFERENCES `akeneo_file_storage_file_info` (`id`),
  CONSTRAINT `FK_F82840BC7BBFC60C` FOREIGN KEY (`catalogLocale_id`) REFERENCES `pim_catalog_locale` (`id`),
  CONSTRAINT `FK_F82840BCA7EA8E8C` FOREIGN KEY (`ui_locale_id`) REFERENCES `pim_catalog_locale` (`id`),
  CONSTRAINT `FK_F82840BCD654B1EE` FOREIGN KEY (`defaultTree_id`) REFERENCES `pim_catalog_category` (`id`),
  CONSTRAINT `FK_F82840BCEAA72736` FOREIGN KEY (`catalogScope_id`) REFERENCES `pim_catalog_channel` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oro_user_access_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oro_user_access_group` (
  `user_id` int NOT NULL,
  `group_id` smallint NOT NULL,
  PRIMARY KEY (`user_id`,`group_id`),
  KEY `IDX_EC003EF3A76ED395` (`user_id`),
  KEY `IDX_EC003EF3FE54D947` (`group_id`),
  CONSTRAINT `FK_EC003EF3A76ED395` FOREIGN KEY (`user_id`) REFERENCES `oro_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_EC003EF3FE54D947` FOREIGN KEY (`group_id`) REFERENCES `oro_access_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oro_user_access_group_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oro_user_access_group_role` (
  `group_id` smallint NOT NULL,
  `role_id` smallint NOT NULL,
  PRIMARY KEY (`group_id`,`role_id`),
  KEY `IDX_E7E7E38EFE54D947` (`group_id`),
  KEY `IDX_E7E7E38ED60322AC` (`role_id`),
  CONSTRAINT `FK_E7E7E38ED60322AC` FOREIGN KEY (`role_id`) REFERENCES `oro_access_role` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_E7E7E38EFE54D947` FOREIGN KEY (`group_id`) REFERENCES `oro_access_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oro_user_access_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oro_user_access_role` (
  `user_id` int NOT NULL,
  `role_id` smallint NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `IDX_290571BEA76ED395` (`user_id`),
  KEY `IDX_290571BED60322AC` (`role_id`),
  CONSTRAINT `FK_290571BEA76ED395` FOREIGN KEY (`user_id`) REFERENCES `oro_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_290571BED60322AC` FOREIGN KEY (`role_id`) REFERENCES `oro_access_role` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_aggregated_volume`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_aggregated_volume` (
  `volume_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `volume` json NOT NULL,
  `aggregated_at` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  PRIMARY KEY (`volume_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_api_access_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_api_access_token` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client` int DEFAULT NULL,
  `user` int DEFAULT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` int DEFAULT NULL,
  `scope` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_BD5E40235F37A13B` (`token`),
  KEY `IDX_BD5E4023C7440455` (`client`),
  KEY `IDX_BD5E40238D93D649` (`user`),
  CONSTRAINT `FK_BD5E40238D93D649` FOREIGN KEY (`user`) REFERENCES `oro_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_BD5E4023C7440455` FOREIGN KEY (`client`) REFERENCES `pim_api_client` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_api_auth_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_api_auth_code` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `redirect_uri` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` int DEFAULT NULL,
  `scope` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_AD5DC7C65F37A13B` (`token`),
  KEY `IDX_AD5DC7C619EB6921` (`client_id`),
  KEY `IDX_AD5DC7C6A76ED395` (`user_id`),
  CONSTRAINT `FK_AD5DC7C619EB6921` FOREIGN KEY (`client_id`) REFERENCES `pim_api_client` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_AD5DC7C6A76ED395` FOREIGN KEY (`user_id`) REFERENCES `oro_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_api_client`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_api_client` (
  `id` int NOT NULL AUTO_INCREMENT,
  `random_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `redirect_uris` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `allowed_grant_types` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `marketplace_public_app_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_api_refresh_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_api_refresh_token` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client` int DEFAULT NULL,
  `user` int DEFAULT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` int DEFAULT NULL,
  `scope` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_264A45105F37A13B` (`token`),
  KEY `IDX_264A4510C7440455` (`client`),
  KEY `IDX_264A45108D93D649` (`user`),
  CONSTRAINT `FK_264A45108D93D649` FOREIGN KEY (`user`) REFERENCES `oro_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_264A4510C7440455` FOREIGN KEY (`client`) REFERENCES `pim_api_client` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_association`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_association` (
  `id` int NOT NULL AUTO_INCREMENT,
  `association_type_id` int NOT NULL,
  `owner_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale_foreign_key_idx` (`owner_id`,`association_type_id`),
  KEY `IDX_CC271001B1E1C39` (`association_type_id`),
  KEY `IDX_CC2710017E3C61F9` (`owner_id`),
  CONSTRAINT `FK_CC2710017E3C61F9` FOREIGN KEY (`owner_id`) REFERENCES `pim_catalog_product` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_CC271001B1E1C39` FOREIGN KEY (`association_type_id`) REFERENCES `pim_catalog_association_type` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_association_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_association_group` (
  `association_id` int NOT NULL,
  `group_id` int NOT NULL,
  PRIMARY KEY (`association_id`,`group_id`),
  KEY `IDX_E91414DDEFB9C8A5` (`association_id`),
  KEY `IDX_E91414DDFE54D947` (`group_id`),
  CONSTRAINT `FK_E91414DDEFB9C8A5` FOREIGN KEY (`association_id`) REFERENCES `pim_catalog_association` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_E91414DDFE54D947` FOREIGN KEY (`group_id`) REFERENCES `pim_catalog_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_association_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_association_product` (
  `association_id` int NOT NULL,
  `product_id` int NOT NULL,
  PRIMARY KEY (`association_id`,`product_id`),
  KEY `IDX_3A3A49D4EFB9C8A5` (`association_id`),
  KEY `IDX_3A3A49D44584665A` (`product_id`),
  CONSTRAINT `FK_3A3A49D44584665A` FOREIGN KEY (`product_id`) REFERENCES `pim_catalog_product` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_3A3A49D4EFB9C8A5` FOREIGN KEY (`association_id`) REFERENCES `pim_catalog_association` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_association_product_model`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_association_product_model` (
  `association_id` int NOT NULL,
  `product_model_id` int NOT NULL,
  PRIMARY KEY (`association_id`,`product_model_id`),
  KEY `IDX_378B82C7EFB9C8A5` (`association_id`),
  KEY `IDX_378B82C7B2C5DD70` (`product_model_id`),
  CONSTRAINT `FK_378B82C7B2C5DD70` FOREIGN KEY (`product_model_id`) REFERENCES `pim_catalog_product_model` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_378B82C7EFB9C8A5` FOREIGN KEY (`association_id`) REFERENCES `pim_catalog_association` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_association_product_model_to_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_association_product_model_to_group` (
  `association_id` int NOT NULL,
  `group_id` int NOT NULL,
  PRIMARY KEY (`association_id`,`group_id`),
  KEY `IDX_16EA55AEEFB9C8A5` (`association_id`),
  KEY `IDX_16EA55AEFE54D947` (`group_id`),
  CONSTRAINT `FK_16EA55AEEFB9C8A5` FOREIGN KEY (`association_id`) REFERENCES `pim_catalog_product_model_association` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_16EA55AEFE54D947` FOREIGN KEY (`group_id`) REFERENCES `pim_catalog_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_association_product_model_to_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_association_product_model_to_product` (
  `association_id` int NOT NULL,
  `product_id` int NOT NULL,
  PRIMARY KEY (`association_id`,`product_id`),
  KEY `IDX_3FF3ED19EFB9C8A5` (`association_id`),
  KEY `IDX_3FF3ED194584665A` (`product_id`),
  CONSTRAINT `FK_3FF3ED194584665A` FOREIGN KEY (`product_id`) REFERENCES `pim_catalog_product` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_3FF3ED19EFB9C8A5` FOREIGN KEY (`association_id`) REFERENCES `pim_catalog_product_model_association` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_association_product_model_to_product_model`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_association_product_model_to_product_model` (
  `association_id` int NOT NULL,
  `product_model_id` int NOT NULL,
  PRIMARY KEY (`association_id`,`product_model_id`),
  KEY `IDX_12D4D59CEFB9C8A5` (`association_id`),
  KEY `IDX_12D4D59CB2C5DD70` (`product_model_id`),
  CONSTRAINT `FK_12D4D59CB2C5DD70` FOREIGN KEY (`product_model_id`) REFERENCES `pim_catalog_product_model` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_12D4D59CEFB9C8A5` FOREIGN KEY (`association_id`) REFERENCES `pim_catalog_product_model_association` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_association_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_association_type` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `is_two_way` tinyint(1) NOT NULL DEFAULT '0',
  `is_quantified` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E6CF913A77153098` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_association_type_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_association_type_translation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `foreign_key` int DEFAULT NULL,
  `label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `locale` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale_foreign_key_idx` (`locale`,`foreign_key`),
  KEY `IDX_CCCBAA2D7E366551` (`foreign_key`),
  CONSTRAINT `FK_CCCBAA2D7E366551` FOREIGN KEY (`foreign_key`) REFERENCES `pim_catalog_association_type` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_attribute` (
  `id` int NOT NULL AUTO_INCREMENT,
  `group_id` int DEFAULT NULL,
  `sort_order` int NOT NULL,
  `useable_as_grid_filter` tinyint(1) NOT NULL DEFAULT '0',
  `max_characters` int DEFAULT NULL,
  `validation_rule` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `validation_regexp` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wysiwyg_enabled` tinyint(1) DEFAULT NULL,
  `number_min` decimal(14,4) DEFAULT NULL,
  `number_max` decimal(14,4) DEFAULT NULL,
  `decimals_allowed` tinyint(1) DEFAULT NULL,
  `negative_allowed` tinyint(1) DEFAULT NULL,
  `date_min` datetime DEFAULT NULL,
  `date_max` datetime DEFAULT NULL,
  `metric_family` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_metric_unit` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_file_size` decimal(6,2) DEFAULT NULL,
  `allowed_extensions` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `minimumInputLength` smallint DEFAULT NULL,
  `is_required` tinyint(1) NOT NULL,
  `is_unique` tinyint(1) NOT NULL,
  `is_localizable` tinyint(1) NOT NULL,
  `is_scopable` tinyint(1) NOT NULL,
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attribute_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `backend_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:array)',
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `guidelines` json NOT NULL DEFAULT (json_object()),
  PRIMARY KEY (`id`),
  UNIQUE KEY `searchunique_idx` (`code`,`entity_type`),
  KEY `IDX_492FD44FFE54D947` (`group_id`),
  KEY `searchcode_idx` (`code`),
  CONSTRAINT `FK_492FD44FFE54D947` FOREIGN KEY (`group_id`) REFERENCES `pim_catalog_attribute_group` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_attribute_blacklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_attribute_blacklist` (
  `attribute_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cleanup_job_execution_id` int DEFAULT NULL,
  PRIMARY KEY (`attribute_code`),
  UNIQUE KEY `searchunique_idx` (`attribute_code`),
  KEY `FK_BDE7D0925812C06B` (`cleanup_job_execution_id`),
  CONSTRAINT `FK_BDE7D0925812C06B` FOREIGN KEY (`cleanup_job_execution_id`) REFERENCES `akeneo_batch_job_execution` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_attribute_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_attribute_group` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E299C05E77153098` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_attribute_group_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_attribute_group_translation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `foreign_key` int DEFAULT NULL,
  `label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `locale` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale_foreign_key_idx` (`locale`,`foreign_key`),
  KEY `IDX_B74958BD7E366551` (`foreign_key`),
  CONSTRAINT `FK_B74958BD7E366551` FOREIGN KEY (`foreign_key`) REFERENCES `pim_catalog_attribute_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_attribute_locale`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_attribute_locale` (
  `attribute_id` int NOT NULL,
  `locale_id` int NOT NULL,
  PRIMARY KEY (`attribute_id`,`locale_id`),
  KEY `IDX_26D2D5D0B6E62EFA` (`attribute_id`),
  KEY `IDX_26D2D5D0E559DFD1` (`locale_id`),
  CONSTRAINT `FK_26D2D5D0B6E62EFA` FOREIGN KEY (`attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_26D2D5D0E559DFD1` FOREIGN KEY (`locale_id`) REFERENCES `pim_catalog_locale` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_attribute_option`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_attribute_option` (
  `id` int NOT NULL AUTO_INCREMENT,
  `attribute_id` int NOT NULL,
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `searchunique_idx` (`code`,`attribute_id`),
  KEY `IDX_3DD413F8B6E62EFA` (`attribute_id`),
  CONSTRAINT `FK_3DD413F8B6E62EFA` FOREIGN KEY (`attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_attribute_option_value`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_attribute_option_value` (
  `id` int NOT NULL AUTO_INCREMENT,
  `option_id` int NOT NULL,
  `locale_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `searchunique_idx` (`locale_code`,`option_id`),
  KEY `IDX_CC4B9A83A7C41D6F` (`option_id`),
  CONSTRAINT `FK_CC4B9A83A7C41D6F` FOREIGN KEY (`option_id`) REFERENCES `pim_catalog_attribute_option` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_attribute_requirement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_attribute_requirement` (
  `id` int NOT NULL AUTO_INCREMENT,
  `family_id` int NOT NULL,
  `attribute_id` int NOT NULL,
  `channel_id` int NOT NULL,
  `required` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `searchunique_idx` (`channel_id`,`family_id`,`attribute_id`),
  KEY `IDX_B494B917C35E566A` (`family_id`),
  KEY `IDX_B494B917B6E62EFA` (`attribute_id`),
  KEY `IDX_B494B91772F5A1AA` (`channel_id`),
  CONSTRAINT `FK_B494B91772F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `pim_catalog_channel` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_B494B917B6E62EFA` FOREIGN KEY (`attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_B494B917C35E566A` FOREIGN KEY (`family_id`) REFERENCES `pim_catalog_family` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_attribute_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_attribute_translation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `foreign_key` int DEFAULT NULL,
  `label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `locale` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale_foreign_key_idx` (`locale`,`foreign_key`),
  KEY `IDX_DBC2A9287E366551` (`foreign_key`),
  CONSTRAINT `FK_DBC2A9287E366551` FOREIGN KEY (`foreign_key`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_id` int DEFAULT NULL,
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `root` int NOT NULL,
  `lvl` int NOT NULL,
  `lft` int NOT NULL,
  `rgt` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pim_category_code_uc` (`code`),
  KEY `IDX_350D8339727ACA70` (`parent_id`),
  KEY `left_idx` (`lft`),
  CONSTRAINT `FK_350D8339727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `pim_catalog_category` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_category_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_category_product` (
  `product_id` int NOT NULL,
  `category_id` int NOT NULL,
  PRIMARY KEY (`product_id`,`category_id`),
  KEY `IDX_512179C14584665A` (`product_id`),
  KEY `IDX_512179C112469DE2` (`category_id`),
  CONSTRAINT `FK_512179C112469DE2` FOREIGN KEY (`category_id`) REFERENCES `pim_catalog_category` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_512179C14584665A` FOREIGN KEY (`product_id`) REFERENCES `pim_catalog_product` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_category_product_model`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_category_product_model` (
  `product_model_id` int NOT NULL,
  `category_id` int NOT NULL,
  PRIMARY KEY (`product_model_id`,`category_id`),
  KEY `IDX_62B5D36B2C5DD70` (`product_model_id`),
  KEY `IDX_62B5D3612469DE2` (`category_id`),
  CONSTRAINT `FK_62B5D3612469DE2` FOREIGN KEY (`category_id`) REFERENCES `pim_catalog_category` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_62B5D36B2C5DD70` FOREIGN KEY (`product_model_id`) REFERENCES `pim_catalog_product_model` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_category_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_category_translation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `foreign_key` int DEFAULT NULL,
  `label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `locale` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale_foreign_key_idx` (`locale`,`foreign_key`),
  KEY `IDX_1C0FA6B77E366551` (`foreign_key`),
  CONSTRAINT `FK_1C0FA6B77E366551` FOREIGN KEY (`foreign_key`) REFERENCES `pim_catalog_category` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_channel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_channel` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int DEFAULT NULL,
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `conversionUnits` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E07E932A77153098` (`code`),
  KEY `IDX_E07E932A12469DE2` (`category_id`),
  CONSTRAINT `FK_E07E932A12469DE2` FOREIGN KEY (`category_id`) REFERENCES `pim_catalog_category` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_channel_currency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_channel_currency` (
  `channel_id` int NOT NULL,
  `currency_id` int NOT NULL,
  PRIMARY KEY (`channel_id`,`currency_id`),
  KEY `IDX_5B55C68472F5A1AA` (`channel_id`),
  KEY `IDX_5B55C68438248176` (`currency_id`),
  CONSTRAINT `FK_5B55C68438248176` FOREIGN KEY (`currency_id`) REFERENCES `pim_catalog_currency` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_5B55C68472F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `pim_catalog_channel` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_channel_locale`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_channel_locale` (
  `channel_id` int NOT NULL,
  `locale_id` int NOT NULL,
  PRIMARY KEY (`channel_id`,`locale_id`),
  KEY `IDX_D8113CB272F5A1AA` (`channel_id`),
  KEY `IDX_D8113CB2E559DFD1` (`locale_id`),
  CONSTRAINT `FK_D8113CB272F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `pim_catalog_channel` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_D8113CB2E559DFD1` FOREIGN KEY (`locale_id`) REFERENCES `pim_catalog_locale` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_channel_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_channel_translation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `foreign_key` int DEFAULT NULL,
  `label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `locale` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale_foreign_key_idx` (`locale`,`foreign_key`),
  KEY `IDX_8A91679D7E366551` (`foreign_key`),
  CONSTRAINT `FK_8A91679D7E366551` FOREIGN KEY (`foreign_key`) REFERENCES `pim_catalog_channel` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_completeness`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_completeness` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `locale_id` int NOT NULL,
  `channel_id` int NOT NULL,
  `product_id` int NOT NULL,
  `missing_count` int NOT NULL,
  `required_count` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `searchunique_idx` (`channel_id`,`locale_id`,`product_id`),
  KEY `IDX_113BA854E559DFD1` (`locale_id`),
  KEY `IDX_113BA85472F5A1AA` (`channel_id`),
  KEY `IDX_113BA8544584665A` (`product_id`),
  CONSTRAINT `FK_113BA85472F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `pim_catalog_channel` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_113BA854E559DFD1` FOREIGN KEY (`locale_id`) REFERENCES `pim_catalog_locale` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_currency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_currency` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_activated` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_5A1712C777153098` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=295 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_family`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_family` (
  `id` int NOT NULL AUTO_INCREMENT,
  `label_attribute_id` int DEFAULT NULL,
  `image_attribute_id` int DEFAULT NULL,
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_9063207277153098` (`code`),
  KEY `IDX_90632072E2D3A503` (`label_attribute_id`),
  KEY `IDX_90632072BC295696` (`image_attribute_id`),
  CONSTRAINT `FK_90632072BC295696` FOREIGN KEY (`image_attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_90632072E2D3A503` FOREIGN KEY (`label_attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_family_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_family_attribute` (
  `family_id` int NOT NULL,
  `attribute_id` int NOT NULL,
  PRIMARY KEY (`family_id`,`attribute_id`),
  KEY `IDX_76074884C35E566A` (`family_id`),
  KEY `IDX_76074884B6E62EFA` (`attribute_id`),
  CONSTRAINT `FK_76074884B6E62EFA` FOREIGN KEY (`attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_76074884C35E566A` FOREIGN KEY (`family_id`) REFERENCES `pim_catalog_family` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_family_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_family_translation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `foreign_key` int DEFAULT NULL,
  `label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `locale` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale_foreign_key_idx` (`locale`,`foreign_key`),
  KEY `IDX_547C9A607E366551` (`foreign_key`),
  CONSTRAINT `FK_547C9A607E366551` FOREIGN KEY (`foreign_key`) REFERENCES `pim_catalog_family` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_family_variant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_family_variant` (
  `id` int NOT NULL AUTO_INCREMENT,
  `family_id` int DEFAULT NULL,
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_FBA5AB4577153098` (`code`),
  KEY `IDX_FBA5AB45C35E566A` (`family_id`),
  CONSTRAINT `FK_FBA5AB45C35E566A` FOREIGN KEY (`family_id`) REFERENCES `pim_catalog_family` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_family_variant_attribute_set`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_family_variant_attribute_set` (
  `id` int NOT NULL AUTO_INCREMENT,
  `level` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_family_variant_has_variant_attribute_sets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_family_variant_has_variant_attribute_sets` (
  `family_variant_id` int NOT NULL,
  `variant_attribute_sets_id` int NOT NULL,
  PRIMARY KEY (`family_variant_id`,`variant_attribute_sets_id`),
  UNIQUE KEY `UNIQ_1F4DC702D8404D` (`variant_attribute_sets_id`),
  KEY `IDX_1F4DC7028A37AD0` (`family_variant_id`),
  CONSTRAINT `FK_1F4DC7028A37AD0` FOREIGN KEY (`family_variant_id`) REFERENCES `pim_catalog_family_variant` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1F4DC702D8404D` FOREIGN KEY (`variant_attribute_sets_id`) REFERENCES `pim_catalog_family_variant_attribute_set` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_family_variant_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_family_variant_translation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `foreign_key` int DEFAULT NULL,
  `label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `locale` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale_foreign_key_idx` (`locale`,`foreign_key`),
  KEY `IDX_CB9A96AF7E366551` (`foreign_key`),
  CONSTRAINT `FK_CB9A96AF7E366551` FOREIGN KEY (`foreign_key`) REFERENCES `pim_catalog_family_variant` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_group` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type_id` int NOT NULL,
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_5D6997ED77153098` (`code`),
  KEY `IDX_5D6997EDC54C8C93` (`type_id`),
  CONSTRAINT `FK_5D6997EDC54C8C93` FOREIGN KEY (`type_id`) REFERENCES `pim_catalog_group_type` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_group_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_group_product` (
  `product_id` int NOT NULL,
  `group_id` int NOT NULL,
  PRIMARY KEY (`product_id`,`group_id`),
  KEY `IDX_7AC0C83A4584665A` (`product_id`),
  KEY `IDX_7AC0C83AFE54D947` (`group_id`),
  CONSTRAINT `FK_7AC0C83A4584665A` FOREIGN KEY (`product_id`) REFERENCES `pim_catalog_product` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_7AC0C83AFE54D947` FOREIGN KEY (`group_id`) REFERENCES `pim_catalog_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_group_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_group_translation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `foreign_key` int DEFAULT NULL,
  `label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `locale` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale_foreign_key_idx` (`locale`,`foreign_key`),
  KEY `IDX_622D98DB7E366551` (`foreign_key`),
  CONSTRAINT `FK_622D98DB7E366551` FOREIGN KEY (`foreign_key`) REFERENCES `pim_catalog_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_group_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_group_type` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_693B2EF777153098` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_group_type_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_group_type_translation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `foreign_key` int DEFAULT NULL,
  `label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `locale` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale_foreign_key_idx` (`locale`,`foreign_key`),
  KEY `IDX_6EF81AEB7E366551` (`foreign_key`),
  CONSTRAINT `FK_6EF81AEB7E366551` FOREIGN KEY (`foreign_key`) REFERENCES `pim_catalog_group_type` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_locale`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_locale` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_activated` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_7405C7B177153098` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=211 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_product` (
  `id` int NOT NULL AUTO_INCREMENT,
  `family_id` int DEFAULT NULL,
  `product_model_id` int DEFAULT NULL,
  `family_variant_id` int DEFAULT NULL,
  `is_enabled` tinyint(1) NOT NULL,
  `identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `raw_values` json NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `quantified_associations` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_91CD19C0772E836A` (`identifier`),
  KEY `IDX_91CD19C0C35E566A` (`family_id`),
  KEY `IDX_91CD19C0B2C5DD70` (`product_model_id`),
  KEY `IDX_91CD19C08A37AD0` (`family_variant_id`),
  CONSTRAINT `FK_91CD19C08A37AD0` FOREIGN KEY (`family_variant_id`) REFERENCES `pim_catalog_family_variant` (`id`),
  CONSTRAINT `FK_91CD19C0B2C5DD70` FOREIGN KEY (`product_model_id`) REFERENCES `pim_catalog_product_model` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_91CD19C0C35E566A` FOREIGN KEY (`family_id`) REFERENCES `pim_catalog_family` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_product_model`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_product_model` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_id` int DEFAULT NULL,
  `family_variant_id` int DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `raw_values` json NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `quantified_associations` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_5943911E77153098` (`code`),
  KEY `IDX_5943911E727ACA70` (`parent_id`),
  KEY `IDX_5943911E8A37AD0` (`family_variant_id`),
  CONSTRAINT `FK_5943911E727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `pim_catalog_product_model` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_5943911E8A37AD0` FOREIGN KEY (`family_variant_id`) REFERENCES `pim_catalog_family_variant` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_product_model_association`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_product_model_association` (
  `id` int NOT NULL AUTO_INCREMENT,
  `association_type_id` int NOT NULL,
  `owner_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale_foreign_key_idx` (`owner_id`,`association_type_id`),
  KEY `IDX_F5F4C8CAB1E1C39` (`association_type_id`),
  KEY `IDX_F5F4C8CA7E3C61F9` (`owner_id`),
  CONSTRAINT `FK_F5F4C8CA7E3C61F9` FOREIGN KEY (`owner_id`) REFERENCES `pim_catalog_product_model` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_F5F4C8CAB1E1C39` FOREIGN KEY (`association_type_id`) REFERENCES `pim_catalog_association_type` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_product_unique_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_product_unique_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `attribute_id` int NOT NULL,
  `raw_data` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_value_idx` (`attribute_id`,`raw_data`),
  KEY `IDX_E0768BA34584665A` (`product_id`),
  KEY `IDX_E0768BA3B6E62EFA` (`attribute_id`),
  CONSTRAINT `FK_E0768BA34584665A` FOREIGN KEY (`product_id`) REFERENCES `pim_catalog_product` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_E0768BA3B6E62EFA` FOREIGN KEY (`attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_variant_attribute_set_has_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_variant_attribute_set_has_attributes` (
  `variant_attribute_set_id` int NOT NULL,
  `attributes_id` int NOT NULL,
  PRIMARY KEY (`variant_attribute_set_id`,`attributes_id`),
  KEY `IDX_E9C4264A11D06F0E` (`variant_attribute_set_id`),
  KEY `IDX_E9C4264ABAAF4009` (`attributes_id`),
  CONSTRAINT `FK_E9C4264A11D06F0E` FOREIGN KEY (`variant_attribute_set_id`) REFERENCES `pim_catalog_family_variant_attribute_set` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_E9C4264ABAAF4009` FOREIGN KEY (`attributes_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_catalog_variant_attribute_set_has_axes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_catalog_variant_attribute_set_has_axes` (
  `variant_attribute_set_id` int NOT NULL,
  `axes_id` int NOT NULL,
  PRIMARY KEY (`variant_attribute_set_id`,`axes_id`),
  KEY `IDX_6965051E11D06F0E` (`variant_attribute_set_id`),
  KEY `IDX_6965051EE684FCEE` (`axes_id`),
  CONSTRAINT `FK_6965051E11D06F0E` FOREIGN KEY (`variant_attribute_set_id`) REFERENCES `pim_catalog_family_variant_attribute_set` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6965051EE684FCEE` FOREIGN KEY (`axes_id`) REFERENCES `pim_catalog_attribute` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_comment_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_comment_comment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_id` int DEFAULT NULL,
  `author_id` int DEFAULT NULL,
  `resource_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `resource_id` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `replied_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_2A32D03D727ACA70` (`parent_id`),
  KEY `IDX_2A32D03DF675F31B` (`author_id`),
  KEY `resource_name_resource_id_idx` (`resource_name`,`resource_id`),
  CONSTRAINT `FK_2A32D03D727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `pim_comment_comment` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2A32D03DF675F31B` FOREIGN KEY (`author_id`) REFERENCES `oro_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_configuration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_configuration` (
  `code` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `values` json NOT NULL,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_data_quality_insights_attribute_group_activation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_data_quality_insights_attribute_group_activation` (
  `attribute_group_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `activated` tinyint NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`attribute_group_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_data_quality_insights_dashboard_scores_projection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_data_quality_insights_dashboard_scores_projection` (
  `type` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `scores` json NOT NULL,
  PRIMARY KEY (`type`,`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_data_quality_insights_product_criteria_evaluation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_data_quality_insights_product_criteria_evaluation` (
  `product_id` int NOT NULL,
  `criterion_code` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `evaluated_at` datetime DEFAULT NULL,
  `status` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `result` json DEFAULT NULL,
  PRIMARY KEY (`product_id`,`criterion_code`),
  KEY `status_index` (`status`),
  CONSTRAINT `FK_dqi_product_criteria_evaluation` FOREIGN KEY (`product_id`) REFERENCES `pim_catalog_product` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_data_quality_insights_product_model_criteria_evaluation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_data_quality_insights_product_model_criteria_evaluation` (
  `product_id` int NOT NULL,
  `criterion_code` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `evaluated_at` datetime DEFAULT NULL,
  `status` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `result` json DEFAULT NULL,
  PRIMARY KEY (`product_id`,`criterion_code`),
  KEY `status_index` (`status`),
  CONSTRAINT `FK_dqi_product_model_criteria_evaluation` FOREIGN KEY (`product_id`) REFERENCES `pim_catalog_product_model` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_data_quality_insights_product_score`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_data_quality_insights_product_score` (
  `product_id` int NOT NULL,
  `evaluated_at` date NOT NULL,
  `scores` json NOT NULL,
  PRIMARY KEY (`product_id`,`evaluated_at`),
  KEY `evaluated_at_index` (`evaluated_at`),
  CONSTRAINT `FK_dqi_product_score` FOREIGN KEY (`product_id`) REFERENCES `pim_catalog_product` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_datagrid_view`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_datagrid_view` (
  `id` int NOT NULL AUTO_INCREMENT,
  `owner_id` int DEFAULT NULL,
  `label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `datagrid_alias` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `columns` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `filters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `IDX_B56B38F17E3C61F9` (`owner_id`),
  CONSTRAINT `FK_B56B38F17E3C61F9` FOREIGN KEY (`owner_id`) REFERENCES `oro_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_index_migration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_index_migration` (
  `index_alias` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `hash` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `values` json NOT NULL,
  UNIQUE KEY `unique_idx` (`index_alias`,`hash`),
  KEY `migration_index` (`index_alias`,`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_notification_notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_notification_notification` (
  `id` int NOT NULL AUTO_INCREMENT,
  `route` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `routeParams` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `message` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `messageParams` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `comment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created` datetime NOT NULL,
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_notification_user_notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_notification_user_notification` (
  `id` int NOT NULL AUTO_INCREMENT,
  `notification` int DEFAULT NULL,
  `user` int DEFAULT NULL,
  `viewed` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_342AA855BF5476CA` (`notification`),
  KEY `IDX_342AA8558D93D649` (`user`),
  CONSTRAINT `FK_342AA8558D93D649` FOREIGN KEY (`user`) REFERENCES `oro_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_342AA855BF5476CA` FOREIGN KEY (`notification`) REFERENCES `pim_notification_notification` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_one_time_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_one_time_task` (
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `values` json NOT NULL,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_session` (
  `sess_id` varbinary(128) NOT NULL,
  `sess_data` blob NOT NULL,
  `sess_time` int unsigned NOT NULL,
  `sess_lifetime` int unsigned NOT NULL,
  PRIMARY KEY (`sess_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_user_default_datagrid_view`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_user_default_datagrid_view` (
  `user_id` int NOT NULL,
  `view_id` int NOT NULL,
  PRIMARY KEY (`user_id`,`view_id`),
  KEY `IDX_3CEEC2F2A76ED395` (`user_id`),
  KEY `IDX_3CEEC2F231518C7` (`view_id`),
  CONSTRAINT `FK_3CEEC2F231518C7` FOREIGN KEY (`view_id`) REFERENCES `pim_datagrid_view` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_3CEEC2F2A76ED395` FOREIGN KEY (`user_id`) REFERENCES `oro_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pim_versioning_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pim_versioning_version` (
  `id` int NOT NULL AUTO_INCREMENT,
  `author` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `resource_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `resource_id` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:array)',
  `changeset` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `context` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `version` int DEFAULT NULL,
  `logged_at` datetime NOT NULL,
  `pending` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pending_idx` (`pending`),
  KEY `version_idx` (`version`),
  KEY `logged_at_idx` (`logged_at`),
  KEY `resource_name_resource_id_version_idx` (`resource_name`,`resource_id`,`version`)
) ENGINE=InnoDB AUTO_INCREMENT=262 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
SQL);

        // Fixtures
        $this->executeLargeQuery(<<<SQL
SET FOREIGN_KEY_CHECKS = 0;
INSERT INTO `acme_reference_data_color` VALUES (7,'colorA',1,'colorA','#colora',31,95,52,28,72,0,45,35),(8,'colorB',1,'colorB','#colorb',49,54,16,46,29,79,12,95),(9,'colorc',1,'colorc','#colorc',10,45,20,88,8,59,19,32);
INSERT INTO `acme_reference_data_fabric` VALUES (5,'fabricA',1,'fabricA',NULL),(6,'fabricB',1,'fabricB',NULL);
INSERT INTO `akeneo_file_storage_file_info` VALUES (35,'8/b/5/c/8b5cf9bfd2e7e4725fd581e03251133ada1b2c99_fileA.txt','fileA.txt','text/plain',1048576,'txt','6545089471ba53d660d22d7b8dc8dd67904b1e28','catalogStorage'),(36,'3/b/5/5/3b5548f9764c0535db2ac92f047fa448cb7cea76_imageA.jpg','imageA.jpg','image/jpeg',1048576,'jpg','a9453e6ce89dbfd776ecae609e1c23e9cfad8277','catalogStorage'),(37,'7/1/3/3/713380965740f8838834cd58505aa329fcf448a5_imageB_en_US.jpg','imageB-en_US.jpg','image/jpeg',1048576,'jpg','16850b6741c6e0d7622edb29465488571a2e63df','catalogStorage'),(38,'0/5/1/9/05198fcf21b2b0d4596459f172e2e62b1a70bfd0_imageB_fr_FR.jpg','imageB-fr_FR.jpg','image/jpeg',1048576,'jpg','058c6f380b0888afadf7341f8baaf58b538e5774','catalogStorage');
INSERT INTO `pim_catalog_association_type` (`id`, `code`, `created`, `updated`, `is_quantified`) VALUES
(865, 'X_SELL', '2016-10-04 16:14:44', '2016-10-04 16:14:44', false),
(866, 'UPSELL', '2016-10-04 16:14:44', '2016-10-04 16:14:44', false),
(867, 'SUBSTITUTION', '2016-10-04 16:14:44', '2016-10-04 16:14:44', false),
(868, 'PACK', '2016-10-04 16:14:44', '2016-10-04 16:14:44', false),
(869, 'PRODUCT_SET', '2016-10-04 16:14:44', '2016-10-04 16:14:44', true);
INSERT INTO `pim_catalog_attribute_group` VALUES (593,'other',100,'2016-08-04 14:28:49','2016-08-04 14:28:49'),(594,'attributeGroupA',0,'2016-08-04 14:28:49','2016-08-04 14:28:49'),(595,'attributeGroupB',0,'2016-08-04 14:28:49','2016-08-04 14:28:49');

DELETE FROM pim_catalog_attribute_locale;
DELETE FROM pim_catalog_attribute;
INSERT INTO `pim_catalog_attribute` VALUES
(2523,594,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,1,1,0,0,'sku','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_identifier','text','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49', '{"en_US":"this is the sku"}'),
(2524,594,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_date','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_date','date','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49', '{"en_US":"date guidelines"}'),
(2525,594,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_file','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_file','media','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49', '{}'),
(2526,595,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'an_image','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_image','media','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49', '{}'),
(2527,595,0,0,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'Power','KILOWATT',NULL,'',NULL,0,0,0,0,'a_metric','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_metric','metric','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49', '{}'),
(2528,595,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_multi_select','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_multiselect','options','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49', '{}'),
(2529,595,0,0,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_number_float','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_number','decimal','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49', '{}'),
(2530,595,0,0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_number_float_negative','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_number','decimal','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49', '{}'),
(2531,595,0,0,NULL,NULL,NULL,NULL,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_number_integer','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_number','decimal','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49', '{}'),
(2532,594,0,0,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_price','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_price_collection','prices','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49', '{}'),
(2533,594,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_ref_data_multi_select','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_reference_data_multiselect','reference_data_options','a:1:{s:19:\"reference_data_name\";s:7:\"fabrics\";}','2016-08-04 14:28:49','2016-08-04 14:28:49', '{}'),
(2534,594,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_ref_data_simple_select','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_reference_data_simpleselect','reference_data_option','a:1:{s:19:\"reference_data_name\";s:5:\"color\";}','2016-08-04 14:28:49','2016-08-04 14:28:49', '{}'),
(2535,595,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_simple_select','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_simpleselect','option','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:50','2016-08-04 14:28:50', '{}'),
(2536,594,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_text','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_text','text','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:50','2016-08-04 14:28:50', '{}'),
(2537,594,0,0,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_text_area','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_textarea','textarea','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:50','2016-08-04 14:28:50', '{}'),
(2538,594,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_yes_no','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_boolean','boolean','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:50','2016-08-04 14:28:50', '{}'),
(2539,595,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,1,0,'a_localizable_image','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_image','media','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:50','2016-08-04 14:28:50', '{}'),
(2540,594,0,0,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,1,'a_scopable_price','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_price_collection','prices','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:50','2016-08-04 14:28:50', '{}'),
(2541,594,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,1,1,'a_localized_and_scopable_text_area','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_textarea','textarea','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:50','2016-08-04 14:28:50', '{}'),
(2542,595,0,0,NULL,NULL,NULL,NULL,NULL,NULL,0,0,NULL,NULL,'Length','METER',NULL,'',NULL,0,0,0,0,'a_metric_without_decimal','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_metric','metric','a:1:{s:19:"reference_data_name";N;}','2016-10-05 13:52:34','2016-10-05 13:52:34', '{}'),
(2543,595,0,0,NULL,NULL,NULL,NULL,NULL,NULL,0,1,NULL,NULL,'Temperature','CELSIUS',NULL,'',NULL,0,0,0,0,'a_metric_without_decimal_negative','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_metric','metric','a:1:{s:19:"reference_data_name";N;}','2016-10-05 13:52:34','2016-10-05 13:52:34', '{}'),
(2544,595,0,0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'Temperature','CELSIUS',NULL,'',NULL,0,0,0,0,'a_metric_negative','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_metric','metric','a:1:{s:19:"reference_data_name";N;}','2016-10-05 13:52:34','2016-10-05 13:52:34', '{}'),
(2545,595,0,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_price_without_decimal','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_price_collection','prices','a:1:{s:19:"reference_data_name";N;}','2016-10-05 13:52:34','2016-10-05 13:52:34', '{}'),
(2546,595,0,0,NULL,NULL,NULL,NULL,NULL,NULL,0,1,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_number_integer_negative','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_number','decimal','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49', '{}'),
(2547,594,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'123','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_text','text','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:50','2016-08-04 14:28:50', '{}');

INSERT INTO `pim_catalog_attribute_option` VALUES (3801,2528,'optionA',1),(3802,2528,'optionB',1),(3803,2535,'optionA',1),(3804,2535,'optionB',1);

INSERT INTO `pim_catalog_attribute_requirement` VALUES (6705,466,2523,209,1),(6706,466,2523,210,1),(6707,466,2524,209,1),(6708,466,2525,209,1),(6709,466,2527,209,1),(6710,466,2528,209,1),(6711,466,2529,209,1),(6712,466,2530,209,1),(6713,466,2531,209,1),(6714,466,2532,209,1),(6715,466,2533,209,1),(6716,466,2534,209,1),(6717,466,2535,209,1),(6718,466,2536,209,1),(6719,466,2537,209,1),(6720,466,2538,209,1),(6721,466,2526,209,1),(6722,466,2539,209,1),(6723,466,2540,209,1),(6724,466,2541,209,1),(6725,466,2524,210,1),(6726,466,2525,210,1),(6727,466,2527,210,1),(6728,466,2528,210,1),(6729,466,2529,210,1),(6730,466,2530,210,1),(6731,466,2531,210,1),(6732,466,2532,210,1),(6733,466,2533,210,1),(6734,466,2534,210,1),(6735,466,2535,210,1),(6736,466,2536,210,1),(6737,466,2537,210,1),(6738,466,2538,210,1),(6739,466,2526,210,1),(6740,466,2539,210,1),(6741,466,2540,210,1),(6742,466,2541,210,1);

INSERT INTO `pim_catalog_attribute_translation` VALUES (3521,2523,'SKU','en_US');

INSERT INTO `pim_catalog_category` (`id`, `parent_id`, `code`, `created`, `updated`, `root`, `lvl`, `lft`, `rgt`) VALUES
(895,NULL,'master','2016-08-04 14:28:49','2016-08-04 14:28:49',895,0,1,10),
(896,895,'categoryA','2016-08-04 14:28:49','2016-08-04 14:28:49',895,1,2,7),
(897,896,'categoryA1','2016-08-04 14:28:49','2016-08-04 14:28:49',895,2,3,4),
(898,896,'categoryA2','2016-08-04 14:28:49','2016-08-04 14:28:49',895,2,5,6),
(899,895,'categoryB','2016-08-04 14:28:49','2016-08-04 14:28:49',895,1,8,9);

INSERT INTO `pim_catalog_channel` VALUES (209,895,'ecommerce','a:0:{}'),(210,895,'tablet','a:0:{}');

INSERT INTO `pim_catalog_currency` VALUES (10101,'USD',1),(10102,'EUR',1);

INSERT INTO `pim_catalog_channel_currency` VALUES (209,10101),(210,10102);

INSERT INTO `pim_catalog_channel_locale` VALUES (209,21058),(210,21039),(210,21058),(210,21090);

INSERT INTO `pim_catalog_family` VALUES
(466,2523,NULL,'familyA','2016-08-04 14:28:50','2016-08-04 14:28:50'),
(467,2523,NULL,'familyB','2016-08-04 14:28:50','2016-08-04 14:28:50');

INSERT INTO `pim_catalog_family_attribute` VALUES (466,2523),(466,2524),(466,2525),(466,2526),(466,2527),(466,2528),(466,2529),(466,2530),(466,2531),(466,2532),(466,2533),(466,2534),(466,2535),(466,2536),(466,2537),(466,2538),(466,2539),(466,2540),(466,2541);

INSERT INTO `pim_catalog_family_variant` (`id`, `family_id`, `code`) VALUES
(25,	466,	'familyVariantA1'),
(26,	466,	'familyVariantA2'),
(27,	466,	'familyVariantB1');

INSERT INTO `pim_catalog_group` VALUES (240,323,'groupA'),(241,323,'groupB');

INSERT INTO `pim_catalog_group_type` VALUES (323,'RELATED');

INSERT INTO `pim_catalog_locale` VALUES (21001,'af_ZA',0),(21002,'am_ET',0),(21003,'ar_AE',0),(21004,'ar_BH',0),(21005,'ar_DZ',0),(21006,'ar_EG',0),(21007,'ar_IQ',0),(21008,'ar_JO',0),(21009,'ar_KW',0),(21010,'ar_LB',0),(21011,'ar_LY',0),(21012,'ar_MA',0),(21013,'arn_CL',0),(21014,'ar_OM',0),(21015,'ar_QA',0),(21016,'ar_SA',0),(21017,'ar_SY',0),(21018,'ar_TN',0),(21019,'ar_YE',0),(21020,'as_IN',0),(21021,'az_Cyrl_AZ',0),(21022,'az_Latn_AZ',0),(21023,'ba_RU',0),(21024,'be_BY',0),(21025,'bg_BG',0),(21026,'bn_BD',0),(21027,'bn_IN',0),(21028,'bo_CN',0),(21029,'br_FR',0),(21030,'bs_Cyrl_BA',0),(21031,'bs_Latn_BA',0),(21032,'ca_ES',0),(21033,'co_FR',0),(21034,'cs_CZ',0),(21035,'cy_GB',0),(21036,'da_DK',0),(21037,'de_AT',0),(21038,'de_CH',0),(21039,'de_DE',1),(21040,'de_LI',0),(21041,'de_LU',0),(21042,'dsb_DE',0),(21043,'dv_MV',0),(21044,'el_GR',0),(21045,'en_029',0),(21046,'en_AU',0),(21047,'en_BZ',0),(21048,'en_CA',0),(21049,'en_GB',0),(21050,'en_IE',0),(21051,'en_IN',0),(21052,'en_JM',0),(21053,'en_MY',0),(21054,'en_NZ',0),(21055,'en_PH',0),(21056,'en_SG',0),(21057,'en_TT',0),(21058,'en_US',1),(21059,'en_ZA',0),(21060,'en_ZW',0),(21061,'es_AR',0),(21062,'es_BO',0),(21063,'es_CL',0),(21064,'es_CO',0),(21065,'es_CR',0),(21066,'es_DO',0),(21067,'es_EC',0),(21068,'es_ES',0),(21069,'es_GT',0),(21070,'es_HN',0),(21071,'es_MX',0),(21072,'es_NI',0),(21073,'es_PA',0),(21074,'es_PE',0),(21075,'es_PR',0),(21076,'es_PY',0),(21077,'es_SV',0),(21078,'es_US',0),(21079,'es_UY',0),(21080,'es_VE',0),(21081,'et_EE',0),(21082,'eu_ES',0),(21083,'fa_IR',0),(21084,'fi_FI',0),(21085,'fil_PH',0),(21086,'fo_FO',0),(21087,'fr_BE',0),(21088,'fr_CA',0),(21089,'fr_CH',0),(21090,'fr_FR',1),(21091,'fr_LU',0),(21092,'fr_MC',0),(21093,'fy_NL',0),(21094,'ga_IE',0),(21095,'gd_GB',0),(21096,'gl_ES',0),(21097,'gsw_FR',0),(21098,'gu_IN',0),(21099,'ha_Latn_NG',0),(21100,'he_IL',0),(21101,'hi_IN',0),(21102,'hr_BA',0),(21103,'hr_HR',0),(21104,'hsb_DE',0),(21105,'hu_HU',0),(21106,'hy_AM',0),(21107,'id_ID',0),(21108,'ig_NG',0),(21109,'ii_CN',0),(21110,'is_IS',0),(21111,'it_CH',0),(21112,'it_IT',0),(21113,'iu_Cans_CA',0),(21114,'iu_Latn_CA',0),(21115,'ja_JP',0),(21116,'ka_GE',0),(21117,'kk_KZ',0),(21118,'kl_GL',0),(21119,'km_KH',0),(21120,'kn_IN',0),(21121,'kok_IN',0),(21122,'ko_KR',0),(21123,'ky_KG',0),(21124,'lb_LU',0),(21125,'lo_LA',0),(21126,'lt_LT',0),(21127,'lv_LV',0),(21128,'mi_NZ',0),(21129,'mk_MK',0),(21130,'ml_IN',0),(21131,'mn_MN',0),(21132,'mn_Mong_CN',0),(21133,'moh_CA',0),(21134,'mr_IN',0),(21135,'ms_BN',0),(21136,'ms_MY',0),(21137,'mt_MT',0),(21138,'nb_NO',0),(21139,'ne_NP',0),(21140,'nl_BE',0),(21141,'nl_NL',0),(21142,'nn_NO',0),(21143,'nso_ZA',0),(21144,'oc_FR',0),(21145,'or_IN',0),(21146,'pa_IN',0),(21147,'pl_PL',0),(21148,'prs_AF',0),(21149,'ps_AF',0),(21150,'pt_BR',0),(21151,'pt_PT',0),(21152,'qut_GT',0),(21153,'quz_BO',0),(21154,'quz_EC',0),(21155,'quz_PE',0),(21156,'rm_CH',0),(21157,'ro_RO',0),(21158,'ru_RU',0),(21159,'rw_RW',0),(21160,'sah_RU',0),(21161,'sa_IN',0),(21162,'se_FI',0),(21163,'se_NO',0),(21164,'se_SE',0),(21165,'si_LK',0),(21166,'sk_SK',0),(21167,'sl_SI',0),(21168,'sma_NO',0),(21169,'sma_SE',0),(21170,'smj_NO',0),(21171,'smj_SE',0),(21172,'smn_FI',0),(21173,'sms_FI',0),(21174,'sq_AL',0),(21175,'sr_Cyrl_BA',0),(21176,'sr_Cyrl_CS',0),(21177,'sr_Cyrl_ME',0),(21178,'sr_Cyrl_RS',0),(21179,'sr_Latn_BA',0),(21180,'sr_Latn_CS',0),(21181,'sr_Latn_ME',0),(21182,'sr_Latn_RS',0),(21183,'sv_FI',0),(21184,'sv_SE',0),(21185,'sw_KE',0),(21186,'syr_SY',0),(21187,'ta_IN',0),(21188,'te_IN',0),(21189,'tg_Cyrl_TJ',0),(21190,'th_TH',0),(21191,'tk_TM',0),(21192,'tn_ZA',0),(21193,'tr_TR',0),(21194,'tt_RU',0),(21195,'tzm_Latn_DZ',0),(21196,'ug_CN',0),(21197,'uk_UA',0),(21198,'ur_PK',0),(21199,'uz_Cyrl_UZ',0),(21200,'uz_Latn_UZ',0),(21201,'vi_VN',0),(21202,'wo_SN',0),(21203,'xh_ZA',0),(21204,'yo_NG',0),(21205,'zh_CN',0),(21206,'zh_HK',0),(21207,'zh_MO',0),(21208,'zh_SG',0),(21209,'zh_TW',0),(21210,'zu_ZA',0);


INSERT INTO `pim_catalog_product_model` (`id`, `code`, `family_variant_id`, `parent_id`, `created`, `updated`, `raw_values`) VALUES
(147,    'bar',  27, NULL,   '2016-06-14 11:12:50',  '2016-06-14 11:12:50',  '{\"sku\": {\"<all_channels>\": {\"<all_locales>\": \"bar\"}}}'),
(148,    'baz',  27,   NULL,   '2016-06-14 11:12:50',  '2016-06-14 11:12:50',  '{\"sku\": {\"<all_channels>\": {\"<all_locales>\": \"baz\"}}}'),
(149,    'foo',  27, NULL,    '2016-06-14 11:12:50',  '2016-06-14 11:12:50',  '{\"sku\": {\"<all_channels>\": {\"<all_locales>\": \"foo\"}},\"a_file\":{\"<all_channels>\":{\"<all_locales>\":\"8/b/5/c/8b5cf9bfd2e7e4725fd581e03251133ada1b2c99_fileA.txt\"}},\"an_image\":{\"<all_channels>\":{\"<all_locales>\":\"3/b/5/5/3b5548f9764c0535db2ac92f047fa448cb7cea76_imageA.jpg\"}},\"a_date\":{\"<all_channels>\":{\"<all_locales>\":\"2016-06-13T00:00:00+02:00\"}},\"a_metric\":{\"<all_channels>\":{\"<all_locales>\":{\"amount\":\"987654321987.1234\",\"unit\":\"KILOWATT\",\"base_data\":\"987654321987123.4000\",\"base_unit\":\"WATT\",\"family\":\"Power\"}}},\"a_metric_without_decimal\":{\"<all_channels>\":{\"<all_locales>\":{\"amount\":98,\"unit\":\"CENTIMETER\",\"base_data\":\"0.98\",\"base_unit\":\"METER\",\"family\":\"Length\"}}},\"a_metric_without_decimal_negative\":{\"<all_channels>\":{\"<all_locales>\":{\"amount\":-20,\"unit\":\"CELSIUS\",\"base_data\":\"253.150000000000\",\"base_unit\":\"KELVIN\",\"family\":\"Temperature\"}}},\"a_metric_negative\":{\"<all_channels>\":{\"<all_locales>\":{\"amount\":\"-20.5000\",\"unit\":\"CELSIUS\",\"base_data\":\"252.650000000000\",\"base_unit\":\"KELVIN\",\"family\":\"Temperature\"}}},\"a_multi_select\":{\"<all_channels>\":{\"<all_locales>\":[\"optionA\",\"optionB\"]}},\"a_number_float\":{\"<all_channels>\":{\"<all_locales>\":\"12.5678\"}},\"a_number_float_negative\":{\"<all_channels>\":{\"<all_locales>\":\"-99.8732\"}},\"a_number_integer\":{\"<all_channels>\":{\"<all_locales>\":42}},\"a_number_integer_negative\":{\"<all_channels>\":{\"<all_locales>\":-42}},\"a_price\":{\"<all_channels>\":{\"<all_locales>\":[{\"amount\":\"45.00\",\"currency\":\"USD\"},{\"amount\":\"56.53\",\"currency\":\"EUR\"}]}},\"a_price_without_decimal\":{\"<all_channels>\":{\"<all_locales>\":[{\"amount\":-45,\"currency\":\"USD\"},{\"amount\":56,\"currency\":\"EUR\"}]}},\"a_ref_data_multi_select\":{\"<all_channels>\":{\"<all_locales>\":[\"fabricA\",\"fabricB\"]}},\"a_ref_data_simple_select\":{\"<all_channels>\":{\"<all_locales>\":\"colorB\"}},\"a_simple_select\":{\"<all_channels>\":{\"<all_locales>\":\"optionB\"}},\"a_text\":{\"<all_channels>\":{\"<all_locales>\":\"this is a text\"}},\"123\":{\"<all_channels>\":{\"<all_locales>\":\"a text for an attribute with numerical code\"}},\"a_text_area\":{\"<all_channels>\":{\"<all_locales>\":\"this is a very very very very very long  text\"}},\"a_yes_no\":{\"<all_channels>\":{\"<all_locales>\":true}},\"a_localizable_image\":{\"<all_channels>\":{\"en_US\":\"7/1/3/3/713380965740f8838834cd58505aa329fcf448a5_imageB_en_US.jpg\",\"fr_FR\":\"0/5/1/9/05198fcf21b2b0d4596459f172e2e62b1a70bfd0_imageB_fr_FR.jpg\"}},\"a_scopable_price\":{\"ecommerce\":{\"<all_locales>\":[{\"amount\":\"15.00\",\"currency\":\"EUR\"},{\"amount\":\"20.00\",\"currency\":\"USD\"}]},\"tablet\":{\"<all_locales>\":[{\"amount\":\"17.00\",\"currency\":\"EUR\"},{\"amount\":\"24.00\",\"currency\":\"USD\"}]}},\"a_localized_and_scopable_text_area\":{\"ecommerce\":{\"en_US\":\"a text area for ecommerce in English\"},\"tablet\":{\"en_US\":\"a text area for tablets in English\",\"fr_FR\":\"une zone de texte pour les tablettes en français\"}}}'),
(150,    'qux',  25, NULL,   '2016-06-14 11:12:50',  '2016-06-14 11:12:50',  '{\"a_text\": {\"<all_channels>\": {\"<all_locales>\": \"this is a text\"}}}'),
(151,    'quux', 25, 150,   '2016-06-14 11:12:50',  '2016-06-14 11:12:50',  '{\"a_simple_select\": {\"<all_channels>\": {\"<all_locales>\": \"optionB\"}}}');


INSERT INTO `pim_catalog_product` (`id`, `identifier`, `family_id`, `family_variant_id`, `product_model_id`, `is_enabled`, `created`, `updated`, `raw_values`, `quantified_associations`) VALUES
(47,    'bar', NULL, NULL,  NULL, 0,  '2016-06-14 11:12:50',  '2016-06-14 11:12:50',  '{\"sku\": {\"<all_channels>\": {\"<all_locales>\": \"bar\"}}}', null),
(48,    'baz', NULL, NULL,  NULL, 1,  '2016-06-14 11:12:50',  '2016-06-14 11:12:50',  '{\"sku\": {\"<all_channels>\": {\"<all_locales>\": \"baz\"}}}', null),
(49,    'foo', 466,  NULL,  NULL, 1,  '2016-06-14 11:12:50',  '2016-06-14 11:12:50',  '{\"sku\": {\"<all_channels>\": {\"<all_locales>\": \"foo\"}},\"a_file\":{\"<all_channels>\":{\"<all_locales>\":\"8/b/5/c/8b5cf9bfd2e7e4725fd581e03251133ada1b2c99_fileA.txt\"}},\"an_image\":{\"<all_channels>\":{\"<all_locales>\":\"3/b/5/5/3b5548f9764c0535db2ac92f047fa448cb7cea76_imageA.jpg\"}},\"a_date\":{\"<all_channels>\":{\"<all_locales>\":\"2016-06-13T00:00:00+02:00\"}},\"a_metric\":{\"<all_channels>\":{\"<all_locales>\":{\"amount\":\"987654321987.1234\",\"unit\":\"KILOWATT\",\"base_data\":\"987654321987123.4000\",\"base_unit\":\"WATT\",\"family\":\"Power\"}}},\"a_metric_without_decimal\":{\"<all_channels>\":{\"<all_locales>\":{\"amount\":98,\"unit\":\"CENTIMETER\",\"base_data\":\"0.98\",\"base_unit\":\"METER\",\"family\":\"Length\"}}},\"a_metric_without_decimal_negative\":{\"<all_channels>\":{\"<all_locales>\":{\"amount\":-20,\"unit\":\"CELSIUS\",\"base_data\":\"253.150000000000\",\"base_unit\":\"KELVIN\",\"family\":\"Temperature\"}}},\"a_metric_negative\":{\"<all_channels>\":{\"<all_locales>\":{\"amount\":\"-20.5000\",\"unit\":\"CELSIUS\",\"base_data\":\"252.650000000000\",\"base_unit\":\"KELVIN\",\"family\":\"Temperature\"}}},\"a_multi_select\":{\"<all_channels>\":{\"<all_locales>\":[\"optionA\",\"optionB\"]}},\"a_number_float\":{\"<all_channels>\":{\"<all_locales>\":\"12.5678\"}},\"a_number_float_negative\":{\"<all_channels>\":{\"<all_locales>\":\"-99.8732\"}},\"a_number_integer\":{\"<all_channels>\":{\"<all_locales>\":42}},\"a_number_integer_negative\":{\"<all_channels>\":{\"<all_locales>\":-42}},\"a_price\":{\"<all_channels>\":{\"<all_locales>\":[{\"amount\":\"45.00\",\"currency\":\"USD\"},{\"amount\":\"56.53\",\"currency\":\"EUR\"}]}},\"a_price_without_decimal\":{\"<all_channels>\":{\"<all_locales>\":[{\"amount\":-45,\"currency\":\"USD\"},{\"amount\":56,\"currency\":\"EUR\"}]}},\"a_ref_data_multi_select\":{\"<all_channels>\":{\"<all_locales>\":[\"fabricA\",\"fabricB\"]}},\"a_ref_data_simple_select\":{\"<all_channels>\":{\"<all_locales>\":\"colorB\"}},\"a_simple_select\":{\"<all_channels>\":{\"<all_locales>\":\"optionB\"}},\"a_text\":{\"<all_channels>\":{\"<all_locales>\":\"this is a text\"}},\"123\":{\"<all_channels>\":{\"<all_locales>\":\"a text for an attribute with numerical code\"}},\"a_text_area\":{\"<all_channels>\":{\"<all_locales>\":\"this is a very very very very very long  text\"}},\"a_yes_no\":{\"<all_channels>\":{\"<all_locales>\":true}},\"a_localizable_image\":{\"<all_channels>\":{\"en_US\":\"7/1/3/3/713380965740f8838834cd58505aa329fcf448a5_imageB_en_US.jpg\",\"fr_FR\":\"0/5/1/9/05198fcf21b2b0d4596459f172e2e62b1a70bfd0_imageB_fr_FR.jpg\"}},\"a_scopable_price\":{\"ecommerce\":{\"<all_locales>\":[{\"amount\":\"15.00\",\"currency\":\"EUR\"},{\"amount\":\"20.00\",\"currency\":\"USD\"}]},\"tablet\":{\"<all_locales>\":[{\"amount\":\"17.00\",\"currency\":\"EUR\"},{\"amount\":\"24.00\",\"currency\":\"USD\"}]}},\"a_localized_and_scopable_text_area\":{\"ecommerce\":{\"en_US\":\"a text area for ecommerce in English\"},\"tablet\":{\"en_US\":\"a text area for tablets in English\",\"fr_FR\":\"une zone de texte pour les tablettes en français\"}}}', '{\"PRODUCT_SET\":{\"products\":[{\"id\":47,\"quantity\": 3}], \"product_models\":[{\"id\":148,\"quantity\": 2}]}}'),
(50,    'qux', 466,  25,   151, 1,  '2016-06-14 11:12:50',  '2016-06-14 11:12:50',  '{\"sku\": {\"<all_channels>\": {\"<all_locales>\": \"qux\"}},\"a_yes_no\": {\"<all_channels>\": {\"<all_locales>\": true}}}', null),
(51,    'product_invalid_file', NULL,  NULL, NULL, 1,  '2016-06-14 11:12:50',  '2016-06-14 11:12:50',  '{\"sku\": {\"<all_channels>\": {\"<all_locales>\": \"product_invalid_file\"}},\"a_file\": {\"<all_channels>\": {\"<all_locales>\": \"file/path/that/does/not/exists/intentionnaly.png\"}}}', null),
(52,    'product_invalid_simple_reference_data', NULL,  NULL, NULL, 1,  '2016-06-14 11:12:50',  '2016-06-14 11:12:50',  '{\"sku\": {\"<all_channels>\": {\"<all_locales>\": \"product_invalid_simple_reference_data\"}},\"a_ref_data_simple_select\": {\"<all_channels>\": {\"<all_locales>\": \"invalid_reference_data_value\"}}}', null),
(53,    'product_invalid_multi_reference_data', NULL,  NULL, NULL, 1,  '2016-06-14 11:12:50',  '2016-06-14 11:12:50',  '{\"sku\": {\"<all_channels>\": {\"<all_locales>\": \"product_invalid_multi_reference_data\"}},\"a_ref_data_multi_select\": {\"<all_channels>\": {\"<all_locales>\": [\"fabricA\", \"invalid_reference_data_value\"]}}}', null);

INSERT INTO `pim_catalog_association` VALUES (9,865,49),(8,866,49),(7,868,49),(6,867,49);
INSERT INTO `pim_catalog_association_group` VALUES (8,240),(9,241);
INSERT INTO `pim_catalog_association_product` VALUES (7,47),(7,48),(9,47);
INSERT INTO `pim_catalog_completeness` VALUES (773,21058,209,49,0,19),(774,21039,210,49,2,19),(775,21058,210,49,0,19),(776,21090,210,49,0,19);
INSERT INTO `pim_catalog_group_product` VALUES (49,239),(49,240),(49,241);

INSERT INTO `pim_catalog_category_product_model` VALUES (150,896),(151,899),(151,897);

INSERT INTO `pim_catalog_category_product` VALUES (49,897),(49,899),(50,898);

INSERT INTO pim_catalog_association_type (id, code, created, updated, is_two_way, is_quantified) VALUES (10, 'SOIREEFOOD10', '2022-05-09 14:21:20', '2022-05-09 14:21:20', 0, 1);

UPDATE pim_catalog_product
SET quantified_associations = '{"SOIREEFOOD10":{"products":[{"id":48,"quantity":1000}]}}'
WHERE id = 47;

INSERT INTO pim_catalog_product_unique_data (id, product_id, attribute_id, raw_data) VALUES (1, 47, 2523, 'test');

INSERT INTO oro_user (id, file_info_id, ui_locale_id, username, email, name_prefix, first_name, middle_name, last_name, name_suffix, image, enabled, salt, password, confirmation_token, password_requested, last_login, login_count, createdAt, updatedAt, product_grid_filters, emailNotifications, phone, timezone, user_type, properties, consecutive_authentication_failure_counter, authentication_failure_reset_date, profile, catalogLocale_id, catalogScope_id, defaultTree_id)
VALUES (1, null, 21001, 'admin', 'admin@example.com', null, 'John', null, 'Doe', null, null, 1, 'a4w2owfhfvkg4ookowgo00840k4c0wo', 'KOPL5jIcF1cDQiA2vn2svIZvDW9kUeo9naG2kFW+VknTlzkMjgOZbpbMeWB71ax69weVHbAj2N1ZWG30SLeZSA==', null, null, null, 0, '2022-05-09 16:44:50', '2022-05-09 16:44:50', '[]', 0, null, 'UTC', 'user', '[]', 0, null, null, 21001, 209, 895);

INSERT INTO pim_comment_comment (id, parent_id, author_id, resource_name, resource_id, body, created_at, replied_at)
VALUES (1, null, 1, 'Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product', 47, 'comment', now(), now());

INSERT INTO pim_versioning_version (id, author, resource_name, resource_id, changeset, logged_at, pending)
VALUES (1, 1, 'Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product', 47, '{}', now(), 0);

SET FOREIGN_KEY_CHECKS = 1;
SQL);

        // INSERT ghost records in pim_catalog_completeness table
        for ($i = 1; $i <= 5; $i++) {
            $this->connection->executeQuery(
                <<<SQL
                INSERT INTO pim_catalog_completeness (locale_id, channel_id, product_id, missing_count, required_count)
                SELECT locale.id, channel.id, (SELECT MAX(id) + :i FROM pim_catalog_product), 1, 5
                FROM pim_catalog_locale locale, pim_catalog_channel channel
                SQL,
                ['i' => $i]
            );
        }

        // Check tables are not empty
        $tablesToCheck = [
            'pim_catalog_completeness',
            'pim_catalog_product',
            'pim_catalog_category_product',
            'pim_catalog_association',
            'pim_catalog_association_product',
            'pim_catalog_group_product',
            'pim_catalog_product_unique_data',
            'pim_comment_comment',
            'pim_versioning_version',
        ];
        foreach ($tablesToCheck as $tableName) {
            $count = (int) $this->connection->executeQuery(
                \strtr('SELECT count(*) FROM `{tableName}`', ['{tableName}' => $tableName])
            )->fetchOne();
            Assert::assertGreaterThan(0, $count, 'The ' . $tableName . ' is empty.');
        }
    }

    private function executeLargeQuery(string $query): void
    {
        $process = new Process([
            'mysql',
            '-h',
            \getenv('APP_DATABASE_HOST'),
            '-u',
            \getenv('APP_DATABASE_USER'),
            '-p' . \getenv('APP_DATABASE_PASSWORD'),
            \getenv('APP_DATABASE_NAME'),
        ]);
        $process->setTimeout(60);
        $process->setInput($query);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
