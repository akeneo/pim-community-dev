<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version_2_3_20190103095151_published_product_completeness_missing_attribute extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $sql = <<<SQL
ALTER TABLE pimee_workflow_published_product_completeness_missing_attribute DROP FOREIGN KEY FK_B0FD5518762147F6;
ALTER TABLE pimee_workflow_published_product_completeness_missing_attribute ADD CONSTRAINT FK_B0FD5518762147F6 FOREIGN KEY (missing_attribute_id) REFERENCES pim_catalog_attribute (id) ON DELETE CASCADE;
SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
