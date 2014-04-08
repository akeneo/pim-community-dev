<?php

namespace Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Gedmo\References\LazyCollection;
use Doctrine\Common\Collections\ArrayCollection;

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
        return ['postLoad'];
    }

    /**
     * Injects related products inside the group
     *
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        foreach ($this->productsAwareClassMapping as $mapping) {
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
                                    ->findBy([$property => array($entity->getId())])
                            );
                        }
                    )
                );
            }
        }
    }
}
