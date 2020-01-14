<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Install\Query\InitDataQualityInsightsSchema;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version_4_0_20191023164418_data_quality_insights_create_tables extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $query = <<<'SQL'
CREATE TABLE pimee_data_quality_insights_criteria_evaluation (
  id varchar(40) NOT NULL,
  criterion_code varchar(40) NOT NULL,
  product_id int NOT NULL,
  created_at datetime(3) NOT NULL,
  started_at datetime(3) DEFAULT NULL,
  ended_at datetime(3) DEFAULT NULL,
  status varchar(15) NOT NULL,
  pending tinyint NULL,
  result json DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE INDEX evaluation_pending_uniqueness (product_id, criterion_code, pending),
  INDEX status_index (status),
  INDEX created_at_index (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pimee_data_quality_insights_product_axis_rates (
    product_id INT NOT NULL,
    axis_code VARCHAR(40) NOT NULL,
    evaluated_at DATE NOT NULL,
    rates JSON NOT NULL,
    PRIMARY KEY (product_id, axis_code, evaluated_at),
    INDEX evaluated_at_index (evaluated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pimee_data_quality_insights_dashboard_rates_projection (
    type VARCHAR(15) NOT NULL,
    code VARCHAR(100) NOT NULL,
    rates JSON NOT NULL,
    PRIMARY KEY (type, code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pimee_data_quality_insights_text_checker_dictionary (
    locale_code VARCHAR(20) NOT NULL,
    word VARCHAR(250) NOT NULL,
    INDEX word_index (word)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $this->addSql($query);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
