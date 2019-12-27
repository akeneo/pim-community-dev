<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version_3_0_20191227141338_fix_versioning_indexes extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $sql = <<<SQL
ALTER TABLE pim_versioning_version 
    DROP INDEX resource_name_idx,
    DROP INDEX version_idx,
    DROP INDEX logged_at_idx,
    ADD INDEX resource_name_logged_at_idx (resource_name, logged_at);
SQL;

        $this->addSql($sql);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
