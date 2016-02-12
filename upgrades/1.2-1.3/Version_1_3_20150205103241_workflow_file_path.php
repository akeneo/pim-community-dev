<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Pim\Upgrade\SchemaHelperEE;
use Pim\Upgrade\UpgradeHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Version_1_3_20150205103241_workflow_file_path
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_1_3_20150205103241_workflow_file_path extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema)
    {
        $upgradeHelper = new UpgradeHelper($this->container);
        if (!$upgradeHelper->areProductsStoredInMongo()) {
            $tableHelper = new SchemaHelperEE($this->container);
            $this->addSql(sprintf('ALTER TABLE %s DROP file_path', $tableHelper->getTableOrCollection('published_product_media')));
        }
    }

    public function down(Schema $schema)
    {
        throw new \RuntimeException('No revert is provided for the migrations.');
    }

    public function postUp(Schema $schema)
    {
        $upgradeHelper = new UpgradeHelper($this->container);
        if ($upgradeHelper->areProductsStoredInMongo()) {
            $database = $upgradeHelper->getMongoInstance();
            $this->removeFilePathFromProductMedias($database);
        }
    }

    protected function removeFilePathFromProductMedias(\MongoDB $database)
    {
        $tableHelper = new SchemaHelperEE($this->container);
        $productCollection = new \MongoCollection($database, $tableHelper->getTableOrCollection('published_product'));
        $products = $productCollection->find();

        echo sprintf("Removing filePath from %s published medias...\n", $products->count());

        foreach ($products as $product) {
            if (array_key_exists('values', $product)) {
                $countValues = count($product['values']);

                for ($i = 0; $i <= $countValues; $i++) {
                    $result = $productCollection->update(
                        ['_id' => $product['_id']],
                        ['$unset' => [sprintf('values.%s.media.filePath', $i) => true]],
                        ['w' => true]
                    );

                    if ($result['ok'] != 1) {
                        echo "ERROR on migrating media value:";
                        print_r($result);
                        print_r($product);
                    }
                }
            }
        }

        echo sprintf("FilePath removed from %s published medias: <info>done</info>.\n", $products->count());
    }
}
