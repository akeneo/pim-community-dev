<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithAssociations;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductIndexerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductModelIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class PersistTwoWayAssociationSubscriber implements EventSubscriberInterface
{
    private ManagerRegistry $registry;
    private ProductIndexerInterface $productIndexer;
    private ProductModelIndexerInterface $productModelIndexer;

    public function __construct(
        ManagerRegistry $registry,
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer
    ) {
        $this->registry = $registry;
        $this->productIndexer = $productIndexer;
        $this->productModelIndexer = $productModelIndexer;
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
        $productIdentifiers = [];
        $productModelCodes = [];

        /** @var AssociationInterface $association */
        foreach ($entity->getAssociations() as $association) {
            $associationType = $association->getAssociationType();

            if (!$associationType->isTwoWay()) {
                continue;
            }

            foreach ($association->getProducts() as $product) {
                $em->persist($product);
                $productIdentifiers[] = $product->getIdentifier();
            }

            foreach ($association->getProductModels() as $productModel) {
                $em->persist($productModel);
                $productModelCodes[] = $productModel->getCode();
            }
        }

        $this->indexAssociatedEntities($productIdentifiers, $productModelCodes);
    }

    private function indexAssociatedEntities(array $productIdentifiers, array $productModelCodes)
    {
        $this->productIndexer->indexFromProductIdentifiers($productIdentifiers);
        $this->productModelIndexer->indexFromProductModelCodes($productModelCodes);
    }
}
