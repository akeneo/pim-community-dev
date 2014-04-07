<?php

namespace Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\OnFlushEventArgs;

/**
 * ORM subscriber registered when Product is a mongo document
 * It sets the other side of the relation Products <-> Groups
 *
 * TODO
 * - Could be enhanced by somehow creating a document(s) custom type
 * with targetDocument and targetField.
 *
 * - A Referenced Collection could also be used to lazy load products.
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetProductsSubscriber implements EventSubscriber
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var string */
    protected $productClass;

    /** @var array */
    protected $productsAwareClassMapping;

    /**
     * @param ManagerRegistry $registry
     * @param string          $productClass
     */
    public function __construct(ManagerRegistry $registry, $productClass, array $productsAwareClassMapping)
    {
        $this->registry = $registry;
        $this->productClass = $productClass;
        $this->productsAwareClassMapping = $productsAwareClassMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return ['postLoad', 'prePersist'];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        foreach ($this->productsAwareClassMapping as $mapping) {
            if ($mapping['class'] === get_class($entity)) {
                $reflClass = new \ReflectionClass($entity);
                $reflProp = $reflClass->getProperty('products');
                $reflProp->setAccessible(true);
                $objects = $reflProp->getValue($entity);
            }
        }
    }

    /**
     * Injects related products inside the group
     *
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $metadata = $args->getEntityManager()->getClassMetadata(get_class($entity));

        foreach ($this->productsAwareClassMapping as $mapping) {
            if ($entity instanceof $mapping['class']) {
                if (!$metadata->reflClass->hasProperty('products')) {
                    throw new \LogicException(
                        sprintf(
                            'Property "%s::$products" does not exist',
                            get_class($entity)
                        )
                    );
                }
                $productsProp = $metadata->reflClass->getProperty('products');
                $productsProp->setAccessible(true);

                $productsProp->setValue(
                    $entity,
                    new \Doctrine\Common\Collections\ArrayCollection(
                        $this->registry->getRepository($this->productClass)->findBy([$mapping['property'] => array($entity->getId())])
                    )
                );
            }
        }
    }
}
