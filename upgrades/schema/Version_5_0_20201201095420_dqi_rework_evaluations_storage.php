<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20201201095420_dqi_rework_evaluations_storage extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
ALTER TABLE pim_data_quality_insights_product_criteria_evaluation
    RENAME TO pim_data_quality_insights_product_criteria_evaluation_depr, ALGORITHM=INSTANT;

CREATE TABLE pim_data_quality_insights_product_criteria_evaluation (
  product_id int NOT NULL,
  criterion_code varchar(40) NOT NULL,
  evaluated_at datetime NULL,
  status varchar(15) NOT NULL,
  result json DEFAULT NULL,
  PRIMARY KEY (product_id, criterion_code),
  INDEX status_index (status),
  CONSTRAINT FK_dqi_product_criteria_evaluation FOREIGN KEY (product_id) REFERENCES pim_catalog_product (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE pim_data_quality_insights_product_model_criteria_evaluation
    RENAME TO pim_data_quality_insights_product_model_criteria_evaluation_depr, ALGORITHM=INSTANT;

CREATE TABLE pim_data_quality_insights_product_model_criteria_evaluation (
  product_id int NOT NULL,
  criterion_code varchar(40) NOT NULL,
  evaluated_at datetime NULL,
  status varchar(15) NOT NULL,
  result json DEFAULT NULL,
  PRIMARY KEY (product_id, criterion_code),
  INDEX status_index (status),
  CONSTRAINT FK_dqi_product_model_criteria_evaluation FOREIGN KEY (product_id) REFERENCES pim_catalog_product_model (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
        );
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
