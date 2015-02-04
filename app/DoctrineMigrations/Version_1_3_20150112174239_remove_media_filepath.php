<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Pim\Upgrade\UpgradeHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Remove absolute filepath from product medias
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_1_3_20150112174239_remove_media_filepath extends AbstractMigration implements ContainerAwareInterface
{
    const PRODUCT_COLLECTION = 'pim_catalog_product';

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
        $helper = new UpgradeHelper($this->container);
        if (!$helper->areProductsStoredInMongo()) {
            $this->addSql(sprintf('ALTER TABLE %s DROP file_path', $this->getOrmTableName()));
        }
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
            $this->removeFilePathFromProductMedias($database);
        }
    }

    protected function removeFilePathFromProductMedias(\MongoDB $database)
    {
        $productCollection = new \MongoCollection($database, self::PRODUCT_COLLECTION);
        $products = $productCollection->find();

        echo sprintf("Removing filePath from %s medias...\n", $products->count());

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

        echo sprintf("FilePath removed from %s medias: <info>done</info>.\n", $products->count());
    }

    protected function getOrmTableName()
    {
        $class = $this->container->getParameter('pim_catalog.entity.product_media.class');

        return $this->container->get('doctrine.orm.entity_manager')->getClassMetadata($class)->getTableName();
    }
}
