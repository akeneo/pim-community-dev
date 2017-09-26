<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add column "image_attribute_id" in the "pim_catalog_family" table
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_2_0_20170404125715 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE pim_catalog_family ADD image_attribute_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pim_catalog_family ADD CONSTRAINT FK_90632072BC295696 '+
            ' FOREIGN KEY (image_attribute_id) REFERENCES pim_catalog_attribute (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_90632072BC295696 ON pim_catalog_family (image_attribute_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
