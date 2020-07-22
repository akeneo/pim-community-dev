<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\IrreversibleMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20200511122550_data_quality_insights_evaluations_on_structure extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(
            <<<SQL
CREATE TABLE IF NOT EXISTS pimee_dqi_attribute_spellcheck (
    attribute_code VARCHAR(100) NOT NULL PRIMARY KEY, 
    evaluated_at DATETIME NOT NULL, 
    to_improve TINYINT(1) DEFAULT NULL, 
    result JSON NOT NULL, 
    INDEX evaluated_at_index (evaluated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
    );

        $this->addSql(
            <<<SQL
CREATE TABLE IF NOT EXISTS pimee_dqi_attribute_option_spellcheck (
    attribute_code VARCHAR(100) NOT NULL,
    attribute_option_code VARCHAR(100) NOT NULL,
    evaluated_at DATETIME NOT NULL,
    to_improve TINYINT NULL,
    result JSON NOT NULL,
    PRIMARY KEY attribute_option_key (attribute_code, attribute_option_code),
    INDEX evaluated_at_index (evaluated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
        );

        $this->addSql(
            <<<SQL
CREATE TABLE IF NOT EXISTS pimee_dqi_attribute_quality (
    attribute_code VARCHAR(100) NOT NULL, 
    quality VARCHAR(20) NOT NULL, 
    PRIMARY KEY(attribute_code)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC;
SQL

        );

        $this->addSql(
            <<<SQL
UPDATE akeneo_batch_job_instance 
SET code = 'data_quality_insights_evaluations', job_name = 'data_quality_insights_evaluations', label = 'Data Quality Insights Evaluations'
WHERE code = 'data_quality_insights_evaluate_products_criteria';
SQL
        );

        $this->addSql(
            <<<SQL
INSERT IGNORE INTO pimee_data_quality_insights_product_criteria_evaluation (product_id, criterion_code, status)
SELECT id, 'consistency_attribute_option_spelling', 'pending' FROM pim_catalog_product
SQL
        );

        $this->addSql(
            <<<SQL
INSERT IGNORE INTO pimee_data_quality_insights_product_model_criteria_evaluation (product_id, criterion_code, status)
SELECT id, 'consistency_attribute_option_spelling', 'pending' FROM pim_catalog_product_model
SQL
        );
    }

    public function down(Schema $schema) : void
    {
        throw new IrreversibleMigrationException();
    }
}
