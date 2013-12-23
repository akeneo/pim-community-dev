<?php

namespace Pim\Bundle\CatalogBundle\EventListener\ORM;

use Pim\Bundle\CatalogBundle\Entity\Group;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Mapping\ClassMetadata;

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
     * @var string
     */
    protected $productClass;

    /**
     * Constructor
     *
     * @param string $productClass
     */
    public function __construct($productClass)
    {
        $this->productClass = $productClass;
    }


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
        $targetEntity = $this->productClass;

        $productsCollection = new PersistentCollection(
            $entityManager,
            $targetEntity,
            new ArrayCollection()
        );

        $assoc = array();
        $assoc['fieldName'] = 'products';
        $assoc['targetEntity'] = $targetEntity;
        $assoc['mappedBy'] = 'groups';
        $assoc['type'] = ClassMetadata::MANY_TO_MANY;
        $assoc['inversedBy'] = '';
        $assoc['isOwningSide'] = false;
        $assoc['sourceEntity'] = get_class($group);
        $assoc['fetch'] = ClassMetadata::FETCH_LAZY;

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
