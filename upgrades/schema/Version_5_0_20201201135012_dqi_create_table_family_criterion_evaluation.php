<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20201201135012_dqi_create_table_family_criterion_evaluation extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
CREATE TABLE IF NOT EXISTS pimee_dqi_family_criteria_evaluation (
  family_id int NOT NULL,
  criterion_code varchar(40) NOT NULL,
  evaluated_at datetime NULL,
  result json DEFAULT NULL,
  PRIMARY KEY (family_id, criterion_code),
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
        );


    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
