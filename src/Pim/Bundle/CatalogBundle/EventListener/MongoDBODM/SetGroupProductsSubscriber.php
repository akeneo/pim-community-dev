<?php

namespace Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\ManagerRegistry;

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
class SetGroupProductsSubscriber implements EventSubscriber
{
    /** @var ManagerRegistry */
    protected $registry;

    /**
     * @Param ManagerRegistry $registry
     * @param string          $productClass
     */
    public function __construct(ManagerRegistry $registry, $productClass)
    {
        $this->registry = $registry;
        $this->productClass = $productClass;
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
        if (!$entity instanceof \Pim\Bundle\CatalogBundle\Entity\Group) {
            return;
        }

        $entity->setProducts(
            $this->registry->getRepository($this->productClass)->findBy(['groups' => array($entity->getId())])
        );
    }
}
