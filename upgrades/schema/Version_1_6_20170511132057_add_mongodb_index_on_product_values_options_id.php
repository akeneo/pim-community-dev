<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Pim\Upgrade\SchemaHelper;
use Pim\Upgrade\UpgradeHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Add index on MongoDB Product collection for retrieving products by an attribute option value code
 * See https://akeneo.atlassian.net/browse/PIM-6376 for more details
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_1_6_20170511132057_add_mongodb_index_on_product_values_options_id extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $upgradeHelper = new UpgradeHelper($this->container);

        if ($upgradeHelper->areProductsStoredInMongo()) {
            $database = $upgradeHelper->getMongoInstance();
            $tableHelper = new SchemaHelper($this->container);

            $productCollection = new \MongoCollection($database, $tableHelper->getTableOrCollection('product'));
            $productCollection->ensureIndex(['values.optionIds' => 1], ['background' => true]);
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
