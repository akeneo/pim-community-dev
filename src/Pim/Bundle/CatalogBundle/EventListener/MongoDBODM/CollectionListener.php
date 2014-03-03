<?php

namespace Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Pim\Bundle\CatalogBundle\Model\Product;
use Doctrine\ORM\PersistentCollection;

/**
 * Aims to convert ArrayCollection to collection 
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CollectionListener implements EventSubscriber
{
    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'postLoad'
        );
    }

    /**
     * After load, will change the PHP array to ArrayCollection for Categories and Groups
     * in product
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Product) {
            $this->setArrayFromCollection($entity);
        }
    }

    /**
     * After load, will change the PHP array to ArrayCollection for Categories and Groups
     * in product
     *
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
    /*
        $entity = $args->getEntity();

        if ($entity instanceof Product) {
            $productManager = $this->container->get('pim_catalog.manager.product');
            $entity->setScope($productManager->getScope());
        }
        */
    }

    /**
     * Convert the ArrayCollection to PHP array
     *
     * @param ProductInterface $product
     */
    public function setArrayFromCollection(ProductInterface $product)
    {
        if (($product->getGroups() instanceof PersistentCollection)
            && ($product->getGroups->isInitialized())) {

            $groupIds = array();
            foreach ($product->getGroups() as $group) {
                $groupId = $group->getId();
                if (null === $groupId) {
                    throw new \LogicException('A group without ID has been found in the product '.$product);
                }
                $groupIds[] = $groupId;
            }

            $product->setGroupIds($groupIds);
        }

        if (($product->getCategories() instanceof PersistentCollection)
            && ($product->getCategories()->isInitialized())) {

            $categoryIds = array();
            foreach ($product->getCategories() as $category) {
                $categoryId = $category->getId();
                if (null === $category) {
                    throw new \LogicException('A category without ID has been found in the product '.$product);
                }
                $categoryIds[] = $categoryId;
            }

            $product->setCategoryIds($categoryIds);
        }
    }
}
