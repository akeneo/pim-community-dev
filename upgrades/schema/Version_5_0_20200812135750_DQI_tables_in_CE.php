<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20200812135750_DQI_tables_in_CE extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        if($this->tablesWithEEPrefixExist()) {
            $this->renameTables();
        } else {
            $this->createTables();
        }
    }

    private function renameTables(): void
    {
        $sql = <<<SQL
ALTER TABLE pimee_data_quality_insights_dashboard_rates_projection RENAME AS pim_data_quality_insights_dashboard_rates_projection;
ALTER TABLE pimee_data_quality_insights_product_axis_rates RENAME AS pim_data_quality_insights_product_axis_rates;
ALTER TABLE pimee_data_quality_insights_product_criteria_evaluation RENAME AS pim_data_quality_insights_product_criteria_evaluation;
ALTER TABLE pimee_data_quality_insights_product_model_axis_rates RENAME AS pim_data_quality_insights_product_model_axis_rates;
ALTER TABLE pimee_data_quality_insights_product_model_criteria_evaluation RENAME AS pim_data_quality_insights_product_model_criteria_evaluation;
SQL;
        $this->addSql($sql);
    }

    private function tablesWithEEPrefixExist(): bool
    {
        $stmt = $this->connection->executeQuery('SHOW TABLES LIKE "%pimee_data_quality_insights_dashboard_rates_projection%"');

        return $stmt->rowCount() === 1;
    }

    private function createTables(): void {
        $sql = <<<'SQL'
CREATE TABLE pim_data_quality_insights_product_criteria_evaluation (
  product_id int NOT NULL,
  criterion_code varchar(40) NOT NULL,
  evaluated_at datetime NULL,
  status varchar(15) NOT NULL,
  result json DEFAULT NULL,
  PRIMARY KEY (product_id, criterion_code),
  INDEX status_index (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pim_data_quality_insights_product_model_criteria_evaluation (
  product_id int NOT NULL,
  criterion_code varchar(40) NOT NULL,
  evaluated_at datetime NULL,
  status varchar(15) NOT NULL,
  result json DEFAULT NULL,
  PRIMARY KEY (product_id, criterion_code),
  INDEX status_index (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pim_data_quality_insights_product_axis_rates (
    product_id INT NOT NULL,
    axis_code VARCHAR(40) NOT NULL,
    evaluated_at DATE NOT NULL,
    rates JSON NOT NULL,
    PRIMARY KEY (product_id, axis_code, evaluated_at),
    INDEX evaluated_at_index (evaluated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pim_data_quality_insights_product_model_axis_rates (
    product_id INT NOT NULL,
    axis_code VARCHAR(40) NOT NULL,
    evaluated_at DATE NOT NULL,
    rates JSON NOT NULL,
    PRIMARY KEY (product_id, axis_code, evaluated_at),
    INDEX evaluated_at_index (evaluated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pim_data_quality_insights_dashboard_rates_projection (
    type VARCHAR(15) NOT NULL,
    code VARCHAR(100) NOT NULL,
    rates JSON NOT NULL,
    PRIMARY KEY (type, code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
