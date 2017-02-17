<?php

namespace Akeneo\Test\Integration;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DatabasePurger
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
     * Calls the appropriates purgers depending on the storage.
     */
    public function purge()
    {
        if (AkeneoStorageUtilsExtension::DOCTRINE_MONGODB_ODM === $this->container->getParameter('pim_catalog_product_storage_driver')) {
            $purgers[] = new MongoDBPurger($this->container->get('doctrine_mongodb')->getManager());
        }

        $purgers[] = new ORMPurger($this->container->get('doctrine')->getManager());

        foreach ($purgers as $purger) {
            $purger->purge();
        }
    }
}
