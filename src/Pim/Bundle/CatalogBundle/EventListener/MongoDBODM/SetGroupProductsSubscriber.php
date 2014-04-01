<?php

namespace Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * ORM subscriber registered when Product is a mongo document
 * It sets the other side of the relation Products <-> Groups
 *
 * Could be enhanced by somehow creating a document(s) custom type
 * with targetDocument and targetField
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetGroupProductsSubscriber implements EventSubscriber
{
    /** @var ManagerRegistry */
    protected $registry;

    public function __construct(ManagerRegistry $registry, $productClass)
    {
        $this->registry = $registry;
        $this->productClass = $productClass;
    }

    public function getSubscribedEvents()
    {
        return ['postLoad'];
    }

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
