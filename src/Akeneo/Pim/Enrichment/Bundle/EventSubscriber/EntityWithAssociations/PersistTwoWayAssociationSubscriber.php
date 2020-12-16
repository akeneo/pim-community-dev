<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithAssociations;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class PersistTwoWayAssociationSubscriber implements EventSubscriberInterface
{
    /** @var ManagerRegistry */
    private $registry;

    public function __construct(
        ManagerRegistry $registry
    ) {
        $this->registry = $registry;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_SAVE => 'handlePreSave',
        ];
    }

    public function handlePreSave(GenericEvent $event): void
    {
        $entity = $event->getSubject();

        if (!$entity instanceof EntityWithAssociationsInterface) {
            return;
        }

        $em = $this->registry->getManager();

        /** @var AssociationInterface $association */
        foreach ($entity->getAssociations() as $association) {
            $associationType = $association->getAssociationType();

            if (!$associationType->isTwoWay()) {
                continue;
            }

            foreach ($association->getProducts() as $product) {
                $em->persist($product);
            }

            foreach ($association->getProductModels() as $productModel) {
                $em->persist($productModel);
            }
        }
    }
}
