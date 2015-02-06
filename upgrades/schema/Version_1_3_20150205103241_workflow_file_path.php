<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Class Version_1_3_20150205103241_workflow_file_path
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_1_3_20150205103241_workflow_file_path extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $helper = new UpgradeHelper($this->container);
        if (!$helper->areProductsStoredInMongo()) {

            $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
            $this->addSql('ALTER TABLE pimee_workflow_published_product_media DROP file_path');
        }
    }

    public function down(Schema $schema)
    {
        $helper = new UpgradeHelper($this->container);
        if (!$helper->areProductsStoredInMongo()) {

            $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
            $this->addSql('ALTER TABLE pimee_workflow_published_product_media ADD file_path VARCHAR(500) DEFAULT NULL');
        }
    }
}
