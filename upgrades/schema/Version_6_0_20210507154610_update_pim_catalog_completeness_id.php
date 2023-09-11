<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20210507154610_update_pim_catalog_completeness_id extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        if (!$this->shouldExecuteMigration()) {
            return;
        }
        $this->addSql("ALTER TABLE pim_catalog_completeness MODIFY id bigint AUTO_INCREMENT");
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    public function shouldExecuteMigration()
    {
        $sql = <<<SQL
select data_type
from information_schema.COLUMNS
where
  TABLE_NAME='pim_catalog_completeness'
  and COLUMN_NAME='id';
SQL;
        $result = $this->get('database_connection')
            ->executeQuery($sql)
            ->fetchOne();

        return 'bigint' !== $result;
    }
}
