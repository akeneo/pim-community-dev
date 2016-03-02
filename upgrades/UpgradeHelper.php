<?php

namespace Pim\Upgrade;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Upgrade helper
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpgradeHelper
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @throws \Exception
     *
     * @return \MongoClient
     */
    public function getMongoClient()
    {
        if (!$this->areProductsStoredInMongo()) {
            throw new \Exception('Your application does not store products in Mongo.');
        }

        $server = $this->container->getParameter('mongodb_server');

        return new \MongoClient($server);
    }

    /**
     * @throws \Exception
     *
     * @return \MongoDB
     */
    public function getMongoInstance()
    {
        if (!$this->areProductsStoredInMongo()) {
            throw new \Exception('Your application does not store products in Mongo.');
        }

        $database = $this->container->getParameter('mongodb_database');

        return $this->getMongoClient()->$database;
    }

    /**
     * @return bool
     */
    public function areProductsStoredInMongo()
    {
        $storage = $this->container->getParameter('pim_catalog_product_storage_driver');

        return $storage === AkeneoStorageUtilsExtension::DOCTRINE_MONGODB_ODM;
    }
}
