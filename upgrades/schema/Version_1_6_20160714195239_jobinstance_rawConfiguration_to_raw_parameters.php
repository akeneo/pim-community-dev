<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Rename JobInstance rawConfiguration to rawParameters
 */
class Version_1_6_20160714195239_jobinstance_rawConfiguration_to_raw_parameters extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE akeneo_batch_job_instance CHANGE rawConfiguration raw_parameters longtext COLLATE utf8_unicode_ci NOT NULL COMMENT \'(DC2Type:array)\'');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
