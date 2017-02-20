<?php

namespace Pim\Upgrade\schema;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Pim\Upgrade\UpgradeHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * In 1.5 we moved model class to the catalog component. As the versioning rely on the FQCN
 * to create and find versions we need to update the version table with the correct FQCN
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_1_5_20160405165700_versioning extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    protected $movedEntities = [
        'Pim\\Bundle\\CatalogBundle\\Model\\Product'       => 'Pim\\Component\\Catalog\\Model\\Product',
        'Akeneo\\Bundle\\BatchBundle\\Entity\\JobInstance' => 'Akeneo\\Component\\Batch\\Model\\JobInstance',
    ];

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        if (AkeneoStorageUtilsExtension::DOCTRINE_ORM ===
            $this->container->getParameter('pim_catalog_product_storage_driver')
        ) {
            $updateSql = 'UPDATE pim_versioning_version SET resource_name = :after WHERE resource_name = :before';

            foreach ($this->movedEntities as $source => $target) {
                $updateStmt = $this->connection->prepare($updateSql);
                $updateStmt->bindValue('before', $source);
                $updateStmt->bindValue('after', $target);
                $updateStmt->execute();
            }
        }
    }

    public function postUp(Schema $schema)
    {
        $helper = new UpgradeHelper($this->container);
        if ($helper->areProductsStoredInMongo()) {
            $database = $helper->getMongoInstance();
            $versionCollection = new \MongoCollection($database, 'pim_versioning_version');

            foreach ($this->movedEntities as $source => $target) {
                $result = $versionCollection->update(
                    ['resourceName' => $source],
                    [
                        '$set' => [
                            'resourceName' => $target
                        ]
                    ],
                    ['multiple' => true]
                );
            }
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
