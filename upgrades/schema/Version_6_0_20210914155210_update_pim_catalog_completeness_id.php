<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


/**
 * This class is a duplication of \Pim\Upgrade\Schema\Version_5_0_20210507154610_update_pim_catalog_completeness_id
 * Because this class have been merged in 5.0 after the release candidate
 */
final class Version_6_0_20210914155210_update_pim_catalog_completeness_id extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql("ALTER TABLE pim_catalog_completeness MODIFY id bigint AUTO_INCREMENT");
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
