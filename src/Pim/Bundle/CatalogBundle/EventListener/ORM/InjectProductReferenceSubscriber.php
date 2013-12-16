<?php

namespace Pim\Bundle\CatalogBundle\EventListener\ORM;

use Pim\Bundle\CatalogBundle\Entity\Group;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\PersistentCollection;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Inject Product references into entities that needs them.
 * We break the mapping between entities and Products because products
 * can be in ORM or in ODM
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InjectProductReferenceSubscriber implements EventSubscriber
{
    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'postLoad'
        );
    }

    /**
     * After load, adds ORM references to document
     *
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if ($entity instanceof Group) {
            $this->setProductGroupReference($entity, $entityManager);
        }
    }

    /**
     * Prepare a lazy loadable PersistentCollection
     * on the group to get Products
     *
     * @param Group         $group
     * @param EntityManager $entityManager
     */
    protected function setProductGroupReference(Group $group, EntityManager $entityManager)
    {
        // FIXME_MONGODB : get the final name of the Product class
        $productsCollection = new PersistentCollection(
            $entityManager,
            'Pim\Bundle\CatalogBundle\Model\Product',
            new ArrayCollection()
        );

        $assoc['inversedBy'] = 'groups';

        $productsCollection->setOwner($group, $assoc);
        $productsCollection->setInitialized(false);

        $groupMetadata = $entityManager->getClassMetadata(get_class($group));

        $productsReflProp = $groupMetadata->reflClass->getProperty('products');
        $productsReflProp->setAccessible(true);

        $productsReflProp->setValue(
            $group,
            $productsCollection
        );
    }
}
