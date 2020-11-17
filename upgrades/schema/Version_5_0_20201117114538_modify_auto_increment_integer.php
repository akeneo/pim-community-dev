<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20201117114538_modify_auto_increment_integer extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $sql = "ALTER TABLE pim_catalog_completeness MODIFY id bigint AUTO_INCREMENT";
        $this->addSql($sql);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
