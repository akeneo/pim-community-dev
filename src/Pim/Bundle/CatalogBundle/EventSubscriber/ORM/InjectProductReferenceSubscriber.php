<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\PersistentCollection;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

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

        if ($entity instanceof GroupInterface) {
            $this->setProductPersistentCollection(
                $entity,
                array(
                    'mappedBy' => 'groups',
                    'fetch'    => ClassMetadata::FETCH_LAZY
                ),
                $entityManager
            );
        }

        if ($entity instanceof CategoryInterface) {
            $this->setProductPersistentCollection(
                $entity,
                array(
                    'mappedBy' => 'categories',
                    'fetch'    => ClassMetadata::FETCH_EXTRA_LAZY
                ),
                $entityManager
            );
        }
    }

    /**
     * Prepare a lazy loadable PersistentCollection
     * on the entity to get Products.
     * The entity must have a "products" property defined
     *
     * @param object        $entity        The entity related to the products
     * @param array         $assoc         Association properties
     * @param EntityManager $entityManager Entity manager
     */
    protected function setProductPersistentCollection(
        $entity,
        $assoc,
        EntityManager $entityManager
    ) {
        $targetEntity = $this->productClass;

        $productsCollection = new PersistentCollection(
            $entityManager,
            $targetEntity,
            new ArrayCollection()
        );

        $assoc['fieldName'] = 'products';
        $assoc['targetEntity'] = $targetEntity;
        $assoc['type'] = ClassMetadata::MANY_TO_MANY;
        $assoc['inversedBy'] = '';
        $assoc['isOwningSide'] = false;
        $assoc['sourceEntity'] = get_class($entity);
        $assoc['orphanRemoval'] = false;

        $productsCollection->setOwner($entity, $assoc);
        $productsCollection->setInitialized(false);

        $entityMetadata = $entityManager->getClassMetadata(get_class($entity));

        $productsReflProp = $entityMetadata->reflClass->getProperty('products');
        $productsReflProp->setAccessible(true);

        $productsReflProp->setValue(
            $entity,
            $productsCollection
        );
    }
}
