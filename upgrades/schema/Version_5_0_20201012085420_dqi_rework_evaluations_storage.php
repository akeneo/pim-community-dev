<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20201012085420_dqi_rework_evaluations_storage extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
ALTER TABLE pim_data_quality_insights_product_criteria_evaluation
    RENAME TO pim_data_quality_insights_product_criteria_evaluation_dep, ALGORITHM=INSTANT;

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
    RENAME TO pim_data_quality_insights_product_model_criteria_evaluation_dep, ALGORITHM=INSTANT;

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

        $this->addSql(<<<SQL
INSERT IGNORE INTO pim_data_quality_insights_product_criteria_evaluation (product_id, criterion_code, evaluated_at, status)
SELECT evaluation_dep.product_id, evaluation_dep.criterion_code, evaluation_dep.evaluated_at, 'pending'
FROM pim_data_quality_insights_product_criteria_evaluation_dep AS evaluation_dep
WHERE evaluation_dep.criterion_code != 'consistency_text_title_formatting';

DROP TABLE pim_data_quality_insights_product_criteria_evaluation_dep;

INSERT IGNORE INTO pim_data_quality_insights_product_model_criteria_evaluation (product_id, criterion_code, evaluated_at, status)
SELECT evaluation_dep.product_id, evaluation_dep.criterion_code, evaluation_dep.evaluated_at, 'pending'
FROM pim_data_quality_insights_product_model_criteria_evaluation_dep AS evaluation_dep
WHERE evaluation_dep.criterion_code != 'consistency_text_title_formatting';

DROP TABLE pim_data_quality_insights_product_model_criteria_evaluation_dep;
SQL
        );
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
