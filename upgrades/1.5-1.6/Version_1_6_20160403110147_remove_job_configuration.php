<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Remove job configuration
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_1_6_20160403110147_remove_job_configuration extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('DROP TABLE pim_job_configuration');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('CREATE TABLE pim_job_configuration (id INT AUTO_INCREMENT NOT NULL, job_execution_id INT DEFAULT NULL, configuration LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_47542A125871C06B (job_execution_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pim_job_configuration ADD CONSTRAINT FK_47542A125871C06B FOREIGN KEY (job_execution_id) REFERENCES akeneo_batch_job_execution (id) ON DELETE CASCADE');
    }
}
