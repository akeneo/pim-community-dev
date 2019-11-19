<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_4_0_20191114152642_create_pim_sso_log extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
DROP TABLE IF EXISTS pimee_sso_log;
CREATE TABLE IF NOT EXISTS pimee_sso_log (
  time DATETIME,
  channel VARCHAR(255),
  level TINYINT,
  message TEXT,
  INDEX(time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
        );
    }

    public function down(Schema $schema) : void
    {
    }
}
