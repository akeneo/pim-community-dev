<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Gedmo\References\LazyCollection;

/**
 * ORM subscriber registered when Product is a mongo document
 * It sets the other side of the relation Products <-> Groups
 *
 * TODO
 * - Could be enhanced by somehow creating a document(s) custom type
 * with targetDocument and targetField.
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
    protected $mappings;

    /**
     * @param ManagerRegistry $registry
     * @param string          $productClass
     * @param array           $mappings
     */
    public function __construct(ManagerRegistry $registry, $productClass, array $mappings)
    {
        $this->registry = $registry;
        $this->productClass = $productClass;
        $this->mappings = $mappings;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'postLoad',
            // 'prePersist' deactivated for now to avoid side effect during import */
        ];
    }

    /**
     * Injects related products inside the group
     *
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        foreach ($this->mappings as $mapping) {
            if ($entity instanceof $mapping['class']) {
                $metadata = $args->getEntityManager()->getClassMetadata(get_class($entity));
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

                $property = $mapping['property'];
                $productsProp->setValue(
                    $entity,
                    new LazyCollection(
                        function () use ($property, $entity) {
                            return new ArrayCollection(
                                $this
                                    ->registry
                                    ->getRepository($this->productClass)
                                    ->findBy([$property => $entity->getId()])
                            );
                        }
                    )
                );
            }
        }
    }

    /**
     * Prevents adding product aware entities before persisting them.
     *
     * This is required because it is the document that holds the relation.
     * Otherwise, if products were added into the entitiy they will be lost when inserting it into the RDBM.
     * In fact, there's a listener (in the versionning bundle) that'll call refresh on every flushed object,
     * thus calling the above postLoad method which overwrites the $products property of the entity.
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        foreach ($this->mappings as $mapping) {
            if ($mapping['class'] === get_class($entity)) {
                $reflClass = new \ReflectionClass($entity);
                $reflProp = $reflClass->getProperty('products');
                $reflProp->setAccessible(true);
                $objects = $reflProp->getValue($entity);

                if ((is_array($objects) && count($objects) > 0)
                    || ($objects instanceof \Countable && $objects->count() > 0)
                ) {
                    throw new \LogicException(
                        sprintf(
                            'Instance of %s must be inserted into database before adding any products inside it',
                            $mapping['class']
                        )
                    );
                }
            }
        }
    }
}
