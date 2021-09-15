<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version_6_0_20210823070345_create_pim_sso_log_grth extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // As we moved the core of the Authentication Bundle from EE to GRTH,
        // we keep the `pimee_sso_log` name in order to keep the compatibility with the EE.
        $this->addSql(<<<SQL
CREATE TABLE IF NOT EXISTS pimee_sso_log (
  time DATETIME,
  channel VARCHAR(255),
  level SMALLINT,
  message TEXT,
  INDEX(time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
