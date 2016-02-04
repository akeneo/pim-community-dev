<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Pim\Upgrade\SchemaHelper;
use Pim\Upgrade\UpgradeHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * MONGO migration only
 *
 * Add product statuses to Mongo normalized data
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_1_3_20141203150000_product_statuses extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema)
    {
    }

    public function down(Schema $schema)
    {
        throw new \RuntimeException('No revert is provided for the migrations.');
    }

    public function postUp(Schema $schema)
    {
        $helper = new UpgradeHelper($this->container);
        if ($helper->areProductsStoredInMongo()) {
            $database = $helper->getMongoInstance();
            $this->normalizeProductStatuses($database);
        }
    }

    protected function normalizeProductStatuses(\MongoDB $database)
    {
        $tableHelper = new SchemaHelper($this->container);
        $productCollection = new \MongoCollection($database, $tableHelper->getTableOrCollection('product'));
        $products = $productCollection->find();

        echo sprintf("Migrating %s product status values...\n", $products->count());

        foreach ($products as $product) {
            $result = $productCollection->update(
                ['_id' => $product['_id']],
                [
                    '$set' => [
                        'normalizedData.enabled' => $product['enabled']
                    ]
                ],
                ['w' => true]
            );

            if ($result['ok'] != 1) {
                echo "ERROR on migrating enabled value:";
                print_r($result);
                print_r($product);
            }
        }

        echo sprintf("Migrating %s product status values done.\n", $products->count());
    }
}
