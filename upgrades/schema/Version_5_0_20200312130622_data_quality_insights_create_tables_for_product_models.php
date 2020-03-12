<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20200312130622_data_quality_insights_create_tables_for_product_models extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(
            <<<SQL
CREATE TABLE IF NOT EXISTS pimee_data_quality_insights_product_model_criteria_evaluation (
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
SQL
        );
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
