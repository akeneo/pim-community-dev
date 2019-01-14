<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version_3_0_20190114132218_create_franklin_insights_tables extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('
        CREATE TABLE `pimee_franklin_insights_identifier_mapping` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute_id` int(11) DEFAULT NULL,
  `franklin_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_7898784B6E62EFA` (`attribute_id`),
  KEY `franklin_code_idx` (`franklin_code`),
  CONSTRAINT `FK_7898784B6E62EFA` FOREIGN KEY (`attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
');
        $this->addSql('
        CREATE TABLE `pimee_franklin_insights_subscription` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subscription_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` int(11) NOT NULL,
  `raw_suggested_data` json DEFAULT NULL COMMENT \'(DC2Type:native_json)\',
  `misses_mapping` tinyint(1) NOT NULL,
  `requested_asin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_upc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_brand` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_mpn` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `franklin_insights_subscription_idx` (`subscription_id`),
  KEY `franklin_insights_product_idx` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
